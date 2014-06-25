<?php
/**
 * Checks for usage of functions that themes should not use:
 *
 * register_post_type()
 * register_taxonomy()
 * add_shortcode()
 * add_meta_box()
 * add_help_tab() (WP_Screen method)
 * query_posts()
 */

class ForbiddenFunctionsCheck extends BaseCheck {

	function check( $files ) {
		$result = true;

		$forbidden_functions = array(
			'register_post_type',
			'register_taxonomy',
			'add_shortcode',
			'add_meta_box',
			'add_help_tab',
			'query_posts',
		);

		$php = $this->merge_files( $files, 'php' );


		foreach ( $forbidden_functions as $function ) {
			$this->increment_check_count();

			if ( false !== strpos( $php, $function ) ) {
				$this->add_error(
					'forbidden-function',
					sprintf( 'The function %s was found in the theme. Themes cannot use this function, please remove it.', '<code>' . $function . '</code>' ),
					'blocker'
				);
				$result = false;
			}
		}
		return $result;
	}
}
