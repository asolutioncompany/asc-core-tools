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
- **Disable auto-update emails** – Stop WordPress from sending email notifications after automatic core, plugin, or theme updates.
- **Hide WordPress Login** – Use a custom URL for login; `wp-login.php` is gated by a required parameter so only the custom slug works. Unauthenticated access to `wp-admin` redirects to the home page; REST API is restricted when enabled.
- **Autosave (Heartbeat)** – Disable autosave or set the autosave interval (15–120 seconds).
- **Revisions** – Disable revisions or limit how many are kept per post.
- **Comments** – Disable comments and pings site-wide; removes the Comments menu when there are no comments.

### Features
- **Shortcodes** – `[asc_core_tools_year]` (current year); `[asc_core_tools_social_sharing]` when social sharing is enabled.
- **Ninja Forms** – Optional customization (enable to load custom public CSS).
- **Social sharing** – Optional sharing bar (Facebook, Twitter, LinkedIn, Email, Copy link) on selected post types or via shortcode; requires clipboard.js and Font Awesome, which are included in the vendor directory and self-hosted by default.
- **Self-host Font Awesome** – Option to self-host Font Awesome from the plugin; on by default.

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

## Links

- **Repository:** [github.com/asolutioncompany/asc-core-tools](https://github.com/asolutioncompany/asc-core-tools)
- **License:** GPL v3 or later
