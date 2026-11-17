<?php
/**
 * Template „Fußbereich“ (Schlüssel: footer, inaktiv – wird vom Layout eingebunden).
 *
 * Lektion:
 *  - Die Service-Links (Impressum, Datenschutz …) sind die Artikel der Kategorie „Service“. Welche Kategorie das ist,
 *    steht nicht im Code, sondern in der Einstellung des AddOns (rex_config) – die Demo legt sie beim Einrichten fest.
 *  - „So ist diese Seite gebaut“ liest die Slices des aktuellen Artikels aus (rex_article_slice) und zeigt, welches
 *    Template und welche Module beteiligt sind – ein Blick hinter die Kulissen, direkt auf der Seite.
 */

$current = rex_article::getCurrent();
$service = rex_category::get((int) rex_config::get('demo_starter', 'service_category', 0));

// Template und Module dieser Seite
$template = new rex_template($current->getTemplateId());   // Template-Objekt: getKey(), getId()
// rex_module kennt Schlüssel und ID; den Namen lesen wir direkt aus der Tabelle rex_module.
$moduleNames = array_column(rex_sql::factory()->getArray('SELECT id, name FROM ' . rex::getTable('module')), 'name', 'id');
$modules = [];
foreach (rex_article_slice::getSlicesForArticle($current->getId(), rex_clang::getCurrentId()) as $slice) {
    $module = new rex_module($slice->getModuleId());
    $modules[$module->getKey() ?? (string) $module->getId()] = (string) ($moduleNames[$module->getId()] ?? '');
}
?>
<footer class="ncss-footer site-footer">
    <div class="ncss-footer-inner ncss-grid" style="--ncss-grid-min: 14rem;">
        <div class="ncss-stack ncss-stack--tight">
            <strong class="site-brand"><span class="site-logo" aria-hidden="true">R</span> REDAXO Starter</strong>
            <p class="ncss-text-sm"><?= rex_i18n::msg('demo_starter_footer_claim') ?></p>
        </div>

        <section class="site-built ncss-stack ncss-stack--tight" aria-labelledby="built-title">
            <h2 id="built-title" class="ncss-text-sm"><?= rex_i18n::msg('demo_starter_built_title') ?></h2>
            <p class="ncss-text-sm">
                <?= rex_i18n::msg('demo_starter_built_template') ?> <code><?= rex_escape($template->getKey() ?? (string) $current->getTemplateId()) ?></code>
                <?php if ([] !== $modules): ?>
                    · <?= rex_i18n::msg('demo_starter_built_modules') ?>
                    <?php foreach ($modules as $key => $name): ?><code title="<?= rex_escape($name) ?>"><?= rex_escape((string) $key) ?></code> <?php endforeach; ?>
                <?php endif; ?>
            </p>
        </section>

        <?php if ($service): ?>
        <nav class="ncss-nav ncss-nav--tree" aria-label="<?= rex_escape($service->getName()) ?>">
            <ul class="ncss-nav-list">
                <?php foreach ($service->getArticles(true) as $item): if ($item->isStartArticle() || $item->getId() === rex_article::getNotfoundArticleId()) { continue; } ?>
                    <li class="ncss-nav-item"><a href="<?= $item->getUrl() ?>"><?= rex_escape($item->getName()) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</footer>
