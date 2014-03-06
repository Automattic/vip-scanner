<?php

class WPUserQueryCheck extends BaseCheck {

	function check( $files ) {
		$result = true;

		$pattern = '/(WP_User_Query)/';

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {

			$this->increment_check_count();

			if ( preg_match_all( $pattern, $file_content, $matches, PREG_SET_ORDER ) ) {

				$filename = $this->get_filename( $file_path );

				foreach( $matches as $match ) {
					if ( ! in_array( $match[1], $whitelist ) ) {
						$lines = $this->grep_content( $match[1], $file_content );
					}
				}

				if ( ! empty( $lines ) ) {
					$this->add_error(
						'wp-user-query',
						'Use of WP_User_Query',
						'info',
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
