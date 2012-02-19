<?php

/**
 * WP.com Public Theme: Scan individual files.
 */
class TheamPubIndividualFiles extends BaseCheck {

	protected $error = array();

	function check( $files ) {

		foreach ( $this->filter_files( $files, 'php' ) as $path => $code ) {

			$filename = $this->get_filename( $path );

			if ( 'functions.php' == $filename ) {
				$this->increment_check_count();
				if ( false === strpos( $code, '$content_width' ) ) {
					$this->add_error(
						'functions-file',
						sprintf( '<var>$content_width</var> could not be found in %1$s.', esc_html( $filename ) ),
						'blocker'
					);
				}
			}

			if ( 'image.php' == $filename ) {
				$this->increment_check_count();
				if ( false === strpos( $code, 'the_content(' ) ) {
					$this->add_error(
						'template-image',
						sprintf( '<var>$content_width</var> could not be found in %1$s. It is often best to set this variable directly in %1$s especially in cases where the sidebar is omited and the image fills the full width of the template.', esc_html( $filename ) ),
						'blocker'
					);
				}

				// NOT a blocker.
				$this->increment_check_count();
				if ( false === strpos( $code, '$content_width' ) ) {
					$this->add_error(
						'template-image',
						sprintf( '<var>$content_width</var> could not be found in %1$s. It is often best to set this variable directly in %1$s especially in cases where the sidebar is omited and the image fills the full width of the template.', esc_html( $filename ) ),
						'blocker'
					);
				}
			}

			if ( 'header.php' == $filename ) {
				$this->increment_check_count();
				if ( false === strpos( $code, '<!DOCTYPE html>' ) ) {
					$this->add_error(
						'template-header',
						sprintf( 'No HTML5 doctype could be found in %1$s.', esc_html( $filename ) ),
						'blocker'
					);
				}
			}

			// NOT a blocker.
			if ( '404.php' == $filename ) {
				$this->increment_check_count();
				if ( strpos( $code, 'post_class' ) ) {
					$this->add_error(
						'template-header',
						sprintf( 'The function <code>post_class()</code> has no effect in %1$s.', esc_html( $filename ) ),
						'blocker'
					);
				}
			}
		}

		return true;
	}
}
