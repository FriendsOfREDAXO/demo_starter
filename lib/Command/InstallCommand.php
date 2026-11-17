<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\DemoStarter\Command;

use FriendsOfRedaxo\DemoStarter\Installer;
use rex_console_command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * php bin/console demo_starter:install
 *
 * Richtet die Demo ein oder ergänzt sie. Werden dabei AddOns neu installiert, startet der Befehl sich einmal neu,
 * damit REDAXO sie vollständig lädt (wie ein neuer Seitenaufruf im Backend).
 */
final class InstallCommand extends rex_console_command
{
    protected function configure(): void
    {
        $this->setDescription('Richtet die Starter-Demo ein (erneut ausführbar, überschreibt keine eigenen Änderungen)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $installer = new Installer();
        try {
            if ($installer->installPackages() && !getenv('DEMO_STARTER_SECOND_RUN')) {
                $this->print($io, $installer);
                $io->note('AddOns installiert – zweiter Durchgang …');
                // REDAXO speichert Paket-Zustände erst am Ende des Aufrufs – vor dem Neustart ausdrücklich sichern
                \rex_config::save();
                putenv("DEMO_STARTER_SECOND_RUN=1");
                passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(\rex_path::backend('bin/console')) . ' demo_starter:install', $code);
                return (int) $code;
            }
            $installer->installContent();
        } catch (\Throwable $e) {
            $this->print($io, $installer);
            $io->error($e->getMessage());
            return 1;
        }
        $this->print($io, $installer);
        $io->success('Demo eingerichtet.');
        return 0;
    }

    private function print(SymfonyStyle $io, Installer $installer): void
    {
        foreach ($installer->log as [$level, $text]) {
            $io->writeln(match ($level) { 'skip' => '<comment>– ' . $text . '</comment>', 'warn' => '<error>! ' . $text . '</error>', default => '✓ ' . $text });
        }
        $installer->log = [];
    }
}
