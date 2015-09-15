<?php
/**
 * Checks for a correct comments implementation:
 *
 * Comments listing via wp_list_comments().
 * Comments pagination via paginate_comments_links() or next_comments_link() and previous_comments_link().
 */

class CommentsCheck extends BaseCheck {

	function check( $files ) {

		$result = true;
		$php = $this->merge_files( $files, 'php' );
		$php_files = $this->filter_files( $files, 'php');

		/**
		 * Comments listing.
		 */
		$this->increment_check_count();
		if ( false === strpos( $php, 'wp_list_comments' ) ) {
			$this->add_error(
				'comments-wp-list-comments',
				"The theme doesn't have a call to <code>wp_list_comments()</code> in it.",
				Basescanner::LEVEL_BLOCKER
			);
			$result = false;
		}

		/**
		 * Comments pagination.
		 */
		$this->increment_check_count();
		if ( false === strpos( $php, 'paginate_comments_links' ) && ( false === strpos( $php, 'previous_comments_link' ) || false === strpos( $php, 'next_comments_link' ) ) ) {
			$this->add_error(
				'comments',
				"The theme doesn't have comment pagination code in it. Use <code>paginate_comments_links()</code> or <code>next_comments_link()</code> and <code>previous_comments_link()</code> to add comment pagination.",
				Basescanner::LEVEL_BLOCKER
			);
			$result = false;
		}

		/**
		 * Check whether the comment form is filtered.
		 */
		foreach( $php_files as $file_path => $file_content ) {
			if ( preg_match( '/add_filter\(\s*[\'"]comment_form_defaults[\'"]/', $file_content ) ) {
				$this->add_error(
					'filtering_comment_form_defaults',
					wp_kses( __( "WordPress.com has it's own commenting experience, themes should not filter the comment form defaults via <code>add_filter( 'comment_form_defaults', [...] )</code>." ), array( 'code' => array() ) ),
					Basescanner::LEVEL_WARNING,
					$this->get_filename( $file_path )
				);
				$result = false;
			}
		}

		return $result;
	}
}
