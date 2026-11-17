<?php
/**
 * Template „Layout“ (Schlüssel: layout) – das Gerüst jeder Seite.
 *
 * Lektion:
 *  - Ein Template ist PHP + HTML. Es läuft bei jedem Seitenaufruf.
 *  - REX_ARTICLE[] gibt den Inhalt des aktuellen Artikels aus (alle Slices der Spalte/ctype 1).
 *  - REX_TEMPLATE[key=…] bindet ein anderes Template ein – hier Navigation und Fußbereich.
 *    Teil-Templates sind „inaktiv“: Redakteure können sie keinem Artikel zuweisen.
 *  - rex_article::getCurrent() und rex_clang::getCurrent() liefern Artikel und Sprache dieses Aufrufs.
 */

$article = rex_article::getCurrent();
$clang = rex_clang::getCurrent();

// Texte der Oberfläche (Menü, Formular …) kommen aus den Sprachdateien des AddOns (lang/de_de.lang, lang/en_gb.lang).
rex_i18n::setLocale('en' === $clang->getCode() ? 'en_gb' : 'de_de', false);

// Seitentitel und Beschreibung: YRewrite bringt SEO-Felder pro Artikel mit (Titel, Beschreibung, Bild).
$seo = rex_addon::get('yrewrite')->isAvailable() ? new rex_yrewrite_seo() : null;

// Dateien des AddOns liegen unter /assets/addons/demo_starter/ (wird bei der Installation kopiert)
$asset = static fn (string $file): string => '/' . ltrim(rex_url::addonAssets('demo_starter', $file), './') . '?v=' . (int) @filemtime(rex_path::addonAssets('demo_starter', $file));
?><!doctype html>
<html lang="<?= rex_escape($clang->getCode()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <script src="<?= $asset('theme.js') ?>"></script>
    <?= $seo ? $seo->getTags() : '<title>' . rex_escape($article?->getName() ?? '') . '</title>' ?>
    <link rel="stylesheet" href="<?= $asset('ncss/ncss.css') ?>">
    <link rel="stylesheet" href="<?= $asset('site.css') ?>">
    <script src="<?= $asset('ncss/js/command-fallback.js') ?>" defer></script>
    <script src="<?= $asset('ncss/js/nav.js') ?>" defer></script>
</head>
<body>
<a class="ncss-skip-link" href="#main"><?= rex_i18n::msg('demo_starter_skip') ?></a>

REX_TEMPLATE[key=navigation]

<main id="main" class="site-main">
    <?php
    // Erster Slice ein „Hero“? Dann ohne Seitenüberschrift – sonst Überschrift aus dem Artikelnamen.
    $first = rex_article_slice::getFirstSliceForArticle($article->getId(), $clang->getId());
    $startsWithHero = $first && 'hero' === (new rex_module($first->getModuleId()))->getKey();
    if (!$startsWithHero): ?>
    <header class="site-pagehead ncss-container">
        <h1><?= rex_escape($article->getName()) ?></h1>
        <?php if ('' !== (string) $article->getValue('art_teaser')): ?>
        <p class="site-lead"><?= rex_escape((string) $article->getValue('art_teaser')) ?></p>
        <?php endif; ?>
    </header>
    <?php endif; ?>

    REX_ARTICLE[]
</main>

REX_TEMPLATE[key=footer]

</body>
</html>
