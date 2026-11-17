<?php
/**
 * Modul „Text“ – Ausgabe.
 * Lektion: REX_VALUE[id=1 output=html] gibt den Wert unverändert aus, ohne „output“ wird er maskiert.
 * rex_markdown gehört zum Core: Markdown wird sicher in HTML umgewandelt (HTML im Text wird maskiert).
 */
$title = 'REX_VALUE[id=1]';
$text = rex_markdown::factory()->parse(<<<'REX_MD_END'
REX_VALUE[id=2 output=html]
REX_MD_END);
?>
<section class="ncss-container site-section ncss-prose">
    <?php if ('' !== $title): ?><h2><?= $title ?></h2><?php endif; ?>
    <?= $text ?>
</section>
