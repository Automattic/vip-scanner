<?php

/**
 * Checks that the $content_width variable is set in functions.php
 */

class ThemeContentWidthCheck extends BaseCheck {

	function check( $files ) {

		$this->increment_check_count();
		$result = true;

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			/**
			 * We only want the functions.php file in the theme root folder.
			 */
			if ( dirname( $file_path ) !== get_stylesheet() && $this->get_filename( $file_path ) !== 'functions.php' ) {
				continue;
			}

			if ( false === strpos( $file_content, '$content_width' ) ) {
				$result = false;
				$this->add_error(
					'content-width-missing',
					sprintf( esc_html__( 'The %1$s variable needs to be set in the %2$s file.', 'vip-scanner' ),
						'<code>$content_width</code>',
						'<code>functions.php</code>'
					),
					BaseScanner::LEVEL_BLOCKER
				);
			}
		}

		return $result;
	}
}