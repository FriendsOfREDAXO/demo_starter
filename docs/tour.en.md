The demo is a lesson: every page of the website explains a piece of REDAXO and is built with it. This page shows where to find the building blocks in the backend. Best open the [website]({{frontend}}) in a second window and compare.

### 1. Structure – the page tree
[Structure]({{structure}}) shows categories and articles. Open the [home page]({{article:home}}): “Content” lists the slices – stage, text, cards, text with image. “Metadata” holds the fields the demo created: short text, teaser image and “Show in main menu”.

### 2. Templates – the frame
[Templates]({{templates}}): “Layout” is the template of all pages. “Part: navigation” and “part: footer” are inactive and included in the layout with `REX_TEMPLATE[key=…]`. The files also live in the add-on under `data/templates/` – with detailed comments.

### 3. Modules – the building blocks
[Modules]({{modules}}): nine modules, each showing a technique – `REX_VALUE`, `REX_MEDIA`, `REX_MEDIALIST`, `REX_LINK`, Markdown from the core, the structure API (cards), YOrm (events, contact form). Commented source in `data/modules/<key>/`.

### 4. Media
The [media pool]({{mediapool}}) holds the images in the category “Demo”; the title serves as alt text. The [media manager]({{media_manager}}) has four types (`demo_hero`, `demo_split`, `demo_card`, `demo_thumb`): resize, crop, WebP.

### 5. Metainfo
[Metainfo]({{metainfo}}) lists the fields for articles (`art_…`) and categories (`cat_…`). That is how you add your own details to pages without changing code.

### 6. Languages and URLs
[Languages]({{languages}}): German and English. Every page exists in both. [YRewrite]({{yrewrite}}) turns this into readable URLs like `/en/basics/modules/` and adds title and description under “SEO” in each article.

### 7. Data and forms (YForm)
The [events]({{yform_events}}) are a table of their own – new entries show up right away on “Data & forms”. The [contact form]({{article:contact}}) stores into [contact requests]({{yform_messages}}). Edit fields and validation under [tables]({{yform_tables}}). Output uses YOrm models from `lib/Model/`.

### Change things
Try everything. “Set up demo” adds what is missing but never overwrites what you changed.
