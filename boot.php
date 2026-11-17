<?php

/**
 * demo_starter – boot.php
 *
 * Lektion: boot.php läuft bei jedem Aufruf (Frontend und Backend), solange das AddOn aktiv ist. Hier gehört nur hin,
 * was immer gebraucht wird – zum Beispiel die YOrm-Modellklassen mit ihren Tabellen zu verbinden.
 */

use FriendsOfRedaxo\DemoStarter\Model\Event;
use FriendsOfRedaxo\DemoStarter\Model\Message;

if (rex_addon::get('yform')->isAvailable()) {
    rex_yform_manager_dataset::setModelClass('rex_demo_event', Event::class);
    rex_yform_manager_dataset::setModelClass('rex_demo_message', Message::class);
}

// Nach einem Update des AddOns: Admins auf das Aktualisieren der Inhalte hinweisen (nicht automatisch ausführen –
// niemand soll überrascht werden). „applied_version“ setzt der Installer am Ende des Einrichtens.
if (rex::isBackend() && rex::getUser()?->isAdmin()) {
    rex_extension::register('PAGE_TITLE_SHOWN', static function (rex_extension_point $ep) {
        $addon = rex_addon::get('demo_starter');
        $applied = (string) $addon->getConfig('applied_version', '');
        $installed = [] !== (array) ($addon->getConfig('registry', [])['pages'] ?? []);
        if (!$installed || $applied === $addon->getVersion() || 'demo_starter' === rex_be_controller::getCurrentPagePart(1)) {
            return null;
        }
        return $ep->getSubject() . rex_view::info(rex_i18n::rawMsg('demo_starter_update_notice', rex_escape($addon->getVersion()), rex_escape($applied ?: '?'), rex_url::backendPage('demo_starter/setup')));
    });
}
