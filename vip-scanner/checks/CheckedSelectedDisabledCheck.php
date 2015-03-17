<?php
/**
 * Checks for missing uses of the checked(), selected() and disabled() helper functions.
 */

class CheckedSelectedDisabledCheck extends BaseCheck {

	function check( $files ) {

		$this->increment_check_count();
		$result = true;

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			/**
			 * Look for missing uses checked().
			 */
			if ( preg_match( '/checked=["\']checked["\']/', $file_content, $matches ) ) {
				$result = false;
				$this->add_error(
					'invalid-checked',
					sprintf( esc_html__( 'Please use the %1$s function to output the %2$s attribute in form elements.', 'vip-scanner' ),
						'<code>checked()</code>',
						'<code>checked</code>'
					),
					BaseScanner::LEVEL_WARNING,
					basename( $file_path ),
					$this->grep_content( rtrim( $matches[0], '(' ), $file_content )
				);
			}

			/**
			 * Look for missing uses selected().
			 */
			if ( preg_match( '/selected=["\']selected["\']/', $file_content, $matches ) ) {
				$result = false;
				$this->add_error(
					'invalid-selected',
					sprintf( esc_html__( 'Please use the %1$s function to output the %2$s attribute in form elements.', 'vip-scanner' ),
						'<code>selected()</code>',
						'<code>selected</code>'
					),
					BaseScanner::LEVEL_WARNING,
					basename( $file_path ),
					$this->grep_content( rtrim( $matches[0], '(' ), $file_content )
				);
			}

			/**
			 * Look for missing uses disabled().
			 */
			if ( preg_match( '/disabled=["\']disabled["\']/', $file_content, $matches ) ) {
				$result = false;
				$this->add_error(
					'invalid-disabled',
					sprintf( esc_html__( 'Please use the %1$s function to output the %2$s attribute in form elements.', 'vip-scanner' ),
						'<code>disabled()</code>',
						'<code>disabled</code>'
					),
					BaseScanner::LEVEL_WARNING,
					basename( $file_path ),
					$this->grep_content( rtrim( $matches[0], '(' ), $file_content )
				);
			}

		}

		return $result;
	}
}