<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\DemoStarter\Model;

use rex_mailer;
use rex_yform_manager_dataset;

/**
 * Kontaktanfrage – YOrm-Modell der Tabelle rex_demo_message.
 *
 * Lektion YOrm-Formulare:
 *  - Das Formular entsteht aus der Tabellendefinition (Felder, Prüfungen) – keine Pipe-Notation, kein Formular-Modul.
 *    $message = Message::create(); $yform = $message->getForm(); … $message->executeForm($yform, callback)
 *  - Gespeichert wird wie jeder Datensatz; die Anfragen erscheinen im Backend unter YForm → Tabellen.
 *  - Nach dem Speichern verschickt notify() eine E-Mail über PHPMailer (Teil von REDAXO).
 */
final class Message extends rex_yform_manager_dataset
{
    public function notify(string $to): bool
    {
        if ('' === $to) {
            return false;
        }
        $mail = new rex_mailer();
        $mail->addAddress($to);
        $mail->addReplyTo((string) $this->getValue('email'), (string) $this->getValue('name'));
        $mail->Subject = 'Kontaktanfrage: ' . $this->getValue('name');
        $mail->Body = "Name: {$this->getValue('name')}\nE-Mail: {$this->getValue('email')}\n\n{$this->getValue('message')}";
        return $mail->send();
    }
}
