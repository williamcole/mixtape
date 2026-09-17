# Mixtape

A WordPress child theme that turns Gutenberg block content into a page-turning digital magazine.

Built for [Mixtape](http://mixtape-zine.com), an online zine. The goal was to let the editorial team lay out an issue in the WordPress block editor — the tool they already knew — and have it render as a flipbook with page-turn animation, a generated table of contents, thumbnails, and zoom, without anyone hand-maintaining a page manifest or exporting to PDF.

**Stack:** WordPress · PHP · child theme of [Hamilton](https://andersnoren.se/teman/hamilton-wordpress-theme/) · Turn.js · Backbone · `theme.json`

---

## The idea

Most WordPress flipbook plugins take a finished PDF and slice it into images. That works, but it costs you everything WordPress is good at: the content stops being text, search engines can't read it, and every correction means re-exporting the whole issue.

Mixtape goes the other way. The issue is authored as ordinary blocks in a single WordPress page. A custom page template intercepts that content before the theme renders it, walks the block tree, and hands the rendered pages to a JavaScript reading app. Content stays content — editable, selectable, and stored as blocks in the database.

## How an issue is structured

The magazine's structure *is* the block structure. Nothing is configured separately:

```
Page (WordPress)
├── Group (flex layout)  ← a SECTION — its List View name becomes a TOC entry
│   ├── Group            ← a PAGE
│   └── Group            ← a PAGE
├── Group (flex layout)  ← next SECTION
│   ├── Group            ← a PAGE
...
```

- A **top-level Group block with a flex layout** is a section.
- Each **Group nested inside it** is one physical page of the magazine.
- The section's name in the block editor's List View becomes its table-of-contents entry.

That last part is the piece I like most. WordPress lets an editor rename any block in the List View, and that name is stored in the block's `metadata.name` attribute. The template reads it, so the table of contents is generated from names the editor typed while laying out the issue — no separate TOC field to maintain, and no way for the TOC to drift out of sync with the pages it points at.

## How the template works

`megazine-page-template.php` replaces the theme's rendering entirely:

1. `get_the_content()` → `parse_blocks()` to get the block tree as a PHP array.
2. Walk the top level, matching `core/group` blocks whose `attrs.layout.type` is `flex` — these are sections. Increment the section counter, record `metadata.name`.
3. Walk each section's `innerBlocks`, matching `core/group` — these are pages. Increment the page counter, and remember the first page number of each section so the TOC can link to it.
4. `render_block()` on each page block, so WordPress renders the page's real content with its own block renderers — core blocks, shortcodes, everything.
5. Emit the accumulated section/page map into the JavaScript app's `FlipbookSettings.table` so the reader's table of contents is built server-side at render time.

The result is that the flipbook's pagination and navigation derive from the content itself. Add a Group block to a section and the issue gains a page; the page count, the slider, the thumbnails, and the TOC offsets all follow automatically.

## Design system

`theme.json` carries the zine's visual identity as design tokens, so editors pick from the magazine's palette and type scale rather than free-styling each page:

- **Palette** locked down with `custom: false` and `customGradient: false` — peach, pink, saffron, and orange, plus neutrals. No arbitrary hex values.
- **Display typography** — Berkshire Swash for h1–h3, Ostrich Sans Rounded for h4, Open Sans for body and small headings.
- **Fluid type** enabled, with a six-step scale from `small` to `huge`.
- **Per-block styling** for `core/quote` (saffron border, peach background) and `core/button`, plus a rounded variation for `core/image`.

---

## Installation

Requires the [Hamilton](https://andersnoren.se/teman/hamilton-wordpress-theme/) parent theme.

1. Install Hamilton, then install Mixtape as a child theme and activate it.
2. Supply the Turn.js vendor files (see below).
3. Create a page, set its template to **Megazine flipbook template**, and lay out the issue using the section/page structure above.

### Vendor dependencies — not included

The reading app is built on the commercially licensed **Turn.js 5th release** and its Megazine UI kit from [turnjs.com](http://turnjs.com). Those files are not redistributed in this repository. To run the theme, obtain a Turn.js license and place the files at:

```
assets/js/turn.min.js      # Turn.js 5.0.0
assets/js/app.js           # Megazine UI kit (Backbone views)
assets/css/app.css         # Turn.js stylesheet
```

The original code in this repository is `megazine-page-template.php`, `theme.json`, `functions.php`, and the child theme wiring. The flipbook rendering engine, its Backbone view layer, and its stylesheet are Emmanuel Garcia's work, licensed separately.

---

## Known limitations

Documented rather than hidden, since this shipped as a working zine and not a reference implementation:

- Section names are interpolated directly into a JavaScript string literal. A name containing an apostrophe will break the table of contents — `esc_js()` on that value is the fix.
- The block matcher assumes every top-level Group carries a `layout` attribute and every section carries `metadata.name`. Groups that don't will emit PHP notices on 8.x.
- The template hard-codes its script tags in `<head>` — including a bundled jQuery 2.0.3 — rather than registering them through `wp_enqueue_script()`. That bypasses WordPress's dependency resolution and pins an old jQuery.
- The viewport meta tag sets `user-scalable=no`, which blocks pinch-zoom and fails WCAG 1.4.4. The flipbook has its own zoom control, but that isn't an adequate substitute for readers who rely on browser zoom.
- Flipbook dimensions are fixed at 1280×920 in `FlipbookSettings`.
