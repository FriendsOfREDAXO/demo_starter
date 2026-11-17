<?php /** Modul „Termine“ – Eingabe. Die Termine selbst pflegt die Redaktion unter YForm → Tabellen → Termine. */ ?>
<div class="form-group"><label for="ev-1">Überschrift (optional)</label>
    <input class="form-control" id="ev-1" type="text" name="REX_INPUT_VALUE[1]" value="REX_VALUE[1]"></div>
<div class="form-group"><label for="ev-2">Höchstens anzeigen</label>
    <input class="form-control" id="ev-2" type="number" min="1" max="50" name="REX_INPUT_VALUE[2]" value="<?= (int) 'REX_VALUE[2]' ?: 6 ?>"></div>
<p class="help-block"><a href="<?= rex_url::backendPage('yform/manager/data_edit', ['table_name' => 'rex_demo_event']) ?>">Termine bearbeiten</a></p>
