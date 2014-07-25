<?php
/**
 * Checks for proper Post Thumbnails.
 *
 * Warns if no Post Thumbnail support is declared.
 * Checks for the proper tags according to the theme support.
 * Checks for implementation of the feature via (get_)the_post_thumbnail.
 */

class PostThumbnailsCheck extends BaseCheck {

	function check( $files ) {

		$result = true;

		/**
		 * Grep the tags from the stylesheets.
		 */
		$css = $this->merge_files( $files, 'css' );
		$tags = array();

		if ( preg_match('|Tags:(.*)|i', $css, $tags) ) {
			$tags = array_map( 'trim', explode( ',', wp_kses( trim( $tags[1] ), array() ) ) );
		}

		/**
		 * Check if the theme declares Post Thumbnail support.
		 */
		$this->increment_check_count();

		$supports_thumbnails = false;
		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			if ( preg_match( '/[\s|]add_theme_support\((\s|)("|\')post-thumbnails("|\')(\s|)\)/m', $file_content, $matches ) ) {
				$supports_thumbnails = true;
			}
		}

		if ( ! $supports_thumbnails ) {
			// Warning about lack of Post Thumbnail support.
			$this->add_error(
				'post-thumbnails',
				'The theme has not declared support for Post Thumnbails.',
				Basescanner::LEVEL_WARNING
			);

			// Check for correct tags.
			if ( in_array( 'featured-images', $tags ) ) {
				$this->add_error(
					'post-thumbnails',
					'The theme has no support for Post Thumbnails, but the <code>featured-images</code> tag exists.',
					Basescanner::LEVEL_BLOCKER
				);
				$result = false;
			}

			// Return here, no further checks needed.
			return $result;
		}
		
		/**
		 * Check if (get_)the_post_thumbnail() is used in the theme.
		 */
		$this->increment_check_count();

		$php = $this->merge_files( $files, 'php' );
		if ( false === strpos( $php, 'the_post_thumbnail' ) ) {
			$this->add_error(
				'post-thumbnails',
				'The theme has declared support for Post Thumbnails, but does not use <code>get_the_post_thumbnail()</code> or <code>the_post_thumbnail()</code>.',
				Basescanner::LEVEL_BLOCKER
			);
			$result = false;
		}

		/**
		 * Check if the tags reflect the post thumbnails usage: either featured-images
		 * or featured-image-header needs to be used.
		 */
		if ( $supports_thumbnails && ! in_array( 'featured-images', $tags ) && ! in_array( 'featured-image-header', $tags ) ) {
			$this->add_error(
				'post-thumbnails',
				'The theme has declared support for Post Thumbnails, but the <code>featured-images</code> or <code>featured-image-header</code> tags do not exist.',
				Basescanner::LEVEL_BLOCKER
			);
			$result = false;
		}

		return $result;
	}
}
