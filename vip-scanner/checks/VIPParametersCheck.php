<?php
/**
 * Checks for deprecated or potentially problematic parameters.
 *
 * Parameter value will be matched with or without quotes
 * (e.g. 5, '5' will match 5 or 5, 'false' with match 'false' and false)
 */

class VIPParametersCheck extends BaseCheck {

	function check( $files ) {
		$result = true;
		$this->increment_check_count();

		$checks = array(
			'wpcom_vip_load_plugin' => array(
				'breadcrumb-navxt'     => array( 'level' => 'warning', 'note' => 'Deprecated VIP Plugin. Use breadcrumb-navxt-39 instead.' ),
				'livefyre'             => array( 'level' => 'warning', 'note' => 'Deprecated VIP Plugin. Use livefyre3 instead.' ),
				'feedwordpress'        => array( 'level' => 'blocker', 'note' => 'Deprecated VIP Plugin. No alternative available' ),
				'wordtwit-1.3-mod'     => array( 'level' => 'warning', 'note' => 'Deprecated VIP Plugin. Use publicize instead.' ),
				'uppsite'              => array( 'level' => 'blocker', 'note' => 'Deprecated VIP Plugin. Retired from Featured Partner Program.' ),
				'wpcom-related-posts'  => array( 'level' => 'warning', 'note' => 'Deprecated VIP Plugin. Functionality included in Jetpack.' ),
				'scrollkit-wp'         => array( 'level' => 'blocker', 'note' => 'Deprecated VIP Plugin. Scroll Kit has shut down.' ),
			),
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			
			// Loop through all functions.
			foreach ( $checks as $function => $data ) {

				// Loop through the parameters and look for all function/parameter combinations.
				foreach ( $data as $parameter => $parameter_data ) {
					if ( preg_match( '/' . $function . '\(\s*("|\')?' . $parameter . '("|\')?\s*\)/', $file_content, $matches ) ) {
						$lines = $this->grep_content( $matches[0], $file_content );
						$this->add_error(
							'vipparametercheck',
							esc_html( $parameter_data['note'] ),
							$parameter_data['level'],
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
