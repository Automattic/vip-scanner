<?php

class VIPRestrictedClassesCheck extends BaseCheck
{
	function check( $files ) {
		$result = true;

		$class_names = array(
			// WordPress Classes
			"WP_User_Query" => array( 'level' => "Note", "note" => "Use of WP_User_Query" ),
		);


		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			foreach ( $class_names as $class_name => $check_info ) {
				$this->increment_check_count();

				if ( strpos( $file_content, $class_name ) !== false ) {
					$pattern = "/\s+($class_name)+\s?\(+/msiU";

					if ( preg_match( $pattern, $file_content, $matches ) ) {
						$filename = $this->get_filename( $file_path );

						$lines = $this->grep_content( rtrim( $matches[0], '(' ), $file_content );

						$this->add_error(
								$class_name,
								$check_info['note'],
								$check_info['level'],
								$filename,
								$lines
						);

						$result = false;
					}
				}
			}
		}

		return $result;
	}
}
