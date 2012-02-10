<?php

/**
 * WP.com Public Theme File Check.
 *
 * Make sure the required templates and stylesheets are present.
 * There are a few file that we do not want; make sure these are excluded.
 *
 * @see http://codex.wordpress.org/Theme_Development#Template_File_Checklist
 *
 * @todo Set non-blockers once it is supported.
 */

class TheamPubFileCheck extends BaseCheck {
	protected $error = array();

	/**
	 * List of required template files.
	 */
	private $required_templates = array(
		'header.php',
		'sidebar.php',
		'footer.php',
		'index.php',
		'archive.php',
		'page.php',
		'single.php',
		'comments.php',
		'search.php',
		'image.php',
		'404.php',
	);

	/**
	 * A list of template files that are NOT wanted.
	 */
	private $unwanted_templates = array(
		'comments-popup.php',
		'attachment.php',
	);

	/**
	 * List of required stylesheets.
	 */
	private $required_stylesheets = array(
		'style.css',
		'rtl.css',
	);

	function check( $files ) {

		$php = $this->filter_files( $files, 'php' );
		$templates = array_map( array( $this, 'get_filename' ), array_keys( $php ) );

		foreach ( $this->required_templates as $file ) {
			$this->increment_check_count();

			if ( ! in_array( $file, $templates ) ) {
				$this->add_error(
					'template-not-found',
					sprintf( '%1$s could not be found.', esc_html( $file ) ),
					'blocker'
				);
			}
		}

		foreach ( $this->unwanted_templates as $file ) {
			$this->increment_check_count();
			if ( in_array( $file, $templates ) ) {
				$this->add_error(
					'unwanted-template',
					sprintf( '%1$s was found in the theme. Please remove.', esc_html( $file ) ),
					'blocker'
				);
			}
		}

		$css = $this->filter_files( $files, 'css' );
		$stylesheets = array_map( array( $this, 'get_filename' ), array_keys( $css ) );

		foreach ( $this->required_stylesheets as $file ) {
			$this->increment_check_count();
			if ( ! in_array( $file, $stylesheets ) ) {
				$this->add_error(
					'stylesheet-not-found',
					sprintf( '%1$s could not be found.', esc_html( $file ) ),
					'blocker'
				);
			}
		}

		return true;
	}
}
