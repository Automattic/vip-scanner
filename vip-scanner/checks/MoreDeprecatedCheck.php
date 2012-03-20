<?php

class MoreDeprecatedCheck extends BaseCheck {
	function check( $files ) {

		$result = true;

		$checks = array(
			'get_bloginfo\((\s|)("|\')home("|\')(\s|)\)' => 'get_bloginfo( \'url\' )',
			'bloginfo\((\s|)("|\')home("|\')(\s|)\)' => 'bloginfo( \'url\' )'
		);

		foreach( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			foreach( $checks as $deprecated => $replacement ) {
				$this->increment_check_count();

				if( preg_match( '/[\s|]' . $deprecated . '/m', $file_content, $matches ) ) {
					$filename = $this->get_filename( $file_path );
					$error = rtrim( $matches[0], '(' );
					$grep = $this->grep_content( $error, $file_content );

					$this->add_error(
						$deprecated,
						'blocker',
						sprintf( '`%1$s` is deprecated. Use `%2$s` instead.', $error, $replacement ),
						$filename,
						$grep
					);
					$result = false;
				}
			}
		}
		return $result;
	}
}