<?php

class ServerGlobalsCheck extends BaseCheck {

	function check( $files ) {
		$result = true;

		// Valid keys can be encapsulated with or without '', ""
		$server_global_pattern = '/\$_SERVER\[([^]]+|)\]/';

		$whitelist = array(
			'REQUEST_URI',
			'SCRIPT_FILENAME',
			'HTTP_HOST',
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {

			$this->increment_check_count();

			$lines = array();

			if ( preg_match_all( $server_global_pattern, $file_content, $matches, PREG_SET_ORDER ) ) {

				$filename = $this->get_filename( $file_path );

				foreach( $matches as $match ) {
					// Strip ' and " from the key
					$match_clean = str_replace("'", '', $match[1]);
					$match_clean = str_replace('"', '', $match_clean);

					// Check found $_SERVER key against whitelist
					if ( ! in_array( $match_clean, $whitelist ) ) {
						$lines_tmp = $this->grep_content( $match_clean, $file_content );

						// We'll save the actual found $_SERVER var against the line number
						foreach ($lines_tmp as $line_number => $line_content) {
							$lines[$line_number] = $match[0];
						}
					}
				}

				if ( ! empty( $lines ) ) {
					$this->add_error(
						'sever-superglobals',
						'Non whitelisted $_SERVER superglobals found in this file',
						'Blocker',
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
