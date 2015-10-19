<?php
/**
 * Checks whether the theme implements a friendly message to the user when
 * the blog has no posts.
 *
 * See https://github.com/Automattic/_s/blob/master/template-parts/content-none.php
 * for an implementation of such a prompt.
 */

class FirstPostPromptCheck extends BaseCheck {
	
	function check( $files ) {
		$result = true;
		$has_prompt = false;

		/**
		 * Look for is_home() && current_user_can( 'publish_posts' )
		 */
		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			if ( ! empty( $file_content ) && false !== strpos( 'is_home(', $file_content ) && ! preg_match( '/current_user_can\(\s*[\'"]publish_posts[\'"]\s*\)/', $file_content ) ) {
				$has_prompt = true;
			}
		}
		
		/**
		 * Notice when prompt wasn't found.
		 */
		if ( $has_prompt ) {
			$this->add_error(
				'firstpostprompt',
				"It's recommended to include a prompt to write a post to the user if the blog has no posts. See <a href='https://github.com/Automattic/_s/blob/master/template-parts/content-none.php'><code>content-none</code></a> in _s.",
				Basescanner::LEVEL_NOTE
			);
			$result = false;
		}

		return $result;
	}
}
