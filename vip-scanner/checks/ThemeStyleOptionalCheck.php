<?php

class ThemeStyleOptionalCheck extends BaseCheck {
	function check( $files ) {
		$result = true;

		$css = $this->merge_files( $files, 'css' );

		$checks = array(
			'\.sticky' => '.sticky',
			'\.bypostauthor' => '.bypostauthor',
		);

		foreach ( $checks as $key => $check ) {
			$this->increment_check_count();
			if ( ! preg_match( '/' . $key . '/mi', $css, $matches ) ) {
				$this->add_error(
					$key,
					sprintf( 'The CSS is missing the `%s` class.', $check ),
					'recommended'
				);
			}
		}

		return $result;
	}
}