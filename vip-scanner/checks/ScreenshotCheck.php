<?php
/**
 * Checks for a correct screenshot:
 * A screenshot.png file has to exist.
 * The screenshot has to be sized 880 x 660 pixels.
 *
 */

class ScreenshotCheck extends BaseCheck {

	function check( $files ) {
		$result = true;

		/**
		 * Check if the screenshots.png file exists.
		 */
		$this->increment_check_count();

		if ( ! $this->file_exists( $files, 'screenshot.png' ) ) {
			$this->add_error(
				'screenshot',
				"The theme doesn't include a screenshot.png file.",
				BaseScanner::LEVEL_BLOCKER
			);

			// We don't have a screenshot, so no further checks.
			return $result = false;
		}
		
		/**
		* We have screenshot, check the size.
		*/
		$this->increment_check_count();
	
		$png_files = $this->filter_files( $files, 'png' );
		foreach( $png_files as $path => $content ) {
			if ( 'screenshot.png' === basename( $path ) ) {
				$image_size = getimagesize( $path );
				$message = '';

				if ( 880 != $image_size[0] ) {
					$message .= ' The width needs to be 880 pixels.';
				}
				if ( 660 != $image_size[1] ) {
					$message .= ' The height needs to be 660 pixels.';
				}

				if ( ! empty( $message ) ) {
					$this->add_error(
						'screenshot',
						'The screenshot does not have the right size.' . $message,
						BaseScanner::LEVEL_BLOCKER
					);
					$result = false;
				}
			}
		}

		return $result;
	}
}
