=== MH Dev Kit ===
Contributors: mh
Requires at least: 6.4
Tested up to: 6.8
Requires PHP: 8.1
Stable tag: 0.7.0
License: GPLv2 or later

Monolithic WordPress development plugin with modules (sub-plugins) and MVP features.

== Description ==

MH Dev Kit provides a modular architecture for custom WordPress features:

* Composer PSR-4 autoloading (no extra dependencies)
* **Modules** group related features; enable/disable under MHDK Settings → Modules
* **Core** module: MHDK-POC reference (settings, CPTs, Gutenberg blocks)
* **Site Tools** module: backend origin guard, map distance calculator
* **Commerce** module: WooCommerce Order Menu and saved menus
* **User Tools** module: QR codes, vCards, shortlinks, family tree
* **Content Tools** module: children pages, Markdown block, local ad pages
* MVP feature folders: Models, Presenters, Views, Assets

== Installation ==

1. Ensure Composer dependencies are installed: `ddev exec "cd wp-content/plugins/mh-dev-kit && composer install"`
2. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 0.7.0 =
* Site Tools module: Backend Restrict Origin, Map Distance shortcode
* User Tools module: QR codes, vCards, shortlinks, family tree builder
* Content Tools module: Children Pages, Markdown block, Local Ad Page CPT + REST API
* Core module retains MHDK-POC reference only; v0.7.0 features recategorized into dedicated modules

= 0.6.0 =
* Module layer: Core and Commerce modules; single MHDK Settings top-level menu
* Modules enable/disable admin page; features no longer register admin directly

= 0.2.0 =
* Composer + MVP architecture
* MHDK-POC feature: 3 blocks, 2 CPTs, MHDK Settings & MHDK Content admin menus

= 0.1.0 =
* Initial scaffold
