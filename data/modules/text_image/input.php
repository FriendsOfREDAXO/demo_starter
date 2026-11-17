<?php /** Modul „Text mit Bild“ – Eingabe. Lektion: Felder kombinieren – Text (Markdown), Bild, Position. */ ?>
<div class="form-group"><label for="ti-1">Überschrift</label>
    <input class="form-control" id="ti-1" type="text" name="REX_INPUT_VALUE[1]" value="REX_VALUE[1]"></div>
<div class="form-group"><label for="ti-2">Text (Markdown)</label>
    <textarea class="form-control" id="ti-2" rows="8" name="REX_INPUT_VALUE[2]">REX_VALUE[2]</textarea></div>
<div class="form-group"><label>Bild</label>REX_MEDIA[id=1 widget=1 types=jpg,jpeg,png,webp]</div>
<div class="form-group"><label for="ti-3">Bild</label>
    <select class="form-control" id="ti-3" name="REX_INPUT_VALUE[3]">
        <option value="left"<?= 'left' === 'REX_VALUE[3]' ? ' selected' : '' ?>>links vom Text</option>
        <option value="right"<?= 'right' === 'REX_VALUE[3]' ? ' selected' : '' ?>>rechts vom Text</option>
    </select></div>
