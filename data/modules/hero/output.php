<?php
/**
 * Modul „Bühne“ – Ausgabe.
 * Lektion:
 *  - Bilder nie direkt aus /media ausliefern, sondern über den Media Manager: rex_media_manager::getUrl(Typ, Datei).
 *    Der Typ „demo_hero“ (von der Demo angelegt) verkleinert das Bild auf 1920 px und wandelt es in WebP um.
 *  - REX_LINK[id=1] enthält die ID des Zielartikels; rex_getUrl() macht daraus die Adresse.
 */
$image = 'REX_MEDIA[id=1]';
$linkId = (int) 'REX_LINK[id=1]';
?>
<section class="ncss-hero site-hero">
    <?php if ('' !== $image): ?>
    <div class="ncss-hero-media" style="background-image: url('<?= rex_media_manager::getUrl('demo_hero', $image) ?>');"></div>
    <?php endif; ?>
    <div class="ncss-hero-content ncss-hero-content--transparent ncss-stack">
        <h1 class="ncss-text-inherit">REX_VALUE[id=1]</h1>
        <?php if ('' !== 'REX_VALUE[id=2]'): ?><p class="ncss-text-inherit site-hero-text">REX_VALUE[id=2]</p><?php endif; ?>
        <?php if ($linkId > 0): ?>
            <p><a class="ncss-btn site-btn" href="<?= rex_getUrl($linkId) ?>"><?= '' !== 'REX_VALUE[id=3]' ? 'REX_VALUE[id=3]' : rex_escape(rex_article::get($linkId)?->getName() ?? '') ?></a></p>
        <?php endif; ?>
    </div>
</section>
