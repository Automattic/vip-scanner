<?php

class ThemePostFormatCheck extends BaseCheck {
	function check( $files ) {
		$result = true;
		$this->increment_check_count();

		$php = $this->merge_files( $files, 'php' );
		$css = $this->merge_files( $files, 'css' );

		$checks = array(
			'/add_theme_support\((\s|)("|\')post-formats("|\')/m'
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			foreach ($checks as $check) {
				$this->increment_check_count();
				if ( preg_match( $check, $file_content, $matches ) ) {
					if ( ! strpos( $php, 'get_post_format' ) && ! strpos( $php, 'has_post_format' ) && ! strpos( $css, '.format' ) ) {
						$filename = $this->get_filename( $file_path );
						$error = rtrim( $matches[0], '(' );//esc_html( rtrim( $matches[0], '(' ) );
						$lines = $this->grep_content( rtrim( $matches[0], '(' ), $file_content );
						$this->add_error(
							$check,
							sprintf( '`%s` was found. However `get_post_format` and/or `has_post_format` were not found, and no use of formats was detected in the CSS.', $error ),
							'required',
							$filename,
							$lines
						);
						$result = false;
					}
				}
			}
		}
		return $result;
	}
}