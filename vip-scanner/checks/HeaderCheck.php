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
				'header-doctype',
				'The theme should have a valid doctype declaration: <code>&lt;!DOCTYPE html&gt;</code>.',
				BaseScanner::LEVEL_BLOCKER
			);
			$result = false;
        }

		return $result;
    }
}
