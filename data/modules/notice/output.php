<?php
/**
 * Modul „Hinweis“ – Ausgabe: ein hervorgehobener Kasten, z. B. „So geht's im Backend“.
 * Lektion: Mit einem Feld (Art) steuert die Redaktion das Aussehen – ohne CSS-Klassen zu kennen.
 */
$kind = in_array('REX_VALUE[3]', ['tip', 'backend', 'info'], true) ? 'REX_VALUE[3]' : 'info';
?>
<aside class="ncss-container site-section">
    <div class="site-notice site-notice--<?= $kind ?>">
        <?php if ('' !== 'REX_VALUE[id=1]'): ?><p class="site-notice-title">REX_VALUE[id=1]</p><?php endif; ?>
        <?= rex_markdown::factory()->parse(<<<'REX_MD_END'
REX_VALUE[id=2 output=html]
REX_MD_END) ?>
    </div>
</aside>
