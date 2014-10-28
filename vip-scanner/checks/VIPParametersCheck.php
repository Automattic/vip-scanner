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
				array(
					'value' => 'breadcrumb-navxt',
					'pos'   => 0,
					'level' => 'warning',
					'note'  => 'Deprecated VIP Plugin. Use breadcrumb-navxt-39 instead.'
				),
				array(
					'value' => 'livefyre',
					'pos'   => 0,
					'level' => 'warning',
					'note'  => 'Deprecated VIP Plugin. Use livefyre3 instead.'
				),
				array(
					'value' => 'feedwordpress',
					'pos'   => 0,
					'level' => 'blocker',
					'note'  => 'Deprecated VIP Plugin. No alternative available'
				),
				array(
					'value' => 'wordtwit-1.3-mod',
					'pos'   => 0,
					'level' => 'warning',
					'note'  => 'Deprecated VIP Plugin. Use publicize instead.'
				),
				array(
					'value' => 'uppsite',
					'pos'   => 0,
					'level' => 'blocker',
					'note'  => 'Deprecated VIP Plugin. Retired from Featured Partner Program.'
				),
				array(
					'value' => 'wpcom-related-posts',
					'pos'   => 0,
					'level' => 'warning',
					'note'  => 'Deprecated VIP Plugin. Functionality included in Jetpack.'
				),
				array(
					'value' => 'scrollkit-wp',
					'pos'   => 0,
					'level' => 'blocker',
					'note'  => 'Deprecated VIP Plugin. Scroll Kit has shut down.'
				),
			),
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			
			// Loop through all functions.
			foreach ( $checks as $function => $data ) {

				// Loop through the parameters and look for all function/parameter combinations.
				foreach ( $data as $parameter_data ) {
					$previous_params = '(("|\')?(.+)("|\')?,\s){' . $parameter_data['pos'] . '}';
					if ( preg_match( '/' . $function . '\(\s*' . ( $parameter_data['pos'] > 0 ? $previous_params : '' ) . '("|\')?' . $parameter_data['value'] . '("|\')?\s*/', $file_content, $matches ) ) {
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
