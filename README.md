# Admin Panel Fonts

**Change WordPress admin panel fonts easily** – a lightweight plugin that adds a font switcher to the admin toolbar, letting you personalise the WordPress dashboard with custom web fonts instantly, without reloading the page.

![Plugin version](https://img.shields.io/badge/version-1.2.0-blue.svg)
![WordPress version](https://img.shields.io/badge/requires_at_least-6.0-green.svg)
![PHP version](https://img.shields.io/badge/PHP-8.1%2B-8892BF.svg)
![License](https://img.shields.io/badge/license-GPLv2-orange.svg)

## Table of Contents

- [Description](#description)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Adding Your Own Fonts](#adding-your-own-fonts)
- [Frequently Asked Questions](#frequently-asked-questions)
- [Translations](#translations)
- [Changelog](#changelog)
- [Credits](#credits)
- [License](#license)

## Description

**Admin Panel Fonts** replaces the default WordPress admin typeface with your choice of font, applied globally across the whole administration area (including the toolbar, editors, modal windows, and RTL layouts). The plugin comes with a built‑in font selector in the top admin bar – simply pick a font and it is applied immediately via AJAX, with no page refresh.

The plugin is developer‑friendly: any `.woff` or `.woff2` file placed in the `assets/fonts/` directory automatically appears in the dropdown.

## Features

- 🔤 **Instant font switching** – change the admin font on the fly with AJAX (no reload).
- 🧩 **Admin bar integration** – the font selector lives right in the WordPress toolbar.
- 🌐 **RTL ready** – full support for right‑to‑left languages (e.g. Persian, Arabic).
- 🎨 **Custom font support** – add your own `.woff`/`.woff2` files, no coding required.
- ⚡ **Performance first** – font list is cached for 24 hours; `font-display: swap` prevents layout shifts.
- 🔒 **Secure** – nonce‑protected AJAX requests and proper sanitisation.
- 🌍 **Internationalised** – comes with English and Persian (fa_IR) translations; easily translatable.
- 🧹 **Clean uninstall** – removes all options and transients when deleted.

## Requirements

- WordPress 6.0 or higher
- PHP 8.1 or higher
- Modern browser (supports WOFF/WOFF2)

## Installation

1. Download the plugin zip file or clone the repository.
2. Upload the `admin-panel-fonts` folder to `/wp-content/plugins/`.
3. Activate the plugin through the **Plugins** menu in WordPress.
4. Go to any admin page – you will see a new **Admin Font** item in the top admin bar.

> No further configuration is required. The plugin works out of the box.

## Usage

1. Log in to your WordPress admin panel.
2. In the admin toolbar (usually at the very top), locate the **Admin Font** menu – it shows a small appearance icon and the text “Admin Font”.
3. Click on it to reveal the font selector dropdown.
4. Choose a different font from the list.
5. The font changes immediately across the whole admin area. A toast notification confirms the change.

The selected font is saved to your WordPress options and will be remembered even after logging out.

## Adding Your Own Fonts

You can extend the plugin with any custom font as long as it is provided in **WOFF** or **WOFF2** format.

### Steps

1. Access your server via FTP or file manager.
2. Navigate to `/wp-content/plugins/admin-panel-fonts/assets/fonts/`.
3. Upload your font files there.  
   Supported extensions: `.woff`, `.woff2`.
4. The plugin automatically detects new fonts (list is cached for 24 hours – you can wait or delete the transient `wp-admin-fonts-list` to force a refresh).
5. Refresh any admin page – your font will appear in the dropdown, using the file name (without extension) as the display name (underscores and hyphens are replaced by spaces).

> **Important:** The font file name must be URL‑safe (only letters, numbers, hyphens, underscores). Example: `my-custom-font.woff2` → “My Custom Font” in the dropdown.

## Frequently Asked Questions

### Can I use other font formats like TTF or OTF?

No – only WOFF and WOFF2 are supported for performance and browser compatibility. Convert your fonts using free online tools if needed.

### Does the plugin work with page builders (e.g., Elementor, Gutenberg)?

It applies styles to **all admin elements**, including Gutenberg blocks, media modal windows, and the toolbar. Third‑party plugin styles that explicitly override the font may not change, but the base admin area will.

### How do I reset to the default WordPress font?

Simply select another font, or uninstall the plugin. The original admin font is restored automatically.

### The font list does not show my newly added font – why?

The plugin caches the font list for 24 hours. To see new fonts immediately, delete the transient `wp-admin-fonts-list` from your database (e.g., using a plugin like Transient Manager) or wait one day. Alternatively, deactivate and reactivate the plugin – this will flush the cache as well.

### Is it compatible with multisite?

Yes – each site in the network can have its own font selection. Network admins can also choose to network‑activate the plugin.

### Does it affect front‑end performance?

No – the plugin only enqueues assets and applies CSS in the admin area (`admin_enqueue_scripts` hook). There is no front‑end impact.

## Translations

The plugin is translation‑ready and currently includes:

- **English (en_US)** – default
- **Persian (fa_IR)** – fully translated

To translate the plugin into your own language:

1. Copy `/languages/admin-panel-fonts.pot` to `admin-panel-fonts-{locale}.po` (e.g., `admin-panel-fonts-de_DE.po` for German).
2. Translate the strings with [Poedit](https://poedit.net/) or a text editor.
3. Compile the `.mo` file and place it in the `/languages/` directory.
4. Reload the admin panel – the strings will appear in your language.

## Changelog

### 1.2.0 (2026-05-02)
- Added AJAX font switching – no reload required.
- Introduced toast notifications for success/error messages.
- Improved RTL selector coverage.
- Added translation files for Persian (fa_IR).
- Cached font list for better performance.

### 1.1.0
- Initial public release with basic font dropdown and inline CSS.

## Credits

- **Author:** [Ahmadreza Ebrahimi](https://ahmadreza.me/)
- **Plugin URI:** [https://ahmadreza.me/plugins/wp-admin-fonts](https://ahmadreza.me/plugins/wp-admin-fonts)
- Built for the WordPress community.

## License

This plugin is licensed under the [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html).

---

**Enjoy a personalised admin experience!**  
If you like this plugin, consider leaving a [review](https://wordpress.org/support/plugin/admin-panel-fonts/reviews/) or sharing it with others.