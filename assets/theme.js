/**
 * Hell / Dunkel – Lektion: nativecss rechnet Farben mit light-dark(). Ohne Angabe folgt die Seite dem System.
 * [data-theme="light" | "dark"] auf <html> überschreibt das; die Wahl merkt sich der Browser (localStorage).
 * Dieses Skript wird im <head> OHNE defer geladen, damit die Seite nicht kurz im falschen Theme aufblitzt.
 */
(function () {
  var KEY = 'demo-starter-theme';
  var root = document.documentElement;
  var saved = null;
  try { saved = localStorage.getItem(KEY); } catch (e) { /* privater Modus */ }
  if (saved === 'light' || saved === 'dark') { root.setAttribute('data-theme', saved); }

  document.addEventListener('DOMContentLoaded', function () {
    var button = document.getElementById('themeToggle');
    if (!button) { return; }
    var order = ['system', 'light', 'dark'];
    var current = root.getAttribute('data-theme') || 'system';
    var show = function () {
      button.setAttribute('data-mode', current);
      button.setAttribute('aria-label', button.getAttribute('data-label-' + current));
      button.title = button.getAttribute('data-label-' + current);
    };
    show();
    button.addEventListener('click', function () {
      current = order[(order.indexOf(current) + 1) % order.length];
      if (current === 'system') { root.removeAttribute('data-theme'); } else { root.setAttribute('data-theme', current); }
      try { current === 'system' ? localStorage.removeItem(KEY) : localStorage.setItem(KEY, current); } catch (e) { /* egal */ }
      show();
    });
  });
})();
