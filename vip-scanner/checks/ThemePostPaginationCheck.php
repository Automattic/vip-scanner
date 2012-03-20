<?php

class ThemePostPaginationCheck extends BaseCheck {
	function check( $files ) {

		$result = true;
		$this->increment_check_count();

		$php = $this->merge_files( $files, 'php' );

		if ( strpos( $php, 'posts_nav_link' ) === false && strpos( $php, 'paginate_links' ) === false &&
		   ( strpos( $php, 'previous_posts_link' ) === false && strpos( $php, 'next_posts_link' ) === false )
		   ) {
			$this->add_error(
				'theme-post-pagination',
				'The theme doesn\'t have post pagination code in it. Use `posts_nav_link()` or `paginate_links()` or `next_posts_link()` and `previous_posts_link()` to add post pagination.',
				'required'
			);
			$result = false;
		}
		return $result;
	}
}