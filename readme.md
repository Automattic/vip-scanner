VIP Scanner
===========

A WordPress plugin that enables you to scan all sorts of themes and files and things.

Contributors: [Mohammad Jangda](http://profiles.wordpress.org/batmoo/), [Automattic](http://profiles.wordpress.org/automattic/), [Thorsten Ott](http://profiles.wordpress.org/tott/), and [Michael Fields](http://profiles.wordpress.org/mfields/)

Requires WordPress version 3.4 or greater.


About
-----

The plugin itself is simple a UI for the VIP Scanner library, which does all the heavy lifting. The library allows you to create arbitrary "Checks" (e.g. UndefinedFunctionCheck), group them together as Reviews (WordPress.org Theme Review), and run them against themes, plugins, directories, single files, and even diffs.

This plugin is based on code from the [Theme Check](http://wordpress.org/extend/plugins/theme-check/) (written by [Pross](http://profiles.wordpress.org/pross/) and [Otto42](http://profiles.wordpress.org/otto42/)) and [Exploit Scanner](http://wordpress.org/extend/plugins/exploit-scanner/)  (written by [donncha](http://profiles.wordpress.org/donncha/)) plugins.


Installation
------------

1. Upload the plugin folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Tools > VIP Scanner

or

Install using the Plugin Installer.


Changelog
---------

__0.2__

* New checks and scans! VIP_PregFile, EscapingCheck, etc.
* PHP 5.2 compatibility, props kevinmcgillivray and chrisguitarguy
* Bump WP version requirement (3.4)
* Code cleanup, props lance

__0.1__

* Initial version, using slightly older versions of the Theme Check plugin's checks.
