<?php
/**
 * Modul „Text“ – Eingabe.
 * Lektion: REX_VALUE[n] sind die Felder eines Slices (1 bis 20). Was im Formular „REX_INPUT_VALUE[n]“ heißt,
 * speichert REDAXO als value1 … value20.
 */
?>
<div class="form-group">
    <label for="text-1">Überschrift (optional)</label>
    <input class="form-control" id="text-1" type="text" name="REX_INPUT_VALUE[1]" value="REX_VALUE[1]">
</div>
<div class="form-group">
    <label for="text-2">Text</label>
    <textarea class="form-control" id="text-2" rows="10" name="REX_INPUT_VALUE[2]">REX_VALUE[2]</textarea>
    <p class="help-block">Markdown: **fett**, *kursiv*, [Link](https://redaxo.org), Listen mit „- “, Überschriften mit „### “.</p>
</div>
