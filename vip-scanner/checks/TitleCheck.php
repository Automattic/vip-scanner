<?php
/**
 * Checks for the title:
 * Don't run tests when the theme has declared title tag support, added in 4.1.
 * Are there <title> and </title> tags?
 * Is there a call to wp_title()?
 * There can't be any hardcoded text in the <title> tag.
 */

class TitleCheck extends BaseCheck {

	function check( $files ) {
		$result = true;

		$php_code  = $this->merge_files( $files, 'php' );
		$php_files = $this->filter_files( $files, 'php' );
		
		/**
		 * Don't run tests when the theme has declared title tag support.
		 */
		if ( preg_match( '/add_theme_support\(\s*["|\']title-tag["|\']\s*\)/', $php_code ) ) {
			return $result;
		}
		

		/**
		 * Look for <title> and </title> tags.
		 */
		$this->increment_check_count();

		if ( false === strpos( $php_code, '<title>' ) || false === strpos( $php_code, '</title>' ) ) {
			$this->add_error(
				'title-no-title-tags',
				'The theme should have <code>&lt;title&gt;</code> tags in <code>header.php</code>.',
				BaseScanner::LEVEL_BLOCKER
			);
			$result = false;
		}

		/**
		 * Check whether there is a call to wp_title().
		 */
		if ( false === strpos( $php_code, 'wp_title(' ) ) {
			$this->add_error(
				'title-no-wp_title',
				'The theme should have a call to <code>wp_title()</code> in the <code>&lt;title&gt;</code> tags in <code>header.php</code>.',
				BaseScanner::LEVEL_BLOCKER
			);
			$result = false;
		}

		/**
		 * Check whether the the <title> tag contains something besides a call to wp_title().
		 */
		$this->increment_check_count();

		foreach ( $php_files as $file_path => $file_content ) {
			/**
			 * First looks ahead to see of there's <title>...</title>
			 * Then performs a negative look ahead for <title><?php wp_title(...); ?></title>
			 */
			if ( preg_match_all( '/(?=<title>(.*)<\/title>)(?!<title>\s*<\?php\s*wp_title\([^\)]*\);\s*\?>\s*<\/title>)/s', $file_content, $matches ) ) {
				$errors = $matches[0];

				foreach ( $errors as $error ) {
					$this->add_error(
						'header-title-contents',
						'Titles should contain nothing besides a call to <code>wp_title()</code>.',
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
