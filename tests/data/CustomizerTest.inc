<?php

die(); //Don't actually run the following code.

// No error
$wp_customize->add_setting( 'setting', array(
		'sanitize_callback' => 'sanitize_text_field',
) );

// No error
$wp_customize->add_setting( 'setting', array(
		'default'           => array( 'id' => 0, 'url' => '' ),
		'sanitize_callback' => 'sanitize_text_field',
) );


// No error
$wp_customize->add_setting( 'setting', array(
	'sanitize_callback' => array( $this, 'sanitize_method' ),
) );

// Error: 'sanitize_callback' argument with empty value
$wp_customize->add_setting( 'setting', array(
	'sanitize_callback' => '',
) );

// Error: 'sanitize_callback' argument with empty value
$wp_customize->add_setting( 'setting', array(
	'sanitize_callback' => '   ',
) );

// Error: No 'sanitize_callback' argument
$wp_customize->add_setting( 'setting', array(
	'default'   => '',
) );

// Warning: Using WP_Customize_Image_Control class
$control = new WP_Customize_Image_Control;

// Warning: Class extends WP_Customize_Control
class SubclassedControl extends WP_Customize_Control {}

// No error: Verify that semicolons in text don't cause issues. See #250.
$wp_customize->add_setting( 'showcase_title_projects', array(
	'default' => __( 'Recent Projects &rsaquo;', 'showcase' ),
	'capability' => 'edit_theme_options',
	'sanitize_callback' => 'esc_html',
) );