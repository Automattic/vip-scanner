<?php

class ThemeIncludeCheck extends BaseCheck {

	function check( $files ) {

		$result = true;
		$this->increment_check_count();

		$php = $this->merge_files( $files, 'php' );

		if ( preg_match( '/include[\s|]*\(/', $php ) != 0 || preg_match( '/require[\s|]*\(/', $php ) != 0 ) {
			$this->add_error(
				'include-files',
				'The theme appears to use `include` or `require`. If these are being used to include separate sections of a template from independant files, then `get_template_part()` should be used instead.',
				'info'
			);
		}
		return $result;
	}
}