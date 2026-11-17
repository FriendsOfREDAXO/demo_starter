<?php
/**
 * Modul „Kontaktformular“ – Ausgabe.
 * Lektion YOrm-Formular:
 *  - Message::create() erzeugt einen neuen, leeren Datensatz der Tabelle rex_demo_message.
 *  - getForm() baut das Formular aus der Tabellendefinition: Felder, Beschriftungen („translate:…“ → Sprachdatei),
 *    Pflichtfelder und E-Mail-Prüfung sind dort hinterlegt – nicht hier im Modul.
 *  - executeForm() prüft und speichert. War das erfolgreich (actions_executed), folgt die Benachrichtigung per E-Mail.
 *  - Ein CSRF-Schutz steckt in YForm schon drin.
 */
use FriendsOfRedaxo\DemoStarter\Model\Message;

$message = Message::create();
$yform = $message->getForm();
$yform->setObjectparams('form_action', rex_getUrl(rex_article::getCurrentId()) . '#contact-REX_SLICE_ID');
$yform->setObjectparams('form_name', 'contact_REX_SLICE_ID');
$yform->setObjectparams('submit_btn_label', rex_i18n::msg('demo_starter_form_send'));
$yform->setObjectparams('real_field_names', true);
$yform->setObjectparams('form_showformafterupdate', 0);
$yform->setObjectparams('form_ytemplate', 'bootstrap');
$yform->setObjectparams('rex_yform_set', ['bootstrap' => ['form_label_type' => 'plain']]);

$html = $message->executeForm($yform);
$sent = (bool) ($yform->objparams['actions_executed'] ?? false);
if ($sent && $message->getId()) {
    // Gespeicherten Datensatz neu laden und die Benachrichtigung verschicken
    Message::get($message->getId())?->notify('REX_VALUE[id=1]');
}
?>
<section id="contact-REX_SLICE_ID" class="ncss-container site-section site-form">
    <?php if ($sent): ?>
        <div class="site-notice site-notice--tip" role="status">
            <p><?= '' !== 'REX_VALUE[id=2]' ? 'REX_VALUE[id=2]' : rex_i18n::msg('demo_starter_form_thanks') ?></p>
        </div>
    <?php else: ?>
        <?= $html ?>
    <?php endif; ?>
</section>
