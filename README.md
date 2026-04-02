# aS.c Core Tools

A lightweight WordPress plugin for common customizations of WordPress. Replaces numerous common plugins.

This plugin is developed for public use as Free and Open Source Software (FOSS).

**Requires:** PHP 8.1+, WordPress 5.x
**Tested with:** WordPress 6.9.1, PHP 8.3
**Version:** 1.2.0

---

## Changelog

### 1.2.0

- **XML-RPC** – Disabling XML-RPC is applied on all requests (including public `xmlrpc.php`) via `Front/WordPressSettings`, not only when the admin bootstrap runs.
- **Hide Login** – Login, logout, lost password, and related flows use the custom slug URL in the browser (e.g. `yoursite.com/your-slug/` and query args on that path). Direct access to `wp-login.php` is redirected to the home page, which pairs cleanly with nginx rules that block `/wp-login.php` while allowing the slug.
- **Local fonts** – `font-family` names derived from filenames now strip trailing `-semibold` / `_semibold` and `-semi-bold` / `_semi-bold` suffixes (same as other weight/style tokens), so the family name matches other weights in the set. `font-weight` was already inferred for `semibold` / `semi-bold` as **600**.
- **Documentation** – `Admin/WordPressSettings` docblocks describe automatic update email suppression and point to `Front/WordPressSettings` for XML-RPC; README updated for Hide Login slug-only behavior and how local `@font-face` CSS is generated from font filenames.

### 1.1.0

- **Social sharing (front-end CSS)** – Normalized styles for `.asc-core-tools-share-icon` on both `<a>` (network links) and `<button>` (Copy link) so spacing, flex alignment, box model, and theme overrides produce a consistent layout and size across all share controls.

---

## Features

### WordPress Settings (tab)
- **Disable XML-RPC** – Turn off the XML-RPC endpoint.
- **Hide WordPress Login** – Use a custom URL for login; WordPress login runs at that slug (e.g. `yoursite.com/your-slug/` and `?action=lostpassword` on the same path). Direct requests to `wp-login.php` redirect to the home page. Unauthenticated access to `wp-admin` redirects to the home page; REST API is restricted when enabled.
- **Disable auto-update emails** – Stop WordPress from sending email notifications after automatic core, plugin, or theme updates.
- **Disable Autosave (Heartbeat API)** – Disable autosave or set the autosave interval.
- **Disable Revisions** – Disable revisions or limit how many are kept per post.
- **Disable Comments** – Disable comments and pings site-wide; removes the Comments menu when there are no comments.

### Display Settings (tab)
- **Shortcodes** – `[asc_core_tools_year]` (current year); `[asc_core_tools_social_sharing]` when social sharing is enabled.
- **Local Fonts** – Upload font files to `wp-content/fonts`; enable to load a generated `fonts.css` on the front-end. Use **Scan for fonts** and **Generate CSS** on the Display tab to list files and build `@font-face` rules. When “Enable local fonts” is on, the settings page auto-scans the directory and regenerates `fonts.css` on load.
- **Local Font Awesome** – Option to host Font Awesome locally from the plugin; on by default.
- **Social sharing** – Optional sharing bar (Facebook, LinkedIn, Bluesky, X, Email, Copy link) on selected post types or via shortcode; you can choose which networks to show (all enabled by default). Requires clipboard.js and Font Awesome, which are included in the vendor directory and locally hosted by default.
- **Ninja Forms** – Optional customization (enable to load custom CSS). **Note:** In Ninja Forms settings, set **Opinionated Styles** to **Light** for default styling.

### Database Maintenance
- **Delete obsolete data** – Removes oembed cache posts, obsolete post meta (e.g. old slug, edit lock), transients, and session options. Also, when enabled, old trash, draft, and revision posts by age, configured by days.
- **Delete orphaned data** – Removes post meta, terms, term meta, term taxonomy, and term relationships that no longer reference valid posts or terms. Runs table by table.
- **Optimize tables** – Runs OPTIMIZE TABLE on core WordPress tables to reclaim space and defragment after deletions.

---

## Installation

1. Install via WordPress admin (Plugins → Add New → upload or search) or copy the plugin folder into `wp-content/plugins/`.
2. Activate the plugin.
3. Go to **Settings → aS.c Core Tools** to configure WordPress, Display, and Database options.

---

## Hide Login

When **Hide Login** is enabled and a **login page slug** is set (e.g. `your-slug`):

- The login page is available at `https://yoursite.com/your-slug/` (or `?your-slug` without pretty permalinks). The browser stays on that URL; flows such as lost password or logged-out use query arguments on the same slug (e.g. `your-slug/?action=lostpassword`).
- Direct access to `wp-login.php` redirects to the home page.
- Unauthenticated requests to `wp-admin` redirect to the home page; the REST API returns 401 when Hide Login is enabled.

Save settings after changing the slug so rewrite rules are updated.

---

## Local Fonts

When **Enable local fonts** is on:

- The plugin enqueues `wp-content/fonts/fonts.css` on the front-end (when the file exists).
- On the **Display** tab you can **Scan for fonts** to list font files and `fonts.css` in that directory, and **Generate CSS** to build or update `fonts.css` with `@font-face` rules from the scanned font files (woff2, woff, ttf, otf, eot).
- Each time you load the settings page with local fonts enabled, the directory is scanned and `fonts.css` is regenerated automatically so it stays in sync.

Upload your font files to `wp-content/fonts`, then enable the option and use **Generate CSS** (or reload the settings page) to create the stylesheet.

### How `fonts.css` / `@font-face` is generated from file names

Each font file in `wp-content/fonts` becomes one `@font-face` block. **No font metadata is read from inside the file**—the plugin uses the **file name** (without extension) only.

**`font-family` (display name)**
- Take the base file name (e.g. `MyBrand-SemiBold` from `MyBrand-SemiBold.woff2`).
- Remove a **single trailing token** after `-` or `_` if it matches one of (case-insensitive; longer hyphenated forms are listed so they win over shorter words inside them): `extra-light`, `extralight`, `extra-bold`, `extrabold`, `semi-bold`, `semibold`, `regular`, `normal`, `italic`, `medium`, `thin`, `black`, `light`, `bold`.
- Replace remaining hyphens and underscores with spaces for the CSS `font-family` value (e.g. `MyBrand-SemiBold` → `MyBrand` after stripping `Semibold`, or `My-Brand-semibold` → `My Brand`).
- If that leaves nothing usable, the full basename is used.

**`font-weight`**
Inferred by **substring** in the base name (first match wins, case-insensitive):

| If the name contains | `font-weight` |
|----------------------|---------------|
| `thin` | 100 |
| `extralight` or `extra-light` | 200 |
| `light` | 300 |
| `medium` | 500 |
| `semibold` or `semi-bold` | 600 |
| `bold` | 700 |
| `extrabold` or `extra-bold` | 800 |
| `black` | 900 |
| `regular` or `normal` | 400 |
| *(none of the above—no weight-related keyword in the name)* | 400 |

Use **`regular`** or **`normal`** in the file name for an explicit regular face (e.g. `SourceSans3-Regular.woff2`), or use a basename **without** any of the weight keywords above—both give **`font-weight: 400`**. Any name that does not match an earlier row defaults to **400**.

**`font-style`**
`italic` if the base name contains `italic`; otherwise `normal`.

**`src`**
Points at the file under your site’s content URL, with a `format(...)` hint from the extension (e.g. `woff2`, `truetype` for `.ttf`).

Name files so the basename reflects the family and weight you want (e.g. `SourceSans3-Regular.woff2`, `SourceSans3-SemiBold.woff2`, `SourceSans3-BoldItalic.woff2`).

---

## Social Sharing

When **Enable Social Sharing** is on (Display Settings tab):

- **Where it appears** – The sharing bar is shown on the post types you list in **Set Social Sharing Post Types** (e.g. `post,page`). You can also place it anywhere with the shortcode `[asc_core_tools_social_sharing]`.
- **Networks** – You can choose which networks to show: Facebook, LinkedIn, Bluesky, X, Email, and Copy link. All are enabled by default; uncheck any you do not want in the sharing bar.
- **Copy link** – Uses the page URL; a short message is shown when the link is copied. Clipboard.js is loaded by the plugin when social sharing is enabled.
- **Icons** – Share icons use Font Awesome (loaded by the plugin; see Local Font Awesome). Ensure Local Font Awesome is enabled or that Font Awesome is available on the front-end for icons to display.

---

## Links

- **Repository:** [github.com/asolutioncompany/asc-core-tools](https://github.com/asolutioncompany/asc-core-tools)
- **License:** GPL v3 or later
