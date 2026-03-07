# aS.c Core Tools

A lightweight WordPress plugin for common customizations of WordPress. Replaces numerous common plugins.

This plugin is developed for public use as Free and Open Source Software (FOSS).

**Requires:** PHP 8.1+, WordPress 5.x  
**Tested with:** WordPress 6.9.1, PHP 8.3  
**Version:** 1.0.0

---

## Features

### General
- **Disable XML-RPC** – Turn off the XML-RPC endpoint.
- **Hide WordPress Login** – Use a custom URL for login; `wp-login.php` is gated by a required parameter so only the custom slug works. Unauthenticated access to `wp-admin` redirects to the home page; REST API is restricted when enabled.
- **Disable auto-update emails** – Stop WordPress from sending email notifications after automatic core, plugin, or theme updates.
- **Disable Autosave (Heartbeat API)** – Disable autosave or set the autosave interval.
- **Disable Revisions** – Disable revisions or limit how many are kept per post.
- **Disable Comments** – Disable comments and pings site-wide; removes the Comments menu when there are no comments.

### Features
- **Shortcodes** – `[asc_core_tools_year]` (current year); `[asc_core_tools_social_sharing]` when social sharing is enabled.
- **Local Fonts** – Upload font files to `wp-content/fonts`; enable to load a generated `fonts.css` on the front-end. Use **Scan for fonts** and **Generate CSS** on the Features tab to list files and build `@font-face` rules. When “Enable local fonts” is on, the settings page auto-scans the directory and regenerates `fonts.css` on load.
- **Local Font Awesome** – Option to host Font Awesome locally from the plugin; on by default.
- **Social sharing** – Optional sharing bar (Facebook, LinkedIn, Bluesky, X, Email, Copy link) on selected post types or via shortcode; you can choose which networks to show (all enabled by default). Requires clipboard.js and Font Awesome, which are included in the vendor directory and locally hosted by default.
- **Ninja Forms** – Optional customization (enable to load custom CSS).

### Database
- **Delete obsolete data** – Remove revisions, trash, auto-drafts, oembed cache, orphaned post meta, transients, and similar.
- **Delete orphaned data** – Clean specific tables (postmeta, terms, termmeta, term_taxonomy, term_relationships, terms_and_term_taxonomy).
- **Optimize tables** – Run OPTIMIZE on core WordPress tables.

---

## Installation

1. Install via WordPress admin (Plugins → Add New → upload or search) or copy the plugin folder into `wp-content/plugins/`.
2. Activate the plugin.
3. Go to **Settings → aS.c Core Tools** to configure General, Features, and Database options.

---

## Hide Login

When **Hide Login** is enabled and a **login page slug** is set (e.g. `your-slug`):

- The login page is available at `https://yoursite.com/your-slug/` (or `?your-slug` without pretty permalinks). Visiting that URL redirects to `wp-login.php` with the required parameter so login works normally (with full WordPress styling).
- Direct access to `wp-login.php` without the correct parameter redirects to the home page.
- Unauthenticated requests to `wp-admin` redirect to the home page; the REST API returns 401 when Hide Login is enabled.

Save settings after changing the slug so rewrite rules are updated.

---

## Local Fonts

When **Enable local fonts** is on:

- The plugin enqueues `wp-content/fonts/fonts.css` on the front-end (when the file exists).
- On the **Features** tab you can **Scan for fonts** to list font files and `fonts.css` in that directory, and **Generate CSS** to build or update `fonts.css` with `@font-face` rules from the scanned font files (woff2, woff, ttf, otf, eot).
- Each time you load the settings page with local fonts enabled, the directory is scanned and `fonts.css` is regenerated automatically so it stays in sync.

Upload your font files to `wp-content/fonts`, then enable the option and use **Generate CSS** (or reload the settings page) to create the stylesheet.

---

## Social Sharing

When **Enable Social Sharing** is on (Features tab):

- **Where it appears** – The sharing bar is shown on the post types you list in **Set Social Sharing Post Types** (e.g. `post,page`). You can also place it anywhere with the shortcode `[asc_core_tools_social_sharing]`.
- **Networks** – You can choose which networks to show: Facebook, LinkedIn, Bluesky, X, Email, and Copy link. All are enabled by default; uncheck any you do not want in the sharing bar.
- **Copy link** – Uses the page URL; a short message is shown when the link is copied. Clipboard.js is loaded by the plugin when social sharing is enabled.
- **Icons** – Share icons use Font Awesome (loaded by the plugin; see Local Font Awesome). Ensure Local Font Awesome is enabled or that Font Awesome is available on the front-end for icons to display.

To use only the shortcode and not show the bar on post types automatically, leave **Set Social Sharing Post Types** empty or set it to post types that do not display the bar in your theme (e.g. a custom post type you do not use for content).

---

## Links

- **Repository:** [github.com/asolutioncompany/asc-core-tools](https://github.com/asolutioncompany/asc-core-tools)
- **License:** GPL v3 or later
