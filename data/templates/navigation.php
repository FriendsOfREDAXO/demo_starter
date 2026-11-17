<?php
/**
 * Template „Navigation“ (Schlüssel: navigation, inaktiv – wird vom Layout eingebunden).
 *
 * Lektion:
 *  - rex_category::getRootCategories(true) liefert die Hauptkategorien, die online sind.
 *  - $category->getChildren(true) liefert die Unterkategorien – so entsteht ein Menü mit Untermenü.
 *  - getValue('cat_menu') liest ein Metainfo-Feld der Kategorie: „Im Hauptmenü zeigen“.
 *    So steuern Redakteure das Menü, ohne das Template anzufassen.
 *  - $category->getUrl() baut die Adresse – mit YRewrite sprechend (/de/grundlagen/templates).
 *  - rex_clang::getAll(true) liefert die aktiven Sprachen für den Sprachwechsler.
 */

$current = rex_article::getCurrent();
$path = $current ? $current->getPathAsArray() : [];   // IDs der Elternkategorien – für „aktiv“
$currentId = $current?->getId();
$isActive = static fn (rex_category $cat): bool => $cat->getId() === $currentId || in_array($cat->getId(), $path, true);
$home = rex_article::getSiteStartArticle();
?>
<header class="ncss-topbar">
    <div class="ncss-topbar-inner">
        <a class="ncss-topbar-brand site-brand" href="<?= $home?->getUrl() ?>">
            <span class="site-logo" aria-hidden="true">R</span> REDAXO Starter
        </a>
        <nav class="ncss-nav" aria-label="<?= rex_i18n::msg('demo_starter_nav_main') ?>">
            <button type="button" class="ncss-nav-toggle" command="show-modal" commandfor="navPanel" aria-label="<?= rex_i18n::msg('demo_starter_nav_open') ?>">
                <span class="ncss-icon ncss-icon-menu" aria-hidden="true"></span>
            </button>
            <dialog id="navPanel" class="ncss-nav-panel ncss-offcanvas ncss-offcanvas--start">
                <button type="button" class="ncss-dialog-close" command="close" commandfor="navPanel" aria-label="<?= rex_i18n::msg('demo_starter_nav_close') ?>">
                    <span class="ncss-icon ncss-icon-close" aria-hidden="true"></span>
                </button>
                <ul class="ncss-nav-list">
                    <?php foreach (rex_category::getRootCategories(true) as $cat):
                        if (!$cat->getValue('cat_menu')) {
                            continue;   // Metainfo „Im Hauptmenü zeigen“ nicht gesetzt (z. B. Service-Seiten)
                        }
                        $children = $cat->getChildren(true);
                        ?>
                        <li class="ncss-nav-item">
                            <?php if ([] === $children): ?>
                                <a href="<?= $cat->getUrl() ?>"<?= $isActive($cat) ? ' aria-current="page"' : '' ?>><?= rex_escape($cat->getName()) ?></a>
                            <?php else: ?>
                                <details class="ncss-nav-dropdown">
                                    <summary<?= $isActive($cat) ? ' aria-current="true"' : '' ?>><?= rex_escape($cat->getName()) ?></summary>
                                    <ul class="ncss-nav-submenu">
                                        <li><a href="<?= $cat->getUrl() ?>"><?= rex_i18n::msg('demo_starter_nav_overview') ?></a></li>
                                        <?php foreach ($children as $child): ?>
                                            <li><a href="<?= $child->getUrl() ?>"<?= $isActive($child) ? ' aria-current="page"' : '' ?>><?= rex_escape($child->getName()) ?></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </details>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </dialog>
        </nav>
        <div class="ncss-topbar-actions">
            <button type="button" id="themeToggle" class="site-theme-toggle"
                    data-label-system="<?= rex_i18n::msg('demo_starter_theme_system') ?>"
                    data-label-light="<?= rex_i18n::msg('demo_starter_theme_light') ?>"
                    data-label-dark="<?= rex_i18n::msg('demo_starter_theme_dark') ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true" class="i-system"><rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/></svg>
                <svg viewBox="0 0 24 24" aria-hidden="true" class="i-light"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                <svg viewBox="0 0 24 24" aria-hidden="true" class="i-dark"><path d="M21 13a9 9 0 1 1-10-10 7 7 0 0 0 10 10z"/></svg>
            </button>
            <nav class="site-lang" aria-label="<?= rex_i18n::msg('demo_starter_nav_lang') ?>">
                <?php foreach (rex_clang::getAll(true) as $lang):
                    // Gleicher Artikel in der anderen Sprache – rex_getUrl(Artikel, Sprache)
                    $url = rex_getUrl($currentId ?? $home?->getId(), $lang->getId()); ?>
                    <a href="<?= $url ?>" hreflang="<?= rex_escape($lang->getCode()) ?>" lang="<?= rex_escape($lang->getCode()) ?>"<?= $lang->getId() === rex_clang::getCurrentId() ? ' aria-current="true"' : '' ?>
                       title="<?= rex_escape($lang->getName()) ?>"><?= rex_escape(strtoupper($lang->getCode())) ?></a>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>
</header>
