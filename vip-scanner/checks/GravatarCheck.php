<?php

class GravatarCheck extends BaseCheck {

	function check( $files ) {
		$result = true;

		$this->increment_check_count();
		$php = $this->merge_files( $files, 'php' );

		if ( ( strpos( $php, 'get_avatar' ) === false ) && ( strpos( $php, 'wp_list_comments' ) === false ) ) {
			$this->add_error( 'gravatar', 'blocker', 'Gravatar support not found. Use `get_avatar` or `wp_list_comments` to add this support.' );
			$result = false;
		}

		return $result;
	}

}