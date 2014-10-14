=== VIP Scanner ===
Contributors: batmoo, automattic, tott, mfields, keoshi, betzster, mobius5150, nickdaugherty
Tags: scanner, scan, files, security, theme check
Requires at least: 3.4
Tested up to: 3.9
Stable tag: 0.7

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

= 0.7 =

* Modified analyzer to use PHP tokens rather than regular expressions
* New checks, including white/blacklist checking for file types and names
* Added basic async scanning as an admin bar node
* WP CLI scan commands now support paths in addition to theme slugs
* WP CLI `scan_type` argument is now optional

= 0.6 =

* Analysis tab for analysing functions, classes, namespaces, shortcodes, actions, filters, capabilities, roles, CPTs, taxonomies, scripts, and styles.
* WP CLI command for analysis: wp vip-scanner analyze-theme
* New checks, including VCMergeConflictCheck, WordPressCodingStandardsCheck
* PHP Code Sniffer integration using the WordPress Coding Standards
* Check improvements: VIPRestrictedCommandsCheck, VIPRestrictedPatternsCheck, PHPShortTagsCheck
* Added unit testing for some tests

= 0.5 =
* ClamAV Integration
* New checks, including VIPInitCheck, filter_input, WP_Widget_Tag_Cloud, and more!
* WP CLI Support (using vip-scanner command)
* Reducing false positives
* Adjusting severity of several checks

= 0.4 =
* UI Refresh
* Exports
* Auto scan

= 0.3 = 

* Various bug fixes, including preventing the annoying upgrade nag between the main VIP Scanner plugin and WP.com Rules.

= 0.2 =

* New checks and scans! VIP_PregFile, EscapingCheck, etc.
* PHP 5.2 compatibility, props kevinmcgillivray and chrisguitarguy
* Bump WP version requirement (3.4)
* Code cleanup, props lance

= 0.1 =

* Initial version, using slightly older versions of the Theme Check plugin's checks.
