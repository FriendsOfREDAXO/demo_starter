<?php

/**
 * Rundgang: Wo steckt was? Text aus docs/tour.<sprache>.md, Platzhalter {{…}} werden zu Backend-Links.
 * Lektion: rex_markdown (Core) rendert Markdown; rex_url::backendPage() baut Links ins Backend.
 */

$addon = rex_addon::get('demo_starter');
$lang = str_starts_with(rex_i18n::getLocale(), 'de') ? 'de' : 'en';
$text = (string) rex_file::get($addon->getPath('docs/tour.' . $lang . '.md'));
$pages = (array) (rex_config::get('demo_starter', 'registry', [])['pages'] ?? []);
$article = static fn (string $id): string => rex_url::backendPage('content/edit', ['article_id' => (int) ($pages[$id] ?? 0), 'clang' => 1, 'mode' => 'edit'], false);

$links = [
    '{{structure}}' => rex_url::backendPage('structure', [], false),
    '{{article:home}}' => $article('home'),
    '{{article:contact}}' => $article('contact'),
    '{{templates}}' => rex_url::backendPage('templates', [], false),
    '{{modules}}' => rex_url::backendPage('modules/modules', [], false),
    '{{mediapool}}' => rex_url::backendPage('mediapool/media', [], false),
    '{{media_manager}}' => rex_url::backendPage('media_manager/types', [], false),
    '{{metainfo}}' => rex_url::backendPage('metainfo/articles', [], false),
    '{{languages}}' => rex_url::backendPage('system/lang', [], false),
    '{{yform_events}}' => rex_url::backendPage('yform/manager/data_edit', ['table_name' => 'rex_demo_event'], false),
    '{{yform_messages}}' => rex_url::backendPage('yform/manager/data_edit', ['table_name' => 'rex_demo_message'], false),
    '{{yform_tables}}' => rex_url::backendPage('yform/manager/table_edit', [], false),
    '{{yrewrite}}' => rex_url::backendPage('yrewrite/domains', [], false),
    '{{frontend}}' => rex_getUrl(rex_article::getSiteStartArticleId()),
];

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('demo_starter_page_tour'), false);
$fragment->setVar('body', '<div class="demo-starter-tour" style="max-width:52rem">' . rex_markdown::factory()->parse(strtr($text, $links)) . '</div>', false);
echo $fragment->parse('core/page/section.php');
