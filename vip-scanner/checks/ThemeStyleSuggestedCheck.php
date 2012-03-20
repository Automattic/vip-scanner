<?php
class ThemeStyleSuggestedCheck extends BaseCheck {
	function check( $files ) {
		$result = true;

		$css = $this->merge_files( $files, 'css' );

		$checks = array(
			'^Tags:' => 'Tags:'
		);

		foreach ($checks as $key => $check) {
			$this->increment_check_count();
			if ( !preg_match( '/' . $key . '/mi', $css, $matches ) ) {
				$this->add_error(
					$key,
					sprintf( '`%s` is missing from your style.css header.', $check ),
					'recommended'
				);
				$result = false;
			}
		}
		return $result;
	}
}