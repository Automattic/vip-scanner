<?php
/**
 * Checks for the usage of forbidden PHP functions.
 */

class ForbiddenPHPFunctionsCheck extends BaseCheck {

	function check( $files ) {

		$this->increment_check_count();
		$result = true;

		$checks = array(
			'eval',
			'popen',
			'proc_open',
			'exec',
			'shell_exec',
			'system',
			'passthru',
			'base64_decode',
			'base64_encode',
			'uudecode',
			'str_rot13',
			'ini_set',
			'create_function',
			'extract',
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {

			foreach ( $checks as $check ) {
				
				/**
				 * Before a function, there's either a start of a line, whitespace, . or (
				 */
				if ( preg_match( '/(?:^|[\s\.\(])' . $check . '\(/m', $file_content, $matches ) ) {
					$forbidden_function = trim( rtrim( $matches[0], '(' ) );

					$this->add_error(
						'deprecated',
						'The PHP function <code>' . esc_html( $forbidden_function ) . '()</code> was found. Themes cannot use this function.',
						BaseScanner::LEVEL_BLOCKER,
						$this->get_filename( $file_path )
					);
					$result = false;
				}
			}

		}

		return $result;
	}
}
