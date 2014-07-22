<?php
/**
 * Checks for a forbidden whitespace:
 *
 * Whitespace before the opening <?php tag.
 * Whitespace after the closing ?> tag.
 */

class WhiteSpaceCheck extends BaseCheck {

	function check( $files ) {

		$result = true;

		/**
		 * Before <?php
		 */
		$this->increment_check_count();

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			if ( preg_match( '/^[\s\n]+\<\?php/', $file_content, $matches ) ) {
				$this->add_error(
					'whitespace',
					'There is whitespace before the opening <code>&lt;?php</code> tag.',
					Basescanner::LEVEL_BLOCKER,
					basename( $file_path )
				);
				$result = false;
			}
		}

		/**
		 * After ?>
		 */
		$this->increment_check_count();

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			if ( preg_match( '/\?\>[\s\n]+$/', $file_content, $matches ) ) {
				$this->add_error(
					'whitespace',
					'There is whitespace afer the closing <code>?&gt;</code> tag.',
					Basescanner::LEVEL_BLOCKER,
					basename( $file_path )
				);
				$result = false;
			}
		}

		return $result;
	}
}
