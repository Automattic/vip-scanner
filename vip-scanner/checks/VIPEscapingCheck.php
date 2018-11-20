<?php

/**
 * Detect errors in escaping.
 */
class VIPEscapingCheck extends BaseCheck {
	function check( $files ) {
		$checks = array(
			array(
				/*
				 * Catch printing usage of __( ), etc.
				 * These are blockers because the results
				 * are being printed without HTML-escaping the
				 * results.
				 */
				'pattern' => '/([\s]|[;]){1,}(echo|vprintf|print)([\s]|[\(])+(__|_x|_n|_nx)[\s]*?\(/',
				'level'   => 'blocker',
				'message' => sprintf( 
						esc_html__( 'Printing output of non-escaping localization functions (i.e. %1$s) is potentially dangerous, as they do not escape HTML. An escaping function (e.g. %2$s) should be used rather.', 'vip-scanner' ), 
						'<code>__( ), _x( ), _n( ), _nx( )</code>', 
						'<code>esc_html__( ), esc_attr__( ), esc_html_x( ), esc_attr_x( )</code>' 
				),
			),
			array(
				/*
				 * Catch non-printing usage of __( ), etc
				 * These are warnings, because the results
				 * are not being immediately printed, and so
				 * could be escaped at a later point.
				 */
				'pattern' => '/([\s]|[;]|[=]){1,}[a-zA-Z]{0}[\s]+(__|_x|_n|_nx)[\s]*?\(/',
				'level'   => 'warning',
				'message' => sprintf( 
						esc_html__( 'Usage of non-escaping localization functions (i.e. %1$s) is discouraged as they do not escape HTML. An escaping function (e.g. %2$s) should be used rather.', 'vip-scanner' ), 
						'<code>__( ), _x( ), _n( ), _nx( )</code>', 
						'<code>esc_html__( ), esc_attr__( ), esc_html_x( ), esc_attr_x( )</code>' 
				),
			),
			array(
				/*
				 * Catch calls to _e( ), _ex( )
				 * These print directly, without HTML-escaping,
				 * and so are blockers.
				 */
				'pattern' => '/([\s]|[;]){1,}[a-zA-Z]{0}[\s]+(_e|_ex)[\s]*?\(/',
				'level'   => 'blocker',
				'message' => sprintf( 
						esc_html__( 'Usage of non-escaping localization functions (i.e. %1$s) is discouraged as they do not escape HTML. An escaping function (e.g. %2$s) should be used rather.', 'vip-scanner' ), 
						'<code>_e( ), _ex( )</code>', 
						'<code>esc_html_e( ), esc_attr_e( ), esc_html_x( ), esc_attr_x( ), esc_attr__( )</code>' 
				),
			),
		);

		$result = true;
		foreach ( $checks as $check ) {
			$this->increment_check_count();
			foreach ( $this->filter_files( $files, 'php' ) as $path => $code ) {
				$filename = $this->get_filename( $path );
				$errors = $this->preg_file2( $check['pattern'], $code );
				foreach ( (array) $errors as $line_number => $error ) {
					$this->add_error(
						'functions-file',
						$check['message'],
						$check['level'],
						$filename,
						array( $line_number => $error )
					);
					$result = false;
				}
			}
		}
		return $result;
	}
}
