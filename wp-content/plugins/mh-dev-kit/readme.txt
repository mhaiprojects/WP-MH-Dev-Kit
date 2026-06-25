=== MH Dev Kit ===
Contributors: mh
Requires at least: 6.4
Tested up to: 6.8
Requires PHP: 8.1
Stable tag: 0.2.0
License: GPLv2 or later

Monolithic WordPress development plugin with Composer autoloading and MVP feature modules.

== Description ==

MH Dev Kit provides a modular architecture for custom WordPress features:

* Composer PSR-4 autoloading (no extra dependencies)
* MVP feature folders: Models, Presenters, Views, Assets
* MHDK-POC: admin settings, custom post types, Gutenberg blocks

== Installation ==

1. Ensure Composer dependencies are installed: `ddev exec "cd wp-content/plugins/mh-dev-kit && composer install"`
2. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 0.2.0 =
* Composer + MVP architecture
* MHDK-POC feature: 3 blocks, 2 CPTs, MHDK Settings & MHDK Content admin menus

= 0.1.0 =
* Initial scaffold
