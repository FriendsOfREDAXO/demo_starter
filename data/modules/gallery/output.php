<?php
/**
 * Modul „Bildergalerie“ – Ausgabe.
 * Lektion:
 *  - REX_MEDIALIST[id=1] ist eine kommagetrennte Liste von Dateinamen.
 *  - Vorschaubilder über den Media-Manager-Typ „demo_thumb“ (quadratisch zugeschnitten), Großansicht über „demo_hero“.
 *  - <dialog> ist ein natives HTML-Element: Die Großansicht braucht kein Lightbox-Skript (command/commandfor).
 */
$files = array_filter(explode(',', 'REX_MEDIALIST[id=1]'));
$uid = 'gal-REX_SLICE_ID';
?>
<section class="ncss-container site-section ncss-stack">
    <?php if ('' !== 'REX_VALUE[id=1]'): ?><h2>REX_VALUE[id=1]</h2><?php endif; ?>
    <ul class="site-gallery" role="list">
        <?php foreach ($files as $i => $file):
            $media = rex_media::get($file);
            if (!$media) { continue; }
            $id = $uid . '-' . $i; ?>
            <li>
                <button type="button" class="site-gallery-item" command="show-modal" commandfor="<?= $id ?>">
                    <img src="<?= rex_media_manager::getUrl('demo_thumb', $file) ?>" alt="<?= rex_escape($media->getTitle()) ?>" width="400" height="400" loading="lazy">
                </button>
                <dialog id="<?= $id ?>" class="ncss-modal site-lightbox" aria-label="<?= rex_escape($media->getTitle()) ?>">
                    <button type="button" class="ncss-dialog-close" command="close" commandfor="<?= $id ?>" aria-label="<?= rex_i18n::msg('demo_starter_close') ?>"><span class="ncss-icon ncss-icon-close" aria-hidden="true"></span></button>
                    <img src="<?= rex_media_manager::getUrl('demo_hero', $file) ?>" alt="<?= rex_escape($media->getTitle()) ?>" loading="lazy">
                    <?php if ('' !== $media->getTitle()): ?><p class="ncss-text-sm"><?= rex_escape($media->getTitle()) ?></p><?php endif; ?>
                </dialog>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
