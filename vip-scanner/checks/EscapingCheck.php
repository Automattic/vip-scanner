<?php

/**
 * Detect errors in escaping.
 */
class EscapingCheck extends BaseCheck {
	function check( $files ) {
		$checks = array(
			array(
				'pattern' => '/esc_attr[\s]*?\([\s]*?printf[\s]*?\(/',
				'message' => sprintf( __( 'The function %1$s is being passed as the first parameter of %2$s. This is problematic because %1$s echoes a string which will not be escaped by %2$s.', 'vip-scanner' ),
					'<code>printf()</code>',
					'<code>esc_attr()</code>'
				),
			),
			array(
				'pattern' => '/esc_attr[\s]*?\([\s]+print[\s]+/',
				'message' => sprintf( __( '%1$s is being passed as the first parameter of %2$s.', 'vip-scanner' ),
					'<code>print</code>',
					'<code>esc_attr()</code>'
				),
			),
			array(
				'pattern' => '/esc_attr[\s]*?\([\s]+echo[\s]+/',
				'message' => sprintf( __( '%1$s is being passed as the first parameter of %2$s.', 'vip-scanner' ),
					'<code>echo</code>',
					'<code>esc_attr()</code>'
				),
			),
			array(
				'pattern' => '/="[\s]*?<\?php[\s]+_e/',
				'message' => sprintf( __( 'Please use %1$s to echo internationalized text in html attributes.', 'vip-scanner' ),
					'<code>esc_attr_e()</code>'
				),
			),
			array(
				'pattern' => "/=[\s]*?\'<\?php[\s]+_e/",
				'message' => sprintf( __( 'Please use %1$s to echo internationalized text in html attributes.', 'vip-scanner' ),
					'<code>esc_attr_e()</code>'
				),
			),
		);

		foreach ( $checks as $check ) {
			$this->increment_check_count();
			foreach ( $this->filter_files( $files, 'php' ) as $path => $code ) {
				$filename = $this->get_filename( $path );
				$errors = $this->preg_file2( $check['pattern'], $code );
				foreach ( (array) $errors as $line_number => $error ) {
					$this->add_error(
						'functions-file',
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