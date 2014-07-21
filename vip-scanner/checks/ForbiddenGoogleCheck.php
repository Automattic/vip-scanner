<?php
/**
 * Checks for Google Custom Search and Google Ads.
 */

class ForbiddenGoogleCheck extends BaseCheck {

	function check( $files ) {

		$this->increment_check_count();
		$result = true;

		$checks = array(
			// Targets strings similar to this: cx = '011885437460350542428:1ncuxxj0qzo'
			'/cx[\s|]=[\s|][\'|\"][0-9]{21}:[a-z0-9]{10}/' => 'Google search code detected. Themes cannot use Google Custom Search, use the build-in WordPress search instead.',
			// Google Ad Client number: pub-(16 digits)
			'/pub-[0-9]{16}/'                              => 'Google advertising code detected. Themes cannot use Google Ads.',
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {

			foreach ( $checks as $check => $message ) {
				
				/**
				 * Before a function, there's either a start of a line, whitespace, . or (
				 */
				if ( preg_match( $check, $file_content, $matches ) ) {
					$this->add_error(
						'forbidden-google',
						$message,
						BaseScanner::LEVEL_BLOCKER,
						$this->get_filename( $file_path )
					);
					$result = false;
				}
			}

		}

		return $result;
	}
}
