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