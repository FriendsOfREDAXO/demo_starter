<?php
/**
 * Modul „Video“ – Ausgabe.
 * Lektion: Das native <video>-Element reicht – keine Einbettung fremder Dienste, kein Cookie-Banner nötig.
 * Dateien aus dem Medienpool liefert rex_url::media() aus; preload="none" lädt erst beim Abspielen.
 */
$video = rex_media::get('REX_MEDIA[id=1]');
$poster = 'REX_MEDIA[id=2]';
?>
<?php if ($video): ?>
<figure class="ncss-container site-section site-video">
    <video controls preload="none" playsinline<?= '' !== $poster ? ' poster="' . rex_media_manager::getUrl('demo_hero', $poster) . '"' : '' ?>>
        <source src="/<?= ltrim(rex_url::media($video->getFileName()), './') ?>" type="<?= rex_escape($video->getType()) ?>">
    </video>
    <?php if ('' !== 'REX_VALUE[id=1]'): ?><figcaption class="ncss-text-sm ncss-text-muted">REX_VALUE[id=1]</figcaption><?php endif; ?>
</figure>
<?php endif; ?>
