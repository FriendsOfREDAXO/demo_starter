<?php
/**
 * Modul „Bühne“ – Eingabe.
 * Lektion: REX_MEDIA[id=1 widget=1] zeigt das Medienpool-Feld, REX_LINK[id=1 widget=1] die Linkmap (interner Link).
 */
?>
<div class="form-group"><label for="hero-1">Überschrift</label>
    <input class="form-control" id="hero-1" type="text" name="REX_INPUT_VALUE[1]" value="REX_VALUE[1]"></div>
<div class="form-group"><label for="hero-2">Text</label>
    <textarea class="form-control" id="hero-2" rows="3" name="REX_INPUT_VALUE[2]">REX_VALUE[2]</textarea></div>
<div class="form-group"><label>Bild</label>REX_MEDIA[id=1 widget=1 types=jpg,jpeg,png,webp]</div>
<div class="form-group"><label>Link zu</label>REX_LINK[id=1 widget=1]</div>
<div class="form-group"><label for="hero-3">Beschriftung des Links</label>
    <input class="form-control" id="hero-3" type="text" name="REX_INPUT_VALUE[3]" value="REX_VALUE[3]"></div>
