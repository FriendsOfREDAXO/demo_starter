<?php /** Modul „Hinweis“ – Eingabe. Lektion: ein <select> speichert einen festen Wert (hier die Art des Hinweises). */ ?>
<div class="form-group">
    <label for="notice-3">Art</label>
    <select class="form-control" id="notice-3" name="REX_INPUT_VALUE[3]">
        <?php foreach (['tip' => 'Tipp', 'backend' => 'Im Backend ansehen', 'info' => 'Info'] as $value => $label): ?>
            <option value="<?= $value ?>"<?= 'REX_VALUE[3]' === $value ? ' selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="form-group">
    <label for="notice-1">Überschrift</label>
    <input class="form-control" id="notice-1" type="text" name="REX_INPUT_VALUE[1]" value="REX_VALUE[1]">
</div>
<div class="form-group">
    <label for="notice-2">Text (Markdown)</label>
    <textarea class="form-control" id="notice-2" rows="5" name="REX_INPUT_VALUE[2]">REX_VALUE[2]</textarea>
</div>
