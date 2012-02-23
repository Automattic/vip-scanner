<?php
class NonPrintableCheck extends BaseCheck {
	function check( $files ) {
		$result = true;

		foreach ( $this->filter_files( $files, 'php' ) as $path => $content) {

			$this->increment_check_count();

			// 09 = tab
			// 0A = line feed
			// 0D = new line
			$pattern = '/[\x00-\x08\x0B-\x0C\x0E-\x1F\x80-\xFF]/';

			if ( preg_match( $pattern, $content, $matches ) ) {
				$filename = $this->get_filename( $path );
				$non_print = $this->preg_content( $pattern, $content );
				$this->add_error(
					'non-printable',
					'Non-printable characters were found in the file. You may want to check this file for errors.',
					'info',
					$filename,
					$non_print
				);
			}
		}

		// return the pass/fail
		return $result;
	}
}