<?php

/**
 * Einrichten – zwei Schritte, weil frisch installierte AddOns erst im nächsten Aufruf vollständig geladen sind:
 *   1. AddOns holen (Installer, Versionsbedingungen aus data/recipe.yml)
 *   2. Inhalte anlegen (nach automatischem Neuladen der Seite)
 * Lektion: Aktionen nur per POST mit CSRF-Token (rex_csrf_token) – nie über einen einfachen Link.
 */

use FriendsOfRedaxo\DemoStarter\Installer;

$addon = rex_addon::get('demo_starter');
$csrf = rex_csrf_token::factory('demo_starter_setup');
$step = rex_request('step', 'string', '');
$log = [];
$message = '';

if ('' !== $step && !$csrf->isValid()) {
    $message = rex_view::error(rex_i18n::msg('csrf_token_invalid'));
} elseif ('' !== $step) {
    $installer = new Installer();
    try {
        if ('packages' === $step && $installer->installPackages()) {
            // Neu installierte AddOns: weiter mit Schritt 2 in einem neuen Aufruf (Formular sendet sich selbst ab)
            rex_set_session('demo_starter_log', $installer->log);
            $message = rex_view::info(rex_i18n::msg('demo_starter_setup_continue'))
                . '<form method="post" action="' . rex_url::currentBackendPage() . '" data-auto-submit>' . $csrf->getHiddenField()
                . '<input type="hidden" name="step" value="content"><noscript><button class="btn btn-primary">' . rex_i18n::msg('demo_starter_setup_button') . '</button></noscript></form>';
        } else {
            $installer->installContent();
            $message = rex_view::success(rex_i18n::msg('demo_starter_setup_done'));
        }
    } catch (Throwable $e) {
        $message = rex_view::error(rex_i18n::msg('demo_starter_setup_error') . ' ' . rex_escape($e->getMessage()));
    }
    $log = array_merge((array) rex_session('demo_starter_log', 'array', []), $installer->log);
    if ('content' === $step || !str_contains($message, 'data-auto-submit')) {
        rex_unset_session('demo_starter_log');
    }
}

echo $message;

// Was die Demo braucht – mit dem Stand dieser Installation
$recipe = rex_string::yamlDecode((string) rex_file::get($addon->getPath('data/recipe.yml')));
$rows = '';
foreach ((array) $recipe['packages'] as $key => $constraint) {
    $package = rex_addon::get($key);
    $state = $package->isAvailable()
        ? '<span class="label label-success">' . rex_escape($package->getVersion()) . '</span>'
        : '<span class="label label-default">–</span>';
    $rows .= '<tr><td><code>' . rex_escape($key) . '</code></td><td><code>' . rex_escape((string) $constraint) . '</code></td><td>' . $state . '</td></tr>';
}
$installed = [] !== (array) (rex_config::get('demo_starter', 'registry', [])['pages'] ?? []);

$body = '<p>' . rex_i18n::msg('demo_starter_setup_intro') . '</p>'
    . '<p class="text-muted">' . rex_i18n::msg('demo_starter_setup_safe') . '</p>'
    . '<h3>' . rex_i18n::msg('demo_starter_setup_packages') . '</h3>'
    . '<table class="table table-condensed" style="max-width:36rem"><tbody>' . $rows . '</tbody></table>'
    . '<form method="post" action="' . rex_url::currentBackendPage() . '">' . $csrf->getHiddenField()
    . '<input type="hidden" name="step" value="packages">'
    . '<button type="submit" class="btn btn-primary"><i class="rex-icon fa-magic" aria-hidden="true"></i> '
    . rex_i18n::msg($installed ? 'demo_starter_setup_button_again' : 'demo_starter_setup_button') . '</button> '
    . ($installed ? '<a class="btn btn-default" href="' . rex_getUrl(rex_article::getSiteStartArticleId()) . '" target="_blank" rel="noopener">' . rex_i18n::msg('demo_starter_setup_open') . '</a>' : '')
    . '</form>'
    . '<p class="help-block">' . rex_i18n::rawMsg('demo_starter_setup_cli') . '</p>';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('demo_starter_page_setup'), false);
$fragment->setVar('body', $body, false);
echo $fragment->parse('core/page/section.php');

if ([] !== $log) {
    $items = '';
    foreach ($log as [$level, $text]) {
        $icon = ['skip' => 'fa-minus text-muted', 'warn' => 'fa-exclamation-triangle text-warning'][$level] ?? 'fa-check text-success';
        $items .= '<li><i class="rex-icon ' . $icon . '" aria-hidden="true"></i> ' . rex_escape($text) . '</li>';
    }
    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('demo_starter_setup_log'), false);
    $fragment->setVar('body', '<ul class="list-unstyled">' . $items . '</ul>', false);
    echo $fragment->parse('core/page/section.php');
}
?>
<script nonce="<?= rex_response::getNonce() ?>">
document.querySelectorAll('form[data-auto-submit]').forEach(function (form) { form.submit(); });
</script>
