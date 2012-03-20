<?php

class ThemeEditorStyleCheck extends BaseCheck {
	function check( $files ) {
		$result = true;
		$this->increment_check_count();

		$php = $this->merge_files( $files, 'php' );

		if ( strpos( $php, 'add_editor_style' ) === false ) {
			$this->add_error(
				'add_editor_style',
				'No reference to `add_editor_style()` was found in the theme. It is recommended that the theme implement editor styling, to make the editor content match the resulting post output in the theme, for a better user experience.',
				'recommended'
			);
		}
		return $result;
	}
}