<?php
/**
 * Modul „Karten“ – Ausgabe.
 * Lektion:
 *  - rex_category::get($id)->getChildren(true) liefert die Unterkategorien, getArticles(true) die Artikel – jeweils
 *    nur die, die online sind. Die Startseite der Kategorie selbst lassen wir aus.
 *  - getValue('art_teaser') und getValue('art_image') lesen Metainfo-Felder, die die Demo angelegt hat.
 */
$category = rex_category::get((int) 'REX_VALUE[id=1]');
$items = [];
if ($category) {
    foreach ($category->getChildren(true) as $child) {
        $items[] = $child->getStartArticle();
    }
    foreach ($category->getArticles(true) as $article) {
        if (!$article->isStartArticle()) {
            $items[] = $article;
        }
    }
}
?>
<section class="ncss-container site-section ncss-stack">
    <?php if ('' !== 'REX_VALUE[id=2]'): ?><h2>REX_VALUE[id=2]</h2><?php endif; ?>
    <div class="ncss-grid site-cards" style="--ncss-grid-min: 16rem;">
        <?php foreach ($items as $item):
            $image = (string) $item->getValue('art_image'); ?>
            <article class="ncss-card ncss-border ncss-radius-lg ncss-shadow-sm ncss-shadow-hover-md site-card">
                <?php if ('' !== $image): ?>
                    <img class="ncss-card-media" src="<?= rex_media_manager::getUrl('demo_card', $image) ?>" alt="" width="800" height="500" loading="lazy">
                <?php endif; ?>
                <div class="ncss-card-body ncss-stack ncss-stack--tight">
                    <h3><a class="site-card-link" href="<?= $item->getUrl() ?>"><?= rex_escape($item->getName()) ?></a></h3>
                    <p class="ncss-text-sm ncss-text-muted"><?= rex_escape((string) $item->getValue('art_teaser')) ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
