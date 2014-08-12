<?php
/**
 * Checks for customization options in the theme:
 * Is there an image uploader?
 * Are there custom controls?
 * Does every setting have a sanitization callback?
 */

class CustomizerCheck extends BaseCheck {
	function check( $files ) {
		$result = true;
		$php_files = $this->filter_files( $files, 'php' );

		/**
		 * Does the theme create a WP_Customize_Image_Control?
		 */
		$this->increment_check_count();

		foreach ( $php_files as $file_path => $file_content ) {
			if ( preg_match_all( '/\s*WP_Customize_Image_Control\s*/', $file_content, $matches ) ) {
				$errors = $matches[0];

				foreach ( $errors as $error ) {
					$this->add_error(
						'customizer',
						'The theme uses the <code>WP_Customize_Image_Control</code> class. Custom logo options should be implemented using the <a href="http://en.support.wordpress.com/site-logo/">Site Logo</a> feature.',
						BaseScanner::LEVEL_WARNING,
						$this->get_filename( $file_path )
					);
					$result = false;
				}
			}
		}

		/**
		 * Does the theme create a new Customizer Control?
		 */
		$this->increment_check_count();

		foreach ( $php_files as $file_path => $file_content ) {
			if ( preg_match_all( '/extends\s+WP_Customize_Control\s{/', $file_content, $matches ) ) {
				$errors = $matches[0];

				foreach ( $errors as $error ) {
					$this->add_error(
						'customizer',
						'The theme creates a new Customizer control by extending <code>WP_Customize_Control</code>.',
						BaseScanner::LEVEL_WARNING,
						$this->get_filename( $file_path )
					);
					$result = false;
				}
			}
		}

		/**
		* Check whether every Customizer setting has a sanitization callback set.
		*/
		$this->increment_check_count();

		foreach ( $php_files as $file_path => $file_content ) {
			// Get the arguments passed to the add_setting method
			if ( preg_match_all( '/\$wp_customize->add_setting\(([^;]+)/', $file_content, $matches ) ) {
				// The full match is in [0], the match group in [1]
				foreach ( $matches[1] as $match ) {
					// Check if we have sanitize_callback or sanitize_js_callback
					if ( false === strpos( $match, 'sanitize_callback' ) && false === strpos( $match, 'sanitize_js_callback' ) ) {
						$this->add_error(
							'customizer',
							'Found a Customizer setting that did not have a sanitization callback function. Every call to the <code>add_setting()</code> method needs to have a sanitization callback function passed.',
							BaseScanner::LEVEL_BLOCKER,
							$this->get_filename( $file_path )
						);
						$result = false;
					} else {
						// There's a callback, check that no empty parameter is passed.
						if ( preg_match( '/[\'"](?:sanitize_callback|sanitize_js_callback)[\'"]\s*=>\s*[\'"]\s*[\'"]/', $match ) ) {
							$this->add_error(
								'customizer',
								'Found a Customizer setting that had an empty value passed as sanitization callback. You need to pass a function name as sanitization callback.',
								BaseScanner::LEVEL_BLOCKER,
								$this->get_filename( $file_path )
							);
							$result = false;
						}
					}
				}
			}
		}

		return $result;
	}
}
