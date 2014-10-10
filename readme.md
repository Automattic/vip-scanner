# VIP Scanner [![Build Status](https://travis-ci.org/Automattic/vip-scanner.png?branch=master)](https://travis-ci.org/Automattic/vip-scanner)

A WordPress plugin that enables you to scan all sorts of themes and files and things.

Contributors: [Mohammad Jangda](http://profiles.wordpress.org/batmoo/), [Automattic](http://profiles.wordpress.org/automattic/), [Thorsten Ott](http://profiles.wordpress.org/tott/), [Michael Fields](http://profiles.wordpress.org/mfields/), [Filipe Varela](http://profiles.wordpress.org/keoshi/), [Josh Betz](http://profiles.wordpress.org/betzster/), [Mike Blouin](https://github.com/Mobius5150), [Nick Daugherty](http://profiles.wordpress.org/nickdaugherty/) and [Fränk Klein](https://profiles.wordpress.org/frank-klein/).

Requires WordPress version 3.4 or greater.


About
-----

The plugin itself is simply a UI for the VIP Scanner library, which does all the heavy lifting. The library allows you to create arbitrary "Checks" (e.g. UndefinedFunctionCheck), group them together as Reviews (WordPress.org Theme Review), and run them against themes, plugins, directories, single files, and even diffs.

This plugin is based on code from the [Theme Check](http://wordpress.org/extend/plugins/theme-check/) (written by [Pross](http://profiles.wordpress.org/pross/) and [Otto42](http://profiles.wordpress.org/otto42/)) and [Exploit Scanner](http://wordpress.org/extend/plugins/exploit-scanner/)  (written by [donncha](http://profiles.wordpress.org/donncha/)) plugins.


Installation
------------

1. Upload the plugin folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

or

Install using the Plugin Installer.

Usage
-----

You can find the tool under Tools > VIP Scanner. There, you can choose what
type of scan you'd like to perform -- there's a dropdown list on the right hand
side, just next to the "Scan" button.

The dropdown allows you to choose between three types of scan:

1. Undefined Function Check
1. WP.com Theme Review
1. VIP Theme Review

Once you have selected a scan type, you can hit the "Scan" button and see the
results in the tabbed view below.

WP-CLI
------

If you prefer to use the wp-cli tool for your check, there's a ``vip-scanner``
command with two main actions:

1. ``analyze-theme``
1. ``scan-theme``

```
$ wp vip-scanner
usage: wp vip-scanner analyze-theme [--theme=<theme>] [--scan_type=<scan-type>] [--depth=<depth>]
   or: wp vip-scanner scan-theme [--theme=<theme>] [--scan_type=<scan_type>] [--summary] [--format=<format>]
```

* ``--theme`` is the theme's path relative to the WP themes directory, for example, ``vip/test-theme`` or ``pub/twentyfourteen``. Defaults to the current theme.
* ``--scan_type`` expects one of the following options: ``"Undefined Function Check"``, ``"WP.com Theme Review"`` or ``"VIP Theme Review"``. Defaults to "VIP Theme Review".
* ``--depth`` expects an integer. You can change the parameter to indicate how many levels of hierarchy you would like outputted. 0 outputs everything. Defaults to 1.
* ``--summary`` gives you just an overview of how many files were checked, how many checks were done and how many errors, warnings and blockers were found.
* ``--format`` allows you to select a output format: ``table``, ``JSON``, ``CSV``. Defaults to ``table``.

Changelog
---------

__0.8__

* Modified check for `pre_option_*` to also include `option_*`

__0.7__

* Modified analyzer to use PHP tokens rather than regular expressions
* New checks, including white/blacklist checking for file types and names
* Added basic async scanning as an admin bar node
* WP CLI scan commands now support paths in addition to theme slugs
* WP CLI `scan_type` argument is now optional

__0.6__

* Analysis tab for analysing functions, classes, namespaces, shortcodes, actions, filters, capabilities, roles, CPTs, taxonomies, scripts, and styles.
* WP CLI command for analysis: `wp vip-scanner analyze-theme`
* New checks, including VCMergeConflictCheck, WordPressCodingStandardsCheck
* [PHP Code Sniffer](http://pear.php.net/package/PHP_CodeSniffer/) integration using the [WordPress Coding Standards](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards)
* Check improvements: VIPRestrictedCommandsCheck, VIPRestrictedPatternsCheck, PHPShortTagsCheck
* Added unit testing for some tests

__0.5__

* ClamAV Integration
* New checks, including VIPInitCheck, filter_input, WP_Widget_Tag_Cloud, and more!
* WP CLI Support (using vip-scanner command)
* Reducing false positives
* Adjusting severity of several checks

__0.4__

* UI Refresh
* Exports
* Auto scan

__0.3__

* Various bug fixes, including preventing the annoying upgrade nag between the main VIP Scanner plugin and WP.com Rules.

__0.2__

* New checks and scans! VIP_PregFile, EscapingCheck, etc.
* PHP 5.2 compatibility, props kevinmcgillivray and chrisguitarguy
* Bump WP version requirement (3.4)
* Code cleanup, props lance

__0.1__

* Initial version, using slightly older versions of the Theme Check plugin's checks.
