<?php
class TimeDateCheck extends BaseCheck {

	function check( $files ) {

		$this->increment_check_count();
		$result = true;

		$format_regex = '\(\s*["\'][a-zA-Z0-9\s\p{P}]+["\']\s*\)';
		$functions = array(
			'get_the_date',
			'the_date',
			'get_the_time',
			'the_time',
			'get_comment_time',
			'comment_time',
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {

			foreach ( $functions as $function ) {
				/**
				 * Before a function, there's either a start of a line, whitespace, . or (
				 * This is to avoid false positives.
				 */
				if ( preg_match( '/(?:^|[\s\.\(])' . $function . $format_regex . '/', $file_content, $matches ) ) {
					$result = false;
					$this->add_error(
						'hardcoded-date-time',
						esc_html__( 'Found a hardcoded time or date format.', 'vip-scanner' ),
						BaseScanner::LEVEL_WARNING,
						basename( $file_path ),
						$this->grep_content( rtrim( $matches[0], '(' ), $file_content )
					);
				}
			}
		}

		return $result;
	}
}