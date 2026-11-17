Die Demo ist ein Lehrstück: Jede Seite der Website erklärt ein Stück REDAXO und ist selbst damit gebaut. Hier steht, wo Sie die Bausteine im Backend finden. Am besten öffnen Sie die [Website]({{frontend}}) in einem zweiten Fenster und vergleichen.

### 1. Struktur – der Seitenbaum
[Struktur]({{structure}}) zeigt Kategorien und Artikel. Öffnen Sie die [Startseite]({{article:home}}): Unter „Inhalt“ stehen die Slices – Bühne, Text, Karten, Text mit Bild. Unter „Metadaten“ finden Sie die Felder, die die Demo angelegt hat: Kurztext, Teaserbild und „Im Hauptmenü zeigen“.

### 2. Templates – das Gerüst
[Templates]({{templates}}): „Layout“ ist das Template aller Seiten. „Teil: Navigation“ und „Teil: Fußbereich“ sind inaktiv und werden im Layout mit `REX_TEMPLATE[key=…]` eingebunden. Die Dateien liegen auch im AddOn unter `data/templates/` – mit ausführlichen Kommentaren.

### 3. Module – die Bausteine
[Module]({{modules}}): neun Module, jedes zeigt eine Technik – `REX_VALUE`, `REX_MEDIA`, `REX_MEDIALIST`, `REX_LINK`, Markdown aus dem Core, die Struktur-API (Karten), YOrm (Termine, Kontaktformular). Quelltext mit Kommentaren in `data/modules/<schlüssel>/`.

### 4. Medien
Im [Medienpool]({{mediapool}}) liegen die Bilder in der Kategorie „Demo“; der Titel dient als Alternativtext. Der [Media Manager]({{media_manager}}) hat vier Typen (`demo_hero`, `demo_split`, `demo_card`, `demo_thumb`): verkleinern, zuschneiden, WebP.

### 5. Metainfos
Unter [Metainfo]({{metainfo}}) sehen Sie die Felder für Artikel (`art_…`) und Kategorien (`cat_…`). So erweitern Sie Seiten um eigene Angaben, ohne Code zu ändern.

### 6. Sprachen und Adressen
[Sprachen]({{languages}}): Deutsch und Englisch. Jede Seite gibt es in beiden Sprachen. [YRewrite]({{yrewrite}}) erzeugt daraus sprechende Adressen wie `/de/grundlagen/module/` und bringt im Artikel unter „SEO“ Titel und Beschreibung mit.

### 7. Daten und Formulare (YForm)
Die [Termine]({{yform_events}}) sind eine eigene Tabelle – neue Einträge erscheinen sofort auf der Seite „Daten & Formulare“. Das [Kontaktformular]({{article:contact}}) speichert in die [Kontaktanfragen]({{yform_messages}}). Felder und Prüfungen bearbeiten Sie unter [Tabellen]({{yform_tables}}). Ausgegeben wird mit YOrm-Modellen aus `lib/Model/`.

### Ändern erlaubt
Probieren Sie alles aus. „Demo einrichten“ ergänzt Fehlendes, überschreibt aber nie, was Sie geändert haben.
