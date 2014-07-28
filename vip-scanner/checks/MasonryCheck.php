<?php
/**
 * Checks for Masonry v3, that comes bundled with WordPress 3.9.
 * Check if the theme uses the old 'jquery-masonry' script handle.
 * Check for deprecated options and methods.
 */

class MasonryCheck extends BaseCheck {

	function check( $files ) {
		$result = true;

		$php_files = $this->filter_files( $files, 'php' );

		/**
		 * Check for 'jquery-masonry' script handle.
		 */
		$this->increment_check_count();

		foreach( $php_files as $file_path => $file_content ) {
			if ( false !== strpos( $file_content, 'jquery-masonry' ) ) {
				$this->add_error(
					'masonry',
					'Found the <code>jquery-masonry</code> script handle. WordPress 3.9 upgraded Masonry to v3, that no longer requires jQuery. The new script handle to use is <code>masonry</code>.',
					Basescanner::LEVEL_BLOCKER,
					$this->get_filename( $file_path )
				);
				$result = false;
			}
		}

		/**
		 * Check for deprecated options and methods: isRTL option, isResizable option, gutterWidth option, and layout method.
		 */
		$this->increment_check_count();

		$js_files = $this->filter_files( $files, 'js' );

		$deprecated = array(
			'isRTL'        => 'The <code>isRTL</code> option has been removed, use <code>isOriginLeft: false</code> instead.',
			'isResizable:' => 'The <code>isResizable</code> option has been renamed to <code>isResizeBound</code>.',
			'gutterWidth'  => 'The <code>gutterWidth</code> option has been renamed to <code>gutter</code>.',
			'layout:'      => 'The <code>layout</code> method has been renamed to <code>layoutItems</code>.',
		);

		foreach( $js_files as $file_path => $file_content ) {
			// Look if the file concerns Masonry.
			if ( false !== stripos( $file_content, 'Masonry' ) ) {
				// Look for the deprecated options.
				foreach ( $deprecated as $code => $message ) {
					if ( false !== strpos( $file_content, $code . ':' ) ) {
						$this->add_error(
							'masonry',
							'Found deprecated jQuery option. ' . $message,
							Basescanner::LEVEL_BLOCKER,
							$this->get_filename( $file_path )
						);
						$result = false;
					}
				}
			}
		}


		return $result;
	}
}
