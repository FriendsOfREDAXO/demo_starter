<?php
/**
 * Modul „Karten“ – Eingabe.
 * Lektion: rex_category_select ist die Kategorie-Auswahl des Cores (mit Rechten und Sprachen).
 * Die Karten füllen sich selbst: neue Seite in der Kategorie anlegen – schon erscheint sie hier.
 */
$select = new rex_category_select(false, false, true, false);
$select->setName('REX_INPUT_VALUE[1]');
$select->setId('cards-1');
$select->setAttribute('class', 'form-control');
$select->setSelected('REX_VALUE[1]');
?>
<div class="form-group"><label for="cards-2">Überschrift (optional)</label>
    <input class="form-control" id="cards-2" type="text" name="REX_INPUT_VALUE[2]" value="REX_VALUE[2]"></div>
<div class="form-group"><label for="cards-1">Seiten aus Kategorie</label><?= $select->get() ?></div>
<p class="help-block">Bild und Kurztext jeder Karte kommen aus den Metadaten der Seite (Reiter „Metadaten“: Teaserbild, Kurztext).</p>
