<?php

/**
 * Backend-Seite der Demo (Untermenüs aus package.yml: Einrichten, Rundgang).
 * Lektion: rex_be_controller::includeCurrentPageSubPath() lädt pages/<unterseite>.php.
 */

echo rex_view::title(rex_i18n::msg('demo_starter_title'));
rex_be_controller::includeCurrentPageSubPath();
