<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\DemoStarter;

use rex_addon;
use rex_install_package_add;
use rex_install_packages;
use rex_package;
use rex_package_manager;
use rex_version;
use RuntimeException;

/**
 * Holt fehlende AddOns über den Installer – mit Versionsbedingung statt fester Version.
 *
 * Warum? Bisherige Demos merken sich eine bestimmte Datei des Installers (z. B. „yform 4.1.1“). Erscheint eine neue
 * Version oder verschwindet die alte, bricht die Demo. Hier steht nur die Bedingung („^5.0“): genommen wird die neueste
 * stabile Version, die sie erfüllt. Lässt sich diese nicht installieren (z. B. braucht sie ein neueres REDAXO oder
 * PHP), versucht der Resolver die nächstältere. Abhängigkeiten zu weiteren AddOns löst er rekursiv auf.
 */
final class Packages
{
    /** @var list<string> Protokoll für die Ausgabe */
    public array $log = [];

    /**
     * @param array<string, string> $constraints AddOn => Versionsbedingung, z. B. ['yform' => '^5.0']
     */
    public function ensure(array $constraints): void
    {
        foreach ($constraints as $key => $constraint) {
            $this->ensureOne($key, $constraint, []);
        }
    }

    /** @param list<string> $chain Aufrufkette (gegen Kreise) */
    private function ensureOne(string $key, string $constraint, array $chain): void
    {
        if (in_array($key, $chain, true)) {
            return;
        }
        $addon = rex_addon::get($key);

        // 1. Schon da und passend? Dann nur noch installieren/aktivieren, falls nötig.
        if ($addon->isAvailable() && rex_version::matchesConstraints($addon->getVersion(), $constraint)) {
            $this->log[] = sprintf('%s %s ist vorhanden (%s)', $key, $addon->getVersion(), $constraint);
            return;
        }
        if (rex_addon::exists($key) && rex_version::matchesConstraints($addon->getVersion(), $constraint)) {
            $this->installLocal($key, $chain);
            return;
        }
        if (rex_addon::exists($key) && $addon->isAvailable()) {
            throw new RuntimeException(sprintf('%s %s ist installiert, die Demo braucht %s. Bitte %s aktualisieren.', $key, $addon->getVersion(), $constraint, $key));
        }

        // 2. Kandidaten aus dem Installer: passende, stabile Versionen, neueste zuerst
        $packages = rex_install_packages::getAddPackages();
        if (!isset($packages[$key])) {
            throw new RuntimeException(sprintf('%s ist im Installer nicht verfügbar.', $key));
        }
        $candidates = [];
        foreach ($packages[$key]['files'] as $fileId => $file) {
            $version = (string) $file['version'];
            if (rex_version::isUnstable($version) || !rex_version::matchesConstraints($version, $constraint)) {
                continue;
            }
            $candidates[$fileId] = $version;
        }
        uasort($candidates, static fn (string $a, string $b): int => rex_version::compare($a, $b, '<') ? 1 : (rex_version::compare($a, $b, '>') ? -1 : 0));
        if ([] === $candidates) {
            throw new RuntimeException(sprintf('Keine stabile Version von %s erfüllt %s.', $key, $constraint));
        }

        // 3. Neueste zuerst versuchen; passt sie nicht zu diesem System, die nächstältere
        $errors = [];
        foreach ($candidates as $fileId => $version) {
            try {
                $message = (new rex_install_package_add())->run($key, (int) $fileId);
                if ('' !== $message) {
                    throw new RuntimeException($message);
                }
                rex_package_manager::synchronizeWithFileSystem();
                $this->log[] = sprintf('%s %s heruntergeladen (%s)', $key, $version, $constraint);
                $this->installLocal($key, $chain);
                return;
            } catch (\Throwable $e) {
                $errors[] = $version . ': ' . strip_tags($e->getMessage());
                $this->log[] = sprintf('%s %s passt nicht – versuche ältere Version', $key, $version);
            }
        }
        throw new RuntimeException(sprintf("%s konnte nicht installiert werden:\n%s", $key, implode("\n", $errors)));
    }

    /** Heruntergeladenes Paket installieren – vorher dessen eigene Abhängigkeiten (package.yml) auflösen */
    private function installLocal(string $key, array $chain): void
    {
        $package = rex_package::get($key);
        foreach ((array) ($package->getProperty('requires')['packages'] ?? []) as $dep => $depConstraint) {
            $depKey = explode('/', (string) $dep)[0];
            if (!rex_addon::get($depKey)->isAvailable()) {
                $this->ensureOne($depKey, (string) $depConstraint ?: '*', [...$chain, $key]);
            }
        }
        $manager = rex_package_manager::factory($package);
        if (!$package->isInstalled() && !$manager->install()) {
            throw new RuntimeException(strip_tags($manager->getMessage()));
        }
        if (!$package->isAvailable() && !$manager->activate()) {
            throw new RuntimeException(strip_tags($manager->getMessage()));
        }
        $this->log[] = sprintf('%s %s installiert und aktiviert', $key, $package->getVersion());
    }
}
