<?php
/**
 * Checks for the presence of post archive navigation.
 */

class ThemePostPaginationCheck extends BaseCheck {
	
	function check( $files ) {
		$result = true;

		$php_code = $this->merge_files( $files, 'php' );
	
		/**
		 * Look for post pagination functions:
		 * (get_)the_pagination()
		 * posts_nav_link()
		 * paginate_links()
		 * (get_)previous_posts_link() and (get_)next_posts_link()
		 */
		$this->increment_check_count();

		if (   0 === preg_match( '/\s(get_)?the_pagination\(/', $php_code )
		  &&   0 === preg_match( '/\sposts_nav_link\(/', $php_code )
		  &&   0 === preg_match( '/\spaginate_links\(/', $php_code )
		  && ( 0 === preg_match( '/\s(get_)?previous_posts_link\(/', $php_code ) && 0 === preg_match( '/\s(get_)?next_posts_link\(/', $php_code ) ) ) {
			$result = false;
			$this->add_error(
					 'post-pagination-no-pagination',
					  esc_html__( 'The theme does not contain any post pagination (links to next/previous set of posts).', 'vip-scanner' ),
					  Basescanner::LEVEL_BLOCKER
				);
		}
		
		return $result;
	}
}