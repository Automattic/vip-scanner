<?php
class PHPShortTagsCheck extends BaseCheck {
	function check( $files ) {
		$result = true;

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			$this->increment_check_count();

			$pattern = '/<\?(?!php|xml)/';

			if ( preg_match( $pattern, $file_content ) ) {

				$filename = $this->get_filename( $file_path );
				$lines = $this->grep_content( $pattern, $file_content );

				$this->add_error(
					'php-shorttags',
					'Found PHP short tags in file <strong>{$filename}</strong>.',
					'warning',
					$filename,
					$lines
				);
				$result = false;
			}
		}
		return $result;
	}
}
