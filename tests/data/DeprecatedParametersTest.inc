<?php

die(); //Don't actually run the following code.

$home                 = get_bloginfo( 'home' );
$url                  = get_bloginfo( 'url' );
$wpurl                = get_bloginfo( 'wpurl' );
$stylesheet_directory = get_bloginfo( 'stylesheet_directory' );
$template_directory   = get_bloginfo( 'template_directory' );
$template_url         = get_bloginfo( 'template_url' );
$text_direction       = get_bloginfo( 'text_direction' );
$feed_url             = get_bloginfo( 'feed_url' );

echo bloginfo( 'home' );
echo bloginfo( 'url' );
echo bloginfo( 'wpurl' );
echo bloginfo( 'stylesheet_directory' );
echo bloginfo( 'template_directory' );
echo bloginfo( 'template_url' );
echo bloginfo( 'text_direction' );
echo bloginfo( 'feed_url' );

$home     = get_option( 'home' );
$site_url = get_option( 'site_url' );

