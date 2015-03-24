<?php

class VIPRestrictedVariablesCheck extends BaseCheck
{
	function check( $files ) {
		$result = true;

		$checks = array(
			'((?<![\\\'\"])\$\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*?(?![\\\'\"])|(?<![\\\'\"])\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*?\-\>\$(?![\\\'\"])|(?<![\\\'\"])\$\{(?:.*)[\}](?![\\\'\"]))' => array( "level" => "Warning", "note" => "Possible variable variables" ),
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			foreach ( $checks as $check => $check_info ) {
				$this->increment_check_count();

				if ( preg_match_all( $check, $file_content, $matches ) ) {
					$filename = $this->get_filename( $file_path );
					$lines = array();
					foreach ( $matches[0] as $match ) {
						$lines = array_merge( $this->grep_content( $match, $file_content ), $lines );
					}
					$this->add_error(
						$check,
						$check_info['note'],
						$check_info['level'],
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
