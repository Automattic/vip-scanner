<?php

// From the codex: The following parameters are read only, and should only be used in the context of current_theme_supports()
class ThemeSupportCheck extends BaseCheck {
	function check( $files ) {
		$result = true;

		$checks = array(
			'/add_theme_support\((\s|)("|\')custom-headers("|\')(\s|)\)/' => 'add_custom_image_header()',
			'/add_theme_support\((\s|)("|\')custom-background("|\')(\s|)\)/' => 'add_custom_background()',
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			foreach ($checks as $key => $check) {
				$this->increment_check_count();
				if ( preg_match( $key, $file_content, $matches ) ) {
					$filename = $this->get_filename( $file_path );
					$error = rtrim( $matches[0], '(' );//esc_html( rtrim( $matches[0],'(' ) );
					$lines = $this->grep_content( rtrim( $matches[0], '(' ), $file_content );
					$this->add_error(
						$key,
						sprintf( '`%1$s` was found in the file. Use `%2$s` instead.', $error, $check ),
						'required',
						$filename,
						$lines
					);
					$result = false;
				}
			}
		}
		return $result;
	}
}