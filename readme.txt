=== VIP Scanner ===
Contributors: batmoo, automattic, tott, mfields
Tags: scanner, scan, files, security, theme check
Requires at least: 3.4
Tested up to: 3.4
Stable tag: 0.3

Scan all sorts of themes and files and things.

== Description ==

Scan all sorts of themes and files and things.

The plugin itself is simple a UI for the VIP Scanner library, which does all the heavy lifting. The library allows you to create arbitrary "Checks" (e.g. UndefinedFunctionCheck), group them together as Reviews (WordPress.org Theme Review), and run them against themes, plugins, directories, single files, and even diffs.

This plugin is based on code from the <a href="http://wordpress.org/extend/plugins/theme-check/">Theme Check</a> (written by Pross and Otto42) and <a href="http://wordpress.org/extend/plugins/exploit-scanner/">Exploit Scanner</a> (written by donncha) plugins.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Tools > VIP Scanner

or

Install using the Plugin Installer.

== Frequently Asked Questions ==

To come...

== Screenshots ==

1. The VIP Scanner has a slick UI. Slicker than your average.

== Changelog ==

= 0.3 = 

* Varios bug fixes, including preventing the annoying upgrade nag between the main VIP Scanner plugin and WP.com Rules.

= 0.2 =

* New checks and scans! VIP_PregFile, EscapingCheck, etc.
* PHP 5.2 compatibility, props kevinmcgillivray and chrisguitarguy
* Bump WP version requirement (3.4)
* Code cleanup, props lance

= 0.1 =

* Initial version, using slightly older versions of the Theme Check plugin's checks.
