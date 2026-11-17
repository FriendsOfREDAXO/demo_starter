<?php
/**
 * Modul „Termine“ – Ausgabe.
 * Lektion YOrm: Kein SQL im Modul. Event::upcoming() (siehe lib/Model/Event.php) liefert die nächsten Termine als
 * Objekte; getTitle() wählt die Sprache. IntlDateFormatter formatiert das Datum passend zur Sprache der Seite.
 */
use FriendsOfRedaxo\DemoStarter\Model\Event;

$events = Event::upcoming(max(1, (int) 'REX_VALUE[id=2]' ?: 6));
$locale = 'en' === rex_clang::getCurrent()->getCode() ? 'en_GB' : 'de_DE';
$format = new IntlDateFormatter($locale, IntlDateFormatter::FULL, IntlDateFormatter::NONE);
?>
<section class="ncss-container site-section ncss-stack">
    <?php if ('' !== 'REX_VALUE[id=1]'): ?><h2>REX_VALUE[id=1]</h2><?php endif; ?>
    <?php if (0 === count($events)): ?>
        <p class="ncss-text-muted"><?= rex_i18n::msg('demo_starter_events_none') ?></p>
    <?php else: ?>
    <ol class="site-events" role="list">
        <?php foreach ($events as $event): ?>
            <li class="site-event">
                <time class="site-event-date" datetime="<?= $event->getDate()->format('Y-m-d') ?>">
                    <span class="site-event-day"><?= $event->getDate()->format('d') ?></span>
                    <span class="site-event-month"><?= rex_escape((new IntlDateFormatter($locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, null, null, 'MMM'))->format($event->getDate())) ?></span>
                </time>
                <div class="ncss-stack ncss-stack--tight">
                    <h3><?= rex_escape($event->getTitle()) ?></h3>
                    <p class="ncss-text-sm ncss-text-muted"><?= rex_escape((string) $format->format($event->getDate())) ?><?= '' !== $event->getPlace() ? ' · ' . rex_escape($event->getPlace()) : '' ?></p>
                    <p><?= rex_escape($event->getText()) ?></p>
                </div>
            </li>
        <?php endforeach; ?>
    </ol>
    <?php endif; ?>
</section>
