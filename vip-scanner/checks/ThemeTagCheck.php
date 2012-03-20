<?php

class ThemeTagCheck extends BaseCheck {
	function check( $files ) {
		$result = true;
		$this->increment_check_count();

		$php = $this->merge_files( $files, 'php' );

		if ( strpos( $php, 'the_tags' ) === false && strpos( $php, 'get_the_tag_list' ) === false && strpos( $php, 'get_the_term_list' ) === false ) {
			$this->add_error(
				'theme-tags',
				'This theme doesn\'t seem to display tags. Modify it to display tags in appropriate locations.',
				'required'
			);
			$result = false;
		}

		return $result;
	}
}