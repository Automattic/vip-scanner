<?php

class ThemeCommentPaginationCheck extends BaseCheck {

	function check( $files ) {
		$result = true;
		$this->increment_check_count();

		// combine all the php files into one string to make it easier to search
		$php = $this->merge_files( $files, 'php' );

		if ( strpos( $php, 'paginate_comments_links' ) === false &&
		    ( strpos( $php, 'next_comments_link' ) === false && strpos( $php, 'previous_comments_link' ) === false ) ) {
			$this->add_error(
				'comment-pagination',
				'The theme doesn\'t have comment pagination code in it. Use `paginate_comments_links()` or `next_comments_link()` and `previous_comments_link()` to add comment pagination.',
				'required'
			);
			$result = false;
		}
		return $result;
	}
}