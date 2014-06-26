<?php
/**
 * Checks for a correct body_class() implementation:
 * If there's a <body> tag in the file, there needs to be body_class() present.
 * body_class() can't contain any paramters.
 *
 */

class BodyClassCheck extends BaseCheck {

	function check( $files ) {

		$this->increment_check_count();
		$result = true;

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			// Check if the file contains the <body> tag
			if ( preg_match( '/<body/', $file_content, $matches ) ) {
				// There's <body>, is there body_class?
				if ( false === strpos( $file_content, 'body_class' ) ) {
					$this->add_error(
						'body-class',
						'There needs to be call to <code>body_class()</code> in the <code>&lt;body&gt;</code> tag.',
						'blocker',
						basename( $file_path )
					);
				} 
				// Theres <body> and body_class, are there classes passed as a parameter?
				else if ( false === strpos( $file_content, 'body_class()' ) ) {
					$this->add_error(
						'body-class',
						'The <code>body_class</code> filter should be used instead of the <code>$class</code> parameter of <code>body_class()</code>.',
						'blocker',
						basename( $file_path )
					);
				}
			}
		}
		return $result;
	}
}
