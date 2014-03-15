<?php
class PHPClosingTagsCheck extends BaseCheck {
	function check( $files ) {
		$result = true;

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			$this->increment_check_count();

			$pattern = '/\?>\s*$/';

			if ( preg_match( $pattern, $file_content, $matches ) ) {
				$filename = $this->get_filename( $file_path );
				$this->add_error(
					'php-closingtags',
					'Closing PHP tag at the end of file. Remove to avoid accidental whitespace output errors.',
					'warning',
					$filename
				);
				$result = false;
			}
		}
		return $result;
	}
}
