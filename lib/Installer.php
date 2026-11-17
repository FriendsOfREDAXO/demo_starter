<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\DemoStarter;

use rex;
use rex_addon;
use rex_article;
use rex_article_service;
use rex_autoload;
use rex_category;
use rex_category_service;
use rex_clang;
use rex_clang_service;
use rex_config;
use rex_content_service;
use rex_file;
use rex_media;
use rex_media_category_service;
use rex_media_service;
use rex_module;
use rex_path;
use rex_sql;
use rex_string;
use rex_template;
use rex_yform_manager_dataset;
use rex_yform_manager_table;
use rex_yform_manager_table_api;
use rex_yrewrite;
use RuntimeException;

/**
 * Richtet die Demo nach dem Rezept (data/recipe.yml) ein – über die Schnittstellen von REDAXO, nicht per SQL-Dump.
 *
 * Update-sicher:
 *  - Was es schon gibt, wird wiedergefunden (Registry in rex_config) und nicht doppelt angelegt.
 *  - Templates und Module werden nur aktualisiert, solange sie seit dem letzten Einrichten unverändert sind
 *    (Prüfsumme). Eigene Änderungen bleiben stehen; das Protokoll sagt, was übersprungen wurde.
 *  - Seiteninhalte werden nur beim ersten Anlegen geschrieben – danach gehören sie der Redaktion.
 */
final class Installer
{
    /** @var list<array{0: string, 1: string}> [Stufe, Text] – Stufe: ok, info, skip, warn */
    public array $log = [];

    /** @var array<string, mixed> */
    private array $recipe;

    /** @var array{templates: array<string, string>, modules: array<string, string>, pages: array<string, int>} */
    private array $registry;

    /** @var array<string, int> Schlüssel → ID */
    private array $templateIds = [];
    private array $moduleIds = [];

    public function __construct()
    {
        $this->recipe = rex_string::yamlDecode((string) rex_file::get(rex_path::addon('demo_starter', 'data/recipe.yml')));
        $saved = rex_config::get('demo_starter', 'registry', []);
        $this->registry = [
            'templates' => (array) ($saved['templates'] ?? []),
            'modules' => (array) ($saved['modules'] ?? []),
            'pages' => (array) ($saved['pages'] ?? []),
        ];
    }

    /**
     * Schritt 1: fehlende AddOns holen. Gibt true zurück, wenn etwas neu installiert wurde – dann braucht es einen
     * neuen Aufruf, damit REDAXO die frisch installierten AddOns vollständig lädt (boot.php, Klassen).
     */
    public function installPackages(): bool
    {
        $before = array_filter(array_keys($this->recipe['packages'] ?? []), static fn (string $k): bool => rex_addon::get($k)->isAvailable());
        $packages = new Packages();
        try {
            $packages->ensure((array) ($this->recipe['packages'] ?? []));
        } finally {
            foreach ($packages->log as $line) {
                $this->log[] = ['ok', $line];
            }
        }
        $after = array_filter(array_keys($this->recipe['packages'] ?? []), static fn (string $k): bool => rex_addon::get($k)->isAvailable());
        return count($after) > count($before);
    }

    /** Schritt 2: alles andere – setzt voraus, dass die AddOns aus Schritt 1 geladen sind */
    public function installContent(): void
    {
        foreach (array_keys($this->recipe['packages'] ?? []) as $key) {
            if (!rex_addon::get($key)->isAvailable()) {
                throw new RuntimeException($key . ' fehlt – bitte zuerst die AddOns einrichten.');
            }
        }
        $this->languages();
        $this->metainfo();
        $this->mediaTypes();
        $this->media();
        $this->yformTables();
        $this->templates();
        $this->modules();
        $this->yrewriteDomain();   // vor der Struktur: YRewrite erzeugt beim Anlegen jedes Artikels dessen Adresse
        $this->structure();
        $this->yrewriteStartPages();
        $this->saveRegistry();
        rex_delete_cache();
        $this->log[] = ['ok', 'Cache gelöscht – fertig.'];
    }

    // ------------------------------------------------------------------------------------------------------------
    // Sprachen
    // ------------------------------------------------------------------------------------------------------------

    private function languages(): void
    {
        foreach ((array) ($this->recipe['languages'] ?? []) as $i => $lang) {
            $existing = null;
            foreach (rex_clang::getAll() as $clang) {
                if ($clang->getCode() === $lang['code'] || (0 === $i && 1 === $clang->getId())) {
                    $existing = $clang;
                    break;
                }
            }
            if ($existing) {
                if (0 === $i && ($existing->getCode() !== $lang['code'] || $existing->getName() !== $lang['name'])) {
                    rex_clang_service::editCLang($existing->getId(), $lang['code'], $lang['name'], $existing->getPriority(), true);
                }
                continue;
            }
            rex_clang_service::addCLang($lang['code'], $lang['name'], $i + 1, true);
            $this->log[] = ['ok', 'Sprache angelegt: ' . $lang['name']];
        }
    }

    private function clangId(string $code): int
    {
        foreach (rex_clang::getAll() as $clang) {
            if ($clang->getCode() === $code) {
                return $clang->getId();
            }
        }
        throw new RuntimeException('Sprache fehlt: ' . $code);
    }

    // ------------------------------------------------------------------------------------------------------------
    // Metainfo-Felder
    // ------------------------------------------------------------------------------------------------------------

    private function metainfo(): void
    {
        require_once rex_path::addon('metainfo', 'functions/function_metainfo.php');
        $types = array_column(rex_sql::factory()->getArray('SELECT id, label FROM ' . rex::getTable('metainfo_type')), 'id', 'label');
        foreach ((array) ($this->recipe['metainfo'] ?? []) as $field) {
            $exists = rex_sql::factory()->getArray('SELECT id FROM ' . rex::getTable('metainfo_field') . ' WHERE name = ?', [$field['name']]);
            if ([] !== $exists) {
                continue;
            }
            $result = rex_metainfo_add_field($field['title'], $field['name'], (int) ($field['priority'] ?? 1), '', (int) $types[$field['type']], (string) ($field['default'] ?? ''), (string) ($field['params'] ?? ''));
            if (true !== $result) {
                throw new RuntimeException('Metainfo-Feld ' . $field['name'] . ': ' . $result);
            }
            $this->log[] = ['ok', 'Metainfo-Feld angelegt: ' . $field['name']];
        }
    }

    // ------------------------------------------------------------------------------------------------------------
    // Media-Manager-Typen
    // ------------------------------------------------------------------------------------------------------------

    private function mediaTypes(): void
    {
        foreach ((array) ($this->recipe['media_types'] ?? []) as $name => $type) {
            $exists = rex_sql::factory()->getArray('SELECT id FROM ' . rex::getTable('media_manager_type') . ' WHERE name = ?', [$name]);
            if ([] !== $exists) {
                continue;
            }
            $sql = rex_sql::factory()->setTable(rex::getTable('media_manager_type'));
            $sql->setValue('name', $name)->setValue('description', $type['description'] ?? '')->setValue('status', 0);
            $sql->addGlobalCreateFields()->addGlobalUpdateFields()->insert();
            $typeId = (int) $sql->getLastId();
            foreach ((array) $type['effects'] as $priority => $effect) {
                // Parameter im Format des Media Managers: {"rex_effect_resize": {"rex_effect_resize_width": "800", …}}
                $params = [];
                foreach ((array) ($effect['params'] ?? []) as $key => $value) {
                    $params['rex_effect_' . $effect['effect'] . '_' . $key] = (string) $value;
                }
                rex_sql::factory()->setTable(rex::getTable('media_manager_type_effect'))
                    ->setValue('type_id', $typeId)
                    ->setValue('effect', $effect['effect'])
                    ->setValue('parameters', json_encode(['rex_effect_' . $effect['effect'] => $params]))
                    ->setValue('priority', $priority + 1)
                    ->addGlobalCreateFields()->addGlobalUpdateFields()->insert();
            }
            $this->log[] = ['ok', 'Media-Manager-Typ angelegt: ' . $name];
        }
    }

    // ------------------------------------------------------------------------------------------------------------
    // Medienpool
    // ------------------------------------------------------------------------------------------------------------

    private function media(): void
    {
        $config = (array) ($this->recipe['media'] ?? []);
        $categoryName = (string) ($config['category'] ?? 'Demo');
        $row = rex_sql::factory()->getArray('SELECT id FROM ' . rex::getTable('media_category') . ' WHERE name = ? AND parent_id = 0', [$categoryName]);
        if ([] === $row) {
            rex_media_category_service::addCategory($categoryName, null);
            $row = rex_sql::factory()->getArray('SELECT id FROM ' . rex::getTable('media_category') . ' WHERE name = ? AND parent_id = 0', [$categoryName]);
        }
        $categoryId = (int) $row[0]['id'];

        foreach ((array) ($config['files'] ?? []) as $file => $title) {
            if (rex_media::get($file)) {
                continue;
            }
            // Kopie in einen Zwischenordner – der Medienpool verschiebt die Datei beim Übernehmen
            $tmp = rex_path::addonCache('demo_starter', 'upload/' . $file);
            rex_file::copy(rex_path::addon('demo_starter', 'data/media/' . $file), $tmp);
            rex_media_service::addMedia([
                'category_id' => $categoryId,
                'title' => $title,
                'file' => ['name' => $file, 'path' => $tmp],
            ], false);
            $this->log[] = ['ok', 'Medium übernommen: ' . $file];
        }
    }

    // ------------------------------------------------------------------------------------------------------------
    // YForm-Tabellen (Struktur) und Beispieldaten
    // ------------------------------------------------------------------------------------------------------------

    private function yformTables(): void
    {
        foreach ((array) ($this->recipe['yform_tables'] ?? []) as $tableName => $definition) {
            $isNew = null === rex_yform_manager_table::get($tableName);
            if ($isNew) {
                $fields = [];
                foreach ((array) $definition['fields'] as $prio => $field) {
                    $fields[] = $field + ['prio' => $prio + 1, 'list_hidden' => 0, 'search' => 1];
                }
                rex_yform_manager_table_api::setTable([
                    'table_name' => $tableName,
                    'name' => $definition['name'],
                    'status' => 1,
                    'list_amount' => 50,
                    'list_sortfield' => $definition['list_sortfield'] ?? 'id',
                    'list_sortorder' => $definition['list_sortorder'] ?? 'ASC',
                    'search' => 1,
                ], $fields);
                $this->log[] = ['ok', 'YForm-Tabelle angelegt: ' . $tableName];
            }
            // Spalten in der Datenbank zu den Feldern anlegen (idempotent – fehlende werden ergänzt)
            rex_yform_manager_table::deleteCache();
            rex_yform_manager_table_api::generateTableAndFields(rex_yform_manager_table::get($tableName));

            // Beispieldaten nur in eine leere Tabelle – über YOrm, wie sie auch ein Modul anlegen würde
            if ([] !== ($definition['data'] ?? []) && 0 === rex_yform_manager_dataset::query($tableName)->count()) {
                foreach ((array) $definition['data'] as $values) {
                    $dataset = rex_yform_manager_dataset::create($tableName);
                    foreach ($values as $key => $value) {
                        if ('date' === $key && is_string($value) && str_starts_with($value, '+')) {
                            $value = date('Y-m-d', (int) strtotime($value));   // relative Daten: Termine liegen immer in der Zukunft
                        }
                        $dataset->setValue($key, $value);
                    }
                    if (!$dataset->save()) {
                        throw new RuntimeException('Beispieldaten für ' . $tableName . ': ' . implode(' ', $dataset->getMessages()));
                    }
                }
                $this->log[] = ['ok', count((array) $definition['data']) . ' Beispieldatensätze in ' . $tableName];
            }
        }
    }

    // ------------------------------------------------------------------------------------------------------------
    // Templates und Module (Dateien → Datenbank, update-sicher über Prüfsummen)
    // ------------------------------------------------------------------------------------------------------------

    private function templates(): void
    {
        foreach ((array) ($this->recipe['templates'] ?? []) as $key => $definition) {
            $content = (string) rex_file::get(rex_path::addon('demo_starter', 'data/templates/' . $key . '.php'));
            $row = rex_sql::factory()->getArray('SELECT id, content FROM ' . rex::getTable('template') . ' WHERE `key` = ?', [$key]);
            if ([] === $row) {
                $sql = rex_sql::factory()->setTable(rex::getTable('template'));
                $sql->setValue('key', $key)->setValue('name', $definition['name'])->setValue('content', $content)
                    ->setValue('active', !empty($definition['active']) ? 1 : 0)
                    ->setValue('attributes', json_encode(['ctype' => [], 'modules' => [1 => ['all' => '1']], 'categories' => ['all' => '1']]))
                    ->addGlobalCreateFields()->addGlobalUpdateFields()->insert();
                $this->templateIds[$key] = (int) $sql->getLastId();
                $this->registry['templates'][$key] = md5($content);
                $this->log[] = ['ok', 'Template angelegt: ' . $definition['name']];
                continue;
            }
            $this->templateIds[$key] = (int) $row[0]['id'];
            $this->updateIfUntouched('templates', $key, (string) $row[0]['content'], $content, static function () use ($row, $content): void {
                rex_sql::factory()->setTable(rex::getTable('template'))->setWhere(['id' => (int) $row[0]['id']])
                    ->setValue('content', $content)->addGlobalUpdateFields()->update();
            }, 'Template ' . $definition['name']);
        }
    }

    private function modules(): void
    {
        foreach ((array) ($this->recipe['modules'] ?? []) as $key) {
            $dir = rex_path::addon('demo_starter', 'data/modules/' . $key . '/');
            $meta = rex_string::yamlDecode((string) rex_file::get($dir . 'module.yml'));
            $input = (string) rex_file::get($dir . 'input.php');
            $output = (string) rex_file::get($dir . 'output.php');
            $row = rex_sql::factory()->getArray('SELECT id, input, output FROM ' . rex::getTable('module') . ' WHERE `key` = ?', [$key]);
            if ([] === $row) {
                $sql = rex_sql::factory()->setTable(rex::getTable('module'));
                $sql->setValue('key', $key)->setValue('name', $meta['name'])->setValue('input', $input)->setValue('output', $output)
                    ->addGlobalCreateFields()->addGlobalUpdateFields()->insert();
                $this->moduleIds[$key] = (int) $sql->getLastId();
                $this->registry['modules'][$key] = md5($input . $output);
                $this->log[] = ['ok', 'Modul angelegt: ' . $meta['name']];
                continue;
            }
            $this->moduleIds[$key] = (int) $row[0]['id'];
            $this->updateIfUntouched('modules', $key, $row[0]['input'] . $row[0]['output'], $input . $output, static function () use ($row, $input, $output): void {
                rex_sql::factory()->setTable(rex::getTable('module'))->setWhere(['id' => (int) $row[0]['id']])
                    ->setValue('input', $input)->setValue('output', $output)->addGlobalUpdateFields()->update();
            }, 'Modul ' . $meta['name']);
        }
    }

    /**
     * Gemeinsame Regel für Templates und Module:
     *  - Datenbank = Datei → nichts zu tun
     *  - Datenbank = Stand vom letzten Einrichten (Prüfsumme) → niemand hat es geändert, neue Fassung übernehmen
     *  - sonst → von Hand geändert, stehen lassen
     */
    private function updateIfUntouched(string $kind, string $key, string $current, string $new, callable $update, string $label): void
    {
        if (md5($current) === md5($new)) {
            $this->registry[$kind][$key] = md5($new);
            return;
        }
        if (($this->registry[$kind][$key] ?? null) === md5($current)) {
            $update();
            $this->registry[$kind][$key] = md5($new);
            $this->log[] = ['ok', $label . ': neue Fassung übernommen'];
            return;
        }
        $this->log[] = ['skip', $label . ': von Hand geändert – bleibt, wie es ist'];
    }

    // ------------------------------------------------------------------------------------------------------------
    // Struktur und Inhalte
    // ------------------------------------------------------------------------------------------------------------

    private function structure(): void
    {
        // Das Layout wird Standard-Template – vor dem Anlegen, damit auch neue Kategorien es bekommen
        if (isset($this->templateIds['layout'])) {
            rex::setConfig('default_template_id', $this->templateIds['layout']);
        }
        $pages = (array) ($this->recipe['structure'] ?? []);
        $created = [];
        // 1. Durchgang: Kategorien und Artikel anlegen (damit Verweise wie „@basics“ im 2. Durchgang auflösbar sind)
        $this->createPages($pages, 0, $created);
        // 2. Durchgang: Inhalte (Slices) und Metadaten – nur für neu angelegte Seiten
        $this->fillPages($pages, $created);

        // Start- und Fehlerseite der Website (System → Einstellungen)
        if (isset($this->registry['pages']['home'])) {
            rex::setConfig('start_article_id', $this->registry['pages']['home']);
        }
        if (isset($this->registry['pages']['notfound'])) {
            rex::setConfig('notfound_article_id', $this->registry['pages']['notfound']);
        }
    }

    /**
     * @param list<array<string, mixed>> $pages
     * @param array<string, true> $created
     */
    private function createPages(array $pages, int $parentId, array &$created, bool $asArticles = false): void
    {
        $de = $this->clangId('de');
        foreach ($pages as $priority => $page) {
            $id = (string) $page['id'];
            $existing = $this->registry['pages'][$id] ?? 0;
            if ($existing > 0 && rex_article::get($existing)) {
                $articleId = $existing;
            } else {
                $isCategory = !$asArticles && !empty($page['category']);
                $articleId = $isCategory
                    ? $this->addCategory($parentId, (string) $page['de']['name'], $priority + 1)
                    : $this->addArticle($parentId, (string) $page['de']['name'], $priority + 1);
                $this->registry['pages'][$id] = $articleId;
                $this->saveRegistry();   // sofort merken – bricht etwas ab, legt der nächste Lauf nichts doppelt an
                $created[$id] = true;
                $this->log[] = ['ok', ($isCategory ? 'Kategorie' : 'Seite') . ' angelegt: ' . $page['de']['name']];
            }
            if (isset($page['setting'])) {
                rex_config::set('demo_starter', (string) $page['setting'], $articleId);
            }
            if (!empty($page['children'])) {
                $this->createPages((array) $page['children'], $articleId, $created);
            }
            if (!empty($page['articles'])) {
                $this->createPages((array) $page['articles'], $articleId, $created, true);
            }
        }
    }

    private function addCategory(int $parentId, string $name, int $priority): int
    {
        rex_category_service::addCategory($parentId, ['catname' => $name, 'catpriority' => $priority, 'status' => 1]);
        $id = $this->lastArticleId($parentId, true);
        // Der Startartikel einer neuen Kategorie erbt sonst das Template der Elternkategorie bzw. das Standard-Template
        if (isset($this->templateIds['layout'])) {
            rex_sql::factory()->setQuery('UPDATE ' . rex::getTable('article') . ' SET template_id = ? WHERE id = ?', [$this->templateIds['layout'], $id]);
        }
        return $id;
    }

    private function addArticle(int $categoryId, string $name, int $priority): int
    {
        rex_article_service::addArticle(['category_id' => $categoryId, 'name' => $name, 'priority' => $priority + 1, 'template_id' => $this->templateIds['layout'] ?? 0]);
        $id = $this->lastArticleId($categoryId, false);
        // Neue Artikel sind offline – online schalten, in jeder Sprache
        foreach (rex_clang::getAllIds() as $clangId) {
            rex_article_service::articleStatus($id, $clangId, 1);
        }
        return $id;
    }

    /** Die Services geben nur eine Meldung zurück – die neue ID ist die höchste in dieser Ebene */
    private function lastArticleId(int $parentId, bool $category): int
    {
        $row = rex_sql::factory()->getArray(
            'SELECT MAX(id) AS id FROM ' . rex::getTable('article') . ' WHERE parent_id = ? AND startarticle = ?',
            [$category ? $parentId : $parentId, $category ? 1 : 0],
        );
        return (int) ($row[0]['id'] ?? 0);
    }

    /**
     * @param list<array<string, mixed>> $pages
     * @param array<string, true> $created
     */
    private function fillPages(array $pages, array $created): void
    {
        foreach ($pages as $page) {
            $id = (string) $page['id'];
            $articleId = (int) $this->registry['pages'][$id];
            if (isset($created[$id])) {
                foreach ((array) ($this->recipe['languages'] ?? []) as $lang) {
                    $content = $page[$lang['code']] ?? null;
                    if (!$content) {
                        continue;
                    }
                    $clangId = $this->clangId($lang['code']);
                    $this->renamePage($articleId, $clangId, $content['name'], !empty($page['category']) && empty($page['article_only']));
                    $this->pageMeta($articleId, $clangId, $content, $page);
                    foreach ((array) ($content['slices'] ?? []) as $priority => $slice) {
                        $this->addSlice($articleId, $clangId, $priority + 1, $slice);
                    }
                }
            }
            if (!empty($page['children'])) {
                $this->fillPages((array) $page['children'], $created);
            }
            if (!empty($page['articles'])) {
                $this->fillPages((array) $page['articles'], $created);
            }
        }
    }

    private function renamePage(int $articleId, int $clangId, string $name, bool $isCategory): void
    {
        $article = rex_article::get($articleId, $clangId);
        if (!$article || $article->getName() === $name) {
            return;
        }
        if ($isCategory && $article->isStartArticle()) {
            rex_category_service::editCategory($articleId, $clangId, ['catname' => $name, 'catpriority' => $article->getValue('catpriority')]);
        }
        rex_article_service::editArticle($articleId, $clangId, ['name' => $name, 'priority' => $article->getPriority(), 'template_id' => $article->getTemplateId()]);
    }

    /** Metadaten: Kurztext, Teaserbild, „Im Hauptmenü zeigen“ */
    private function pageMeta(int $articleId, int $clangId, array $content, array $page): void
    {
        rex_sql::factory()->setTable(rex::getTable('article'))
            ->setWhere(['id' => $articleId, 'clang_id' => $clangId])
            ->setValue('art_teaser', (string) ($content['teaser'] ?? ''))
            ->setValue('art_image', (string) ($content['image'] ?? ''))
            ->setValue('cat_menu', false === ($page['menu'] ?? true) ? '' : '|1|')
            ->update();
    }

    private function addSlice(int $articleId, int $clangId, int $priority, array $slice): void
    {
        $moduleId = $this->moduleIds[$slice['module']] ?? 0;
        if (0 === $moduleId) {
            throw new RuntimeException('Modul fehlt: ' . $slice['module']);
        }
        $data = ['priority' => $priority];
        foreach ($slice as $field => $value) {
            if ('module' === $field) {
                continue;
            }
            // Verweise auf andere Seiten des Rezepts: „@basics“ → Artikel-ID
            if (is_string($value) && str_starts_with($value, '@')) {
                $value = (string) ($this->registry['pages'][substr($value, 1)] ?? 0);
            }
            $data[$field] = (string) $value;
        }
        rex_content_service::addSlice($articleId, $clangId, 1, $moduleId, $data);
    }

    // ------------------------------------------------------------------------------------------------------------
    // YRewrite: Domain für diese Installation
    // ------------------------------------------------------------------------------------------------------------

    /** Domain dieser Installation – Start- und Fehlerseite folgen nach dem Anlegen der Struktur */
    private function yrewriteDomain(): void
    {
        if ([] !== rex_sql::factory()->getArray('SELECT id FROM ' . rex::getTable('yrewrite_domain') . ' LIMIT 1')) {
            return;
        }
        $host = (string) (parse_url(rex::getServer(), PHP_URL_HOST) ?: ($_SERVER['HTTP_HOST'] ?? ''));
        if ('' === $host) {
            $this->log[] = ['warn', 'YRewrite: keine Domain ermittelt – bitte unter YRewrite → Domains eintragen.'];
            return;
        }
        rex_sql::factory()->setTable(rex::getTable('yrewrite_domain'))
            ->setValue('domain', $host)
            ->setValue('mount_id', 0)
            ->setValue('start_id', (int) ($this->registry['pages']['home'] ?? 0))
            ->setValue('notfound_id', (int) ($this->registry['pages']['notfound'] ?? 0))
            ->setValue('clangs', '')
            ->setValue('clang_start', $this->clangId('de'))
            ->setValue('clang_start_auto', 0)
            ->setValue('clang_start_hidden', 0)
            ->setValue('robots', "User-agent: *\nDisallow:")
            ->setValue('title_scheme', '%T / %SN')
            ->setValue('description', '')
            ->setValue('auto_redirect', 0)
            ->setValue('auto_redirect_days', 0)
            ->insert();
        // Konfiguration neu schreiben und in diesem Aufruf neu laden – sonst kennt YRewrite die Domain noch nicht
        rex_yrewrite::generateConfig();
        rex_yrewrite::init();
        rex_yrewrite::copyHtaccess();
        $this->log[] = ['ok', 'YRewrite: Domain ' . $host . ' angelegt, .htaccess kopiert'];
    }

    private function yrewriteStartPages(): void
    {
        $home = (int) ($this->registry['pages']['home'] ?? 0);
        $notfound = (int) ($this->registry['pages']['notfound'] ?? 0);
        rex_sql::factory()->setQuery(
            'UPDATE ' . rex::getTable('yrewrite_domain') . ' SET start_id = ?, notfound_id = ? WHERE mount_id = 0 AND (start_id = 0 OR notfound_id = 0)',
            [$home, $notfound],
        );
        rex_yrewrite::generateConfig();
        rex_yrewrite::deleteCache();
    }

    private function saveRegistry(): void
    {
        rex_config::set('demo_starter', 'registry', $this->registry);
    }
}
