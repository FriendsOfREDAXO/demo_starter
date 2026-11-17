# Starter-Demo für REDAXO

Eine zweisprachige Beispiel-Website, die **nur mit dem auskommt, was REDAXO mitbringt** – plus YForm und YRewrite.
Sie ist zugleich ein **Lehrstück**: Jede Seite erklärt ein Stück REDAXO und ist selbst damit gebaut. Unten auf jeder
Seite steht, welches Template und welche Module sie verwendet.

- Struktur, Templates (mit Teil-Templates), neun kommentierte Module, Medienpool, Media Manager, Metainfos
- Deutsch und Englisch, sprechende Adressen mit YRewrite
- YForm-Tabellen mit **YOrm**-Modellen: Termine als Liste, Kontaktformular aus der Tabellendefinition
- Frontend mit [nativecss](https://github.com/skerbis/nativecss) – natives CSS ohne Framework und Build-Schritt,
  **hell und dunkel** (Systemeinstellung oder Schalter)

## Installation

1. AddOn installieren (Installer oder Ordner nach `redaxo/src/addons/demo_starter` kopieren)
2. **Starter-Demo → Einrichten → Demo einrichten** – oder auf der Konsole:
   ```
   php bin/console demo_starter:install
   ```

Mehr ist nicht nötig. Fehlende AddOns holt die Demo selbst.

## Warum diese Demo nicht veraltet

Bisherige Demos merken sich **feste Versionen** der AddOns (eine bestimmte Datei im Installer) und spielen einen
**SQL-Dump** ein. Beides bricht, sobald AddOns sich weiterentwickeln. Diese Demo macht es anders:

| | bisher | Starter-Demo |
|---|---|---|
| AddOns | feste Version („yform 4.1.1“) | **Bedingung** (`^5.0`) – die neueste passende stabile Version, notfalls die nächstältere |
| Inhalte | Datenbank-Dump überschreibt die Installation | **Rezept** (`data/recipe.yml`), angelegt über die Schnittstellen von REDAXO |
| Templates, Module | im Dump | als **Dateien** (`data/templates`, `data/modules`), mit Kommentaren |
| Wiederholen | überschreibt alles | **gefahrlos**: ergänzt Fehlendes, überschreibt nie eigene Änderungen |
| Frontend | Bootstrap 3 / UIkit | natives CSS, keine Framework-Version, die veraltet |

**Update-sicher:** Templates und Module werden beim erneuten Einrichten nur ersetzt, wenn sie seit dem letzten Mal
unverändert sind (Prüfsumme). Seiteninhalte schreibt die Demo nur beim ersten Anlegen – danach gehören sie der Redaktion.

## Aufbau

```
data/recipe.yml          Was die Demo anlegt: AddOns, Sprachen, Metainfos, Medientypen, Medien,
                         YForm-Tabellen, Templates, Module, Seiten mit Inhalten (de/en)
data/templates/*.php     Templates (Layout, Navigation, Fußbereich)
data/modules/<key>/      Module: module.yml, input.php, output.php
data/media/              Bilder und Video, Nachweise in CREDITS.md
lib/Packages.php         Holt AddOns über den Installer – Versionsbedingung statt fester Version
lib/Installer.php        Setzt das Rezept um (rex_category_service, rex_content_service, rex_media_service, YForm-API …)
lib/Model/               YOrm-Modelle: Event, Message
assets/                  site.css (Gestaltung), theme.js (hell/dunkel), ncss/ (nativecss)
docs/tour.*.md           Rundgang im Backend
```

## Eigene Demo bauen

Das Rezept ist absichtlich lesbar. Für eine eigene Demo oder einen Projekt-Starter: `data/recipe.yml`, Templates,
Module und Medien anpassen – der Installer bleibt derselbe.

## Lizenz

MIT. nativecss: MIT (© Thomas Skerbis, KLXM Crossmedia). Bildnachweise: `data/media/CREDITS.md`.
