<?php

class ThemePostThumbnailCheck extends BaseCheck {
	function check( $files ) {
		$result = true;
		$this->increment_check_count();

		$php = $this->merge_files( $files, 'php' );

		if ( strpos( $php, 'the_post_thumbnail' ) === false ) {
			$this->add_error(
				'the_post_thumbnail',
				'No reference to `the_post_thumbnail()` was found in the theme. It is recommended that the theme implement this functionality instead of using custom fields for thumbnails.',
				'recommended'
			);
		}

		// TODO: This should check for full function, i.e. add_theme_support( ... )
		if ( strpos( $php, 'post-thumbnails' ) === false ) {
			$this->add_error(
				'post-thumbnails',
				'No reference to `post-thumbnails` was found in the theme. If the theme has thumbnail-like functionality, it should be implemented with `add_theme_support( \'post-thumbnails\' )` in the `functions.php` file.',
				'recommended'
			);
		}

		return $result;
	}
}