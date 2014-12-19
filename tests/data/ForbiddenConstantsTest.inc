<?php

die(); //Don't actually run the following code.

$plugindir = PLUGINDIR;
$wp_plugin_dir=WP_PLUGIN_DIR;
$wpmu_plugin_dir= WPMU_PLUGIN_DIR;
$muplugindir= MUPLUGINDIR . '/';
echo IS_WPCOM;

// Don't throw an error on the following
/* Let's not use PLUGINDIR */
$my_plugindir = MYPLUGINDIR;