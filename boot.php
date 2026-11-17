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
