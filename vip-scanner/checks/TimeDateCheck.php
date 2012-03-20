<?php
class TimeDateCheck extends BaseCheck {

	function check( $files ) {
		$result = true;

		$checks = array(
			//'/get_the_time\((\s|)["|\'][A-Za-z\s]+(\s|)["|\']\)/' => 'get_the_time( get_option( \'date_format\' ) )',
			'/\sdate\((\s|)["|\'][A-Za-z\s]+(\s|)["|\']\)/' => 'date( get_option( \'date_format\' ) )',
			'/[^get_]the_date\((\s|)["|\'][A-Za-z\s]+(\s|)["|\']\)/' => 'the_date( get_option( \'date_format\' ) )',
			'/[^get_]the_time\((\s|)["|\'][A-Za-z\s]+(\s|)["|\']\)/' => 'the_time( get_option( \'date_format\' ) )'
		);

		foreach ( $this->filter_files( $files, 'php' ) as $filepath => $file ) {
			foreach ( $checks as $key => $check ) {
				$this->increment_check_count();
				if ( preg_match( $key, $file, $matches ) ) {
					$filename = $this->get_filename( $filepath );
					$error = trim( rtrim( $matches[0], '(' ) );//trim( esc_html( rtrim( $matches[0], '(' ) ) );
					$this->add_error(
						$key,
						'At least one hard-coded date was found. Consider `get_option( \'date_format\' )` instead.',
						'info',
						$filename
					);
					$result = false;
				}
			}
		}
		return $result;
	}
}