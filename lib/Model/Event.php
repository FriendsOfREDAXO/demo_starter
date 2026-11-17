<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\DemoStarter\Model;

use rex_clang;
use rex_yform_manager_collection;
use rex_yform_manager_dataset;

/**
 * Veranstaltung – YOrm-Modell der Tabelle rex_demo_event.
 *
 * Lektion YOrm:
 *  - Eine Klasse pro Tabelle, abgeleitet von rex_yform_manager_dataset, in boot.php mit
 *    rex_yform_manager_dataset::setModelClass('rex_demo_event', Event::class) verbunden.
 *  - Abfragen ohne SQL: Event::query()->where(…)->orderBy(…)->find() liefert eine Sammlung von Event-Objekten.
 *  - Eigene Methoden (getTitle, upcoming …) halten Logik aus Modulen und Templates heraus.
 *  - Zweisprachig ohne weiteres AddOn: Felder title / title_en, die Methode wählt passend zur Sprache.
 */
final class Event extends rex_yform_manager_dataset
{
    /** @return rex_yform_manager_collection<self> kommende Termine, nächster zuerst */
    public static function upcoming(int $limit = 10): rex_yform_manager_collection
    {
        return self::query()
            ->where('status', 1)
            ->whereRaw('date >= CURDATE()')
            ->orderBy('date')
            ->limit($limit)
            ->find();
    }

    public function getTitle(): string
    {
        return $this->localized('title');
    }

    public function getText(): string
    {
        return $this->localized('text');
    }

    public function getPlace(): string
    {
        return (string) $this->getValue('place');
    }

    public function getDate(): \DateTimeImmutable
    {
        return new \DateTimeImmutable((string) $this->getValue('date'));
    }

    /** Englischer Wert, wenn die Seite englisch ist und er gepflegt wurde – sonst der deutsche */
    private function localized(string $field): string
    {
        $value = 'en' === rex_clang::getCurrent()->getCode() ? (string) $this->getValue($field . '_en') : '';
        return '' !== $value ? $value : (string) $this->getValue($field);
    }
}
