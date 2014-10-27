<?php
/**
 * Checks for deprecated or potentially problematic parameters.
 */

class VIPParametersCheck extends BaseCheck {

	function check( $files ) {
		$result = true;
		$this->increment_check_count();

		$checks = array(
			'wpcom_vip_load_plugin' => array(
				'breadcrumb-navxt'     => 'Deprecated VIP Plugin. Use breadcrumb-navxt-39 instead.',
				'livefyre'             => 'Deprecated VIP Plugin. Use livefyre3 instead.',
				'feedwordpress'        => 'Deprecated VIP Plugin. No alternative available',
				'wordtwit-1.3-mod'     => 'Deprecated VIP Plugin. Use publicize instead.',
				'uppsite'              => 'Deprecated VIP Plugin. Retired from Featured Partner Program.',
				'wpcom-related-posts'  => 'Deprecated VIP Plugin. Functionality included in Jetpack.',
				'scrollkit-wp'         => 'Deprecated VIP Plugin. Scroll Kit has shut down.',
			),
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			
			// Loop through all functions.
			foreach ( $checks as $function => $data ) {

				// Loop through the parameters and look for all function/parameter combinations.
				foreach ( $data as $parameter => $message ) {
					if ( preg_match( '/' . $function . '\(\s*("|\')' . $parameter . '("|\')\s*\)/', $file_content, $matches ) ) {
						$lines = $this->grep_content( $matches[0], $file_content );
						$this->add_error(
							'vipparametercheck',
							esc_html( $message ),
							BaseScanner::LEVEL_WARNING,
							$this->get_filename( $file_path ),
							$lines
						);
						$result = false;
					}
				}
			}
		}

		return $result;
	}
}
