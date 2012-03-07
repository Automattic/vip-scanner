<?php

/**
 * Detect errors in internationalization.
 */
class InternationalizedStringCheck extends BaseCheck {
	function check( $files ) {

		/**
		 * Prefix for all regex patterns.
		 * Matches the following gettext functions:
		 * __()
		 * _e()
		 * _x()
		 * esc_attr__()
		 * esc_attr_e()
		 * esc_attr_x()
		 * esc_html__()
		 * esc_html_e()
		 * esc_html_x()
		 */
		$prefix = '(_(_|e|x)|esc_attr_(_|e|x)|esc_html_(_|e|x))';

		$checks = array(
			array(
				'pattern' => '/' . $prefix . '[\s]*\([\s]*\)/',
				'message' => __( 'No parameters we passed to a gettext function.', 'vip-scanner' ),
			),
			array(
				'pattern' => '/' . $prefix . "[\s]*\([\s]*'[\s]*'[\s]*\)/",
				'message' => __( 'An empty string has been passed to a gettext function.', 'vip-scanner' ),
			),
			array(
				'pattern' => '/' . $prefix . '[\s]*\([\s]*"[\s]*"[\s]*\)/',
				'message' => __( 'An empty string has been passed to a gettext function.', 'vip-scanner' ),
			),
			array(
				'pattern' => '/' . $prefix . '[\s]*\([\s]*\$[\w]+/',
				'message' => __( 'A variable has been passed as the first parameter of a gettext function.', 'vip-scanner' ),
			),
			array(
				'pattern' => '/' . $prefix . '[\s]*\([\s][a-z_][\w]+/',
				'message' => __( 'The return value of a function may have been passed as the first parameter to a get text function.', 'vip-scanner' ),
			),
		);

		foreach ( $checks as $check ) {
			$this->increment_check_count();
			foreach ( $this->filter_files( $files, 'php' ) as $path => $code ) {
				$filename = $this->get_filename( $path );
				$errors = $this->preg_file2( $check['pattern'], $code );
				foreach ( (array) $errors as $line_number => $error ) {
					$this->add_error(
						'i18n',
						$check['message'],
						'blocker',
						array( $filename, $line_number ),
						esc_html( $error )
					);
				}
			}
		}
	}
}