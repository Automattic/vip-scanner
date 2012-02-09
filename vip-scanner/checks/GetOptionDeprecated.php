<?php

class GetOptionDeprecated extends BaseCheck {
	function check( $files ) {

		$result = true;

		$checks = array(
			'/[\s|]get_option\((\s|)("|\')home("|\')(\s|)\)/m' => 'home_url()',
			'/[\s|]get_option\((\s|)("|\')site_url("|\')(\s|)\)/m' => 'site_url()',
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			foreach ( $checks as $key => $check ) {
				$this->increment_check_count();
				if ( preg_match( $key, $file_content, $matches ) ) {
					$filename = $this->get_filename( $file_path );
					$error = rtrim( $matches[0], '(' );//esc_html( rtrim($matches[0],'(') );
					$lines = $this->grep_content( rtrim($matches[0],'('), $file_content );
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