<?php
/**
 * Checks for required Comment Headers in style.css:
 * Theme Name, Author and License.
 */

class StyleHeadersCheck extends BaseCheck {

	function check( $files ) {

		$this->increment_check_count();
		$result = true;

		$required_headers = array(
			'Theme Name:',
			'Author:',
			'License:'
		);

		foreach ( $this->filter_files( $files, 'css' ) as $file_path => $file_content ) {
			/**
			 * We only want the style.css file in the theme root folder.
			 */
			if ( dirname( $file_path ) !== get_stylesheet() && $this->get_filename( $file_path ) !== 'style.css' ) {
				continue;
			}

			foreach ( $required_headers as $header ) {
				if ( ! preg_match( '/' . $header . '/mi', $file_content, $matches ) ) {
					$this->add_error(
						'required-style-header-missing',
						sprintf( esc_html__( '%1$s needs to be added to the comment headers in the %2$s file.', 'vip-scanner' ),
							'<code>' . esc_html( $header ) . '</code>',
							'<code>style.css</code>'
						),
						BaseScanner::LEVEL_BLOCKER
					);
				}
			}
		}

		return $result;
	}
}