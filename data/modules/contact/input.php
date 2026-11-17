<?php /** Modul „Kontaktformular“ – Eingabe. Felder und Prüfungen stehen in der Tabelle „Kontaktanfragen“ (YForm → Tabellen). */ ?>
<div class="form-group"><label for="ct-1">Empfänger der Benachrichtigung</label>
    <input class="form-control" id="ct-1" type="email" name="REX_INPUT_VALUE[1]" value="REX_VALUE[1]" placeholder="info@example.com"></div>
<div class="form-group"><label for="ct-2">Text nach dem Absenden</label>
    <input class="form-control" id="ct-2" type="text" name="REX_INPUT_VALUE[2]" value="REX_VALUE[2]"></div>
<p class="help-block"><a href="<?= rex_url::backendPage('yform/manager/data_edit', ['table_name' => 'rex_demo_message']) ?>">Eingegangene Anfragen ansehen</a></p>
