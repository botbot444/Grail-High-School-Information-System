# Assets and Icons

> Last updated: 2026-09-18
> Update this file when icon fonts, webfonts, or global front-end assets change.

---

## Overview

The icon fonts are **vendored into the repository** under `public/fonts/` and linked with Blade's `asset()`
helper — they are *not* pulled from a CDN. Nothing has to be installed or built for icons to render: a fresh
clone serves them straight from `public/`.

Text webfonts (Figtree, Inter, JetBrains Mono, Plus Jakarta Sans) and the Tailwind CDN script used by the
standalone redesigned pages are still loaded from the internet — see *Still remote* below.

---

## Vendored icon fonts

| Font | Version | Files (`public/fonts/`) | Size |
| ---- | ------- | ----------------------- | ---- |
| Font Awesome Free — **solid family only** | 6.5.1 | `fontawesome/css/fontawesome.min.css` | 80,795 B |
| | | `fontawesome/css/solid.min.css` | 572 B |
| | | `fontawesome/webfonts/fa-solid-900.woff2` | 156,496 B |
| | | `fontawesome/webfonts/fa-solid-900.ttf` | 419,720 B |
| Material Symbols Outlined (variable, weight 100–700) | — | `material-symbols/material-symbols.css` | 599 B |
| | | `material-symbols/MaterialSymbolsOutlined.woff2` | 3,980,460 B |
| | | `material-symbols/MaterialSymbolsOutlined.ttf` | 10,678,200 B |

**Font Awesome caveat:** only the *solid* family was vendored. `far-*` (regular) and `fab-*` (brands) classes
will render as empty boxes. If a regular or brand icon is ever needed, the matching `regular.min.css` /
`brands.min.css` plus their webfonts have to be added — do not point the link back at a CDN.

**Material Symbols** is self-hosted with its own `@font-face` (`font-weight: 100 700`, woff2 + ttf sources) and
re-declares the `.material-symbols-outlined` helper class that the Google Fonts stylesheet used to provide.
Variation axes (`FILL`, `wght`, `GRAD`, `opsz`) are set per element with `font-variation-settings`; global
defaults live in `resources/css/app.css` (`.material-symbols-outlined`, ~line 682).

---

## Where each page loads them

Every `<head>` that needs an icon font links it explicitly with `asset()`:

| File | Font Awesome | Material Symbols |
| ---- | ------------ | ---------------- |
| `resources/views/layouts/app.blade.php` | lines 12–13 | line 17 |
| `resources/views/layouts/student.blade.php` | — | line 12 |
| `resources/views/welcome.blade.php` | — | line 9 |
| `resources/views/auth/login.blade.php` | — | line 9 |
| `resources/views/auth/parent-register.blade.php` | — | line 9 |
| `resources/views/auth/registration-submitted.blade.php` | — | line 9 |
| `resources/views/admin/fees/receipt.blade.php` | lines 9–10 | — |

`resources/views/layouts/parent.blade.php` and `resources/views/layouts/teacher.blade.php` only
`@extends('layouts.app')`, so the parent and teacher portals inherit the local links from the `app` layout.
`layouts/guest.blade.php` does **not** load either icon font (Breeze auth screens that need icons — login,
parent registration — declare their own `<head>` instead).

Font Awesome is currently used in exactly **one** view: `admin/fees/receipt.blade.php`
(`fa-solid fa-print`, `fa-solid fa-arrow-left`). Every other screen (admin, parent, teacher, student chrome)
uses Material Symbols ligature names such as `<span class="material-symbols-outlined">school</span>`.

---

## Still remote (internet required)

These are the remaining external requests, so a page that needs them is not offline-capable yet:

| Asset | Loaded by |
| ----- | --------- |
| Figtree (`fonts.bunny.net`) | `layouts/app.blade.php`, `layouts/guest.blade.php`, `admin/fees/receipt.blade.php` |
| Inter + JetBrains Mono (Google Fonts) | `layouts/app.blade.php` |
| Inter + JetBrains Mono (Google Fonts) | `layouts/student.blade.php` |
| Plus Jakarta Sans (Google Fonts) | `welcome.blade.php`, `auth/login.blade.php`, `auth/parent-register.blade.php`, `auth/registration-submitted.blade.php` |
| `cdn.tailwindcss.com` (Tailwind Play CDN) | the four standalone redesigned pages above |

The four standalone pages compile their theme from an inline `tailwind.config` object next to that CDN script.
Replacing the CDN script with the Vite-built `resources/css/app.css` bundle would make them fully self-contained;
that is a **code task, not yet done**, and is the remaining follow-up for the offline/icons work.

---

## Gotchas

- **Always link with `asset('fonts/...')`** — do not re-add `cdnjs.cloudflare.com/ajax/libs/font-awesome/...` or the
  Google Fonts `css2?family=Material+Symbols+Outlined` stylesheet. Those are what the local copies replaced.
- **File names are case-sensitive on Linux hosts** — the shipped names are `MaterialSymbolsOutlined.woff2` /
  `.ttf`; the `@font-face` in `material-symbols.css` references them relative to that stylesheet, so keep the
  CSS and the font files in the same directory.
- **The fonts are committed to git** (~15 MB total, most of it the Material Symbols `.ttf` fallback). A subsetting
  pass (only the icons the app actually uses) is worthwhile future work; until then, avoid adding more copies.
- **No `Storage::url()`** for these files — they live in `public/`, not on the `public` storage disk, so `asset()`
  (which also tolerates a mismatched `APP_URL`, the same reason `PaymentSubmission::proof_url` uses it) is correct.
- **Verify before removing** a CDN link from a view: check that view's `<head>` for the Material Symbols
  `asset()` line first, otherwise its icons disappear silently.

---

_End of assets and icons documentation._