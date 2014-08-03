<?php
/**
 * Checks for the usage of forbidden variables.
 */

class ForbiddenVariablesCheck extends BaseCheck {

	function check( $files ) {

		$this->increment_check_count();
		$result = true;

		$checks = array(
			'_REQUEST',
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {

			foreach ( $checks as $check ) {
				
				/**
				 * Before a function, there's either a start of a line, whitespace, . or (
				 */
				if ( preg_match( '/(?<=\$)' . $check . '/m', $file_content, $matches ) ) {
					$forbidden_variable = trim( rtrim( $matches[0], '(' ) );

					$this->add_error(
						'forbidden-variable',
						'The variable with the name <code>$' . esc_html( $forbidden_variable ) . '</code> was found. Themes cannot use this variable.',
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
