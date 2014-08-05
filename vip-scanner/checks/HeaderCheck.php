<?php
/**
 * Checks for the header file, so everything from the opening doctype declaration to the closing </head>:
 * There needs to be a valid HTML5 doctype.
 * There can't be any hardcoded text in the <title> tag.
 */

class HeaderCheck extends BaseCheck {

	function check( $files ) {
		$result = true;

        /**
         * Check for HTML5 doctype.
         */
        $this->increment_check_count();

        if ( false === strpos( $this->merge_files( $files, 'php' ), '<!DOCTYPE html>' ) ) {
			$this->add_error(
				'header',
				'The theme should have a valid doctype declaration: <code>&lt;!DOCTYPE html&gt;</code>.',
				BaseScanner::LEVEL_BLOCKER
			);
			$result = false;
        }

        /**
         * Check whether the the <title> tag contains something besides a call to wp_title()
         */
		$this->increment_check_count();

        foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
            /**
             * First looks ahead to see of there's <title>...</title>
             * Then performs a negative look ahead for <title><?php wp_title(...); ?></title>
             */
            if ( preg_match_all( '/(?=<title>(.*)<\/title>)(?!<title>\s*<\?php\s*wp_title\([^\)]*\);\s*\?>\s*<\/title>)/s', $file_content, $matches ) ) {
                $errors = $matches[0];

				foreach ( $errors as $error ) {
					$this->add_error(
						'header-title',
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
