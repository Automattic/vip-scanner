=== VIP Scanner ===
Contributors: batmoo, automattic, tott, mfields, keoshi, betzster, mobius5150, nickdaugherty
Tags: scanner, scan, files, security, theme check
Requires at least: 3.4
Tested up to: 4.0
Stable tag: 0.7

Scan all sorts of themes and files and things.

== Description ==

Scan all sorts of themes and files and things.

The plugin itself is simple a UI for the VIP Scanner library, which does all the heavy lifting. The library allows you to create arbitrary "Checks" (e.g. UndefinedFunctionCheck), group them together as Reviews (e.g. WP.com Theme Review), and run them against themes, plugins, directories, single files, and even diffs.

This plugin is based on code from the <a href="http://wordpress.org/extend/plugins/theme-check/">Theme Check</a> (written by Pross and Otto42) and <a href="http://wordpress.org/extend/plugins/exploit-scanner/">Exploit Scanner</a> (written by donncha) plugins.

== Requirements ==

VIP Scanner requires PHP >= 5.4.

For parsing PHP files, VIP Scanner uses [PHP-Parser](https://github.com/nikic/PHP-Parser),
which it includes as a git submodule. When cloning VIP Scanner's git repo, use
the `--recursive` parameter to include PHP-Parser, i.e.
`git clone --recursive git@github.com:Automattic/vip-scanner.git`
If you have already cloned the repo without the `--recursive` parameter and
find yourself with an empty `vendor/PHP-Parser` directory, run
`git submodule update --init --recursive`.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

or

Install using the Plugin Installer.

== Usage ==

You can find the tool under Tools > VIP Scanner. There, you can choose what
type of scan you'd like to perform -- there's a dropdown list on the right hand
side, just next to the "Scan" button.

The dropdown allows you to choose between three types of scan:

1. Undefined Function Check
1. WP.com Theme Review
1. VIP Theme Review

Once you have selected a scan type, you can hit the "Scan" button and see the
results in the tabbed view below.

== WP-CLI ==

If you prefer to use the wp-cli tool for your check, there's a ``vip-scanner``
command with two main actions:

1. `analyze-theme`
1. `scan-theme`

`
$ wp vip-scanner
usage: wp vip-scanner analyze-theme [--theme=<theme>] [--scan_type=<scan-type>] [--depth=<depth>]
   or: wp vip-scanner scan-theme [--theme=<theme>] [--scan_type=<scan_type>] [--summary] [--format=<format>]
`

* `--theme` is the theme's path relative to the WP themes directory, for example, `vip/test-theme` or `pub/twentyfourteen`. Defaults to the current theme.
* `--scan_type` expects one of the following options: `"Undefined Function Check"`, `"WP.com Theme Review"` or `"VIP Theme Review"`. Defaults to "VIP Theme Review".
* `--depth` expects an integer. You can change the parameter to indicate how many levels of hierarchy you would like outputted. 0 outputs everything. Defaults to 1.
* `--summary` gives you just an overview of how many files were checked, how many checks were done and how many errors, warnings and blockers were found.
* `--format` allows you to select a output format: `table`, `JSON`, `CSV`. Defaults to `table`.

== Frequently Asked Questions ==

To come...

== Screenshots ==

1. The VIP Scanner has a slick UI. Slicker than your average.

== Changelog ==

= 0.8 =

* Modified check for pre_option_* to also include option_*

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
