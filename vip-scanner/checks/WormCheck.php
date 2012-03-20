<?php
class WormCheck extends BaseCheck {
	function check( $files ) {
		$result = true;

		$checks = array(
			'/wshell\.php/'=>'<strong>PHP shell was found!</strong>',
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			foreach ( $checks as $key => $check ) {
				$this->increment_check_count();
				if ( preg_match( $key, $file_content, $matches ) ) {
					$filename = $this->get_filename( $file_path );
					$error = $matches[0];
					$lines = $this->grep_content( $error, $file_content );
					$this->add_error(
						$key,
						$check,
						'warning',
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