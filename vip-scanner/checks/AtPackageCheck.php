<?php
/**
 * Checks for correct @package information.
 */

class AtPackageCheck extends BaseCheck {

	function check( $files ) {

		$result = true;
		$php_files = $this->filter_files( $files, 'php' );

		/**
		 * Check for wrong @package WordPress.
		 */
		$this->increment_check_count();

		foreach ( $php_files as $file_path => $file_content ) {
			if ( false !== stripos( $file_content, '@package WordPress' ) ) {
				$this->add_error(
					'atpackage',
					'The theme uses <code>@package WordPress</code>. This is reserved for WordPress Core files.',
					Basescanner::LEVEL_WARNING,
					$this->get_filename( $file_path )
				);
				$result = false;
			}
		}

		/**
		 * Check if every PHP file contains @package information. 
		 */
		$this->increment_check_count();

		foreach ( $php_files as $file_path => $file_content ) {
			if ( false === stripos( $file_content, '@package' ) ) {
				$this->add_error(
					'atpackage',
					'The file does not contain <code>@package</code> information. Every PHP file should have <code>@package Themename</code> in its header, with <code>Themename</code> being one word with no spaces or newlines containing only letters, digits, and "_", "-", "[" or "].',
					Basescanner::LEVEL_WARNING,
					$this->get_filename( $file_path )
				);
				$result = false;
			}
		}

		return $result;
	}
}
