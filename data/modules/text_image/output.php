<?php
/**
 * Modul „Text mit Bild“ – Ausgabe.
 * Lektion:
 *  - rex_media::get($datei) liefert das Medium mit seinen Metadaten. Der Titel aus dem Medienpool dient als
 *    Alternativtext – so pflegt die Redaktion ihn einmal zentral statt in jedem Modul.
 *  - Breite und Höhe ins <img> schreiben (getWidth/getHeight), damit beim Laden nichts springt.
 */
$media = rex_media::get('REX_MEDIA[id=1]');
$imageFirst = 'right' !== 'REX_VALUE[id=3]';
$figure = $media ? '<div class="ncss-split-media"><img class="site-img" src="' . rex_media_manager::getUrl('demo_split', $media->getFileName()) . '"'
    . ' alt="' . rex_escape($media->getTitle()) . '" width="1200" height="' . (int) round(1200 * $media->getHeight() / max(1, $media->getWidth())) . '" loading="lazy"></div>' : '';
?>
<section class="ncss-container site-section">
    <div class="ncss-split-container">
        <div class="ncss-split">
            <?= $imageFirst ? $figure : '' ?>
            <div class="ncss-split-content ncss-stack ncss-prose">
                <?php if ('' !== 'REX_VALUE[id=1]'): ?><h2>REX_VALUE[id=1]</h2><?php endif; ?>
                <?= rex_markdown::factory()->parse(<<<'REX_MD_END'
REX_VALUE[id=2 output=html]
REX_MD_END) ?>
            </div>
            <?= $imageFirst ? '' : $figure ?>
        </div>
    </div>
</section>
