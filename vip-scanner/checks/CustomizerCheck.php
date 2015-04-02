<?php
/**
 * Checks for customization options in the theme:
 * Is there an image uploader?
 * Are there custom controls?
 * Does every setting have a sanitization callback?
 */

class CustomizerCheck extends CodeCheck {
	function __construct() {
		parent::__construct( array(
			/**
			 * Does the theme create a WP_Customize_Image_Control?
			 */
			'PhpParser\Node\Expr\New_' => function( $node ) {
				$class_name = $node->class->toString();
				if ( 'WP_Customize_Image_Control' === $class_name ) {
					$this->add_error(
						'customizer',
						'The theme uses the <code>WP_Customize_Image_Control</code> class. Custom logo options should be implemented using the <a href="http://en.support.wordpress.com/site-logo/">Site Logo</a> feature.',
						BaseScanner::LEVEL_WARNING
					);
				}
			},
			/**
			 * Does the theme create a new Customizer Control?
			 */
			'PhpParser\Node\Stmt\Class_' => function( $node ) {
				if ( isset( $node->extends ) && 'WP_Customize_Control' === $node->extends->toString() ) {
					$this->add_error(
						'customizer',
						'The theme creates a new Customizer control by extending <code>WP_Customize_Control</code>.',
						BaseScanner::LEVEL_WARNING
					);
				}
			},
			/**
			 * Check whether every Customizer setting has a sanitization callback set.
			 */
			'PhpParser\Node\Expr\MethodCall' => function( $node ) {
				if ( 'wp_customize' !== $node->var->name || 'add_setting' !== $node->name || count( $node->args ) < 2 ) {
					return;
				}

				// Get the second argument passed to the add_setting method
				$args = $node->args[1]->value;
				$found_sanitize_callback = false;
				if ( ! $args instanceof PhpParser\Node\Expr\Array_ ) {
					return;
				}

				foreach( $args->items as $arg ) {
					if ( ! $arg->key instanceof PhpParser\Node\Scalar\String ) {
						continue;
					}

					$key = $arg->key->value;
					// Check if we have sanitize_callback or sanitize_js_callback
					if ( 'sanitize_callback' !== $key && 'sanitize_js_callback' !== $key ) {
						continue;
					}

					$found_sanitize_callback = true;
					// There's a callback, check that no empty parameter is passed.
					if ( ! $arg->value instanceof PhpParser\Node\Scalar\String ) {
						continue;
					}

					$value = trim( $arg->value->value );
					if ( empty( $value ) ) {
						$this->add_error(
							'customizer',
							'Found a Customizer setting that had an empty value passed as sanitization callback. You need to pass a function name as sanitization callback.',
							BaseScanner::LEVEL_BLOCKER
						);
					}
				}
				if ( ! $found_sanitize_callback ) {
					$this->add_error(
							'customizer',
							'Found a Customizer setting that did not have a sanitization callback function. Every call to the <code>add_setting()</code> method needs to have a sanitization callback function passed.',
							BaseScanner::LEVEL_BLOCKER
					);
				}
			},
		) );
	}
}
