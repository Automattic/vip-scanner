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
					'value'    => 'breadcrumb-navxt',
					'position' => 0,
					'level'    => 'warning',
					'note'     => 'Deprecated VIP Plugin. Use breadcrumb-navxt-39 instead.'
				),
				array(
					'value'    => 'livefyre',
					'position' => 0,
					'level'    => 'warning',
					'note'     => 'Deprecated VIP Plugin. Use livefyre3 instead.'
				),
				array(
					'value'    => 'feedwordpress',
					'position' => 0,
					'level'    => 'blocker',
					'note'     => 'Deprecated VIP Plugin. No alternative available'
				),
				array(
					'value'    => 'wordtwit-1.3-mod',
					'position' => 0,
					'level'    => 'warning',
					'note'     => 'Deprecated VIP Plugin. Use publicize instead.'
				),
				array(
					'value'    => 'uppsite',
					'position' => 0,
					'level'    => 'blocker',
					'note'     => 'Deprecated VIP Plugin. Retired from Featured Partner Program.'
				),
				array(
					'value'    => 'wpcom-related-posts',
					'position' => 0,
					'level'    => 'warning',
					'note'     => 'Deprecated VIP Plugin. Functionality included in Jetpack.'
				),
				array(
					'value'    => 'scrollkit-wp',
					'position' => 0,
					'level'    => 'blocker',
					'note'     => 'Deprecated VIP Plugin. Scroll Kit has shut down.'
				),
			),
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			if ( false == $this->check_file_contents( $checks, $file_path, $file_content ) ) {
				$result = false;
			}
		}

		return $result;
	}

	function check_file_contents( $checks, $file_path, $file_content ) {
		$result = true;

		// Loop through all functions.
		foreach ( $checks as $function => $data ) {

			// Loop through the parameters and look for all function/parameter combinations.
			foreach ( $data as $parameter_data ) {
				$previous_params = '';
				if ( isset( $parameter_data['position'] ) && $parameter_data['position'] > 0 ) {
					$previous_params = '(("|\')?(.+)("|\')?,\s*){' . $parameter_data['position'] . '}';
				} elseif ( ! isset( $parameter_data['position'] ) ) {
					$previous_params = '(.+)';
				}

				if ( preg_match( '/' . $function . '\(\s*' . $previous_params . '("|\'|\s)' . $parameter_data['value'] . '("|\'|,|\s)\s*/', $file_content, $matches ) ) {
					$lines = $this->grep_content( $matches[0], $file_content );
					$this->add_error(
						'vip-parameters-' . $parameter_data['value'],
						esc_html( $parameter_data['note'] ),
						$parameter_data['level'],
						$this->get_filename( $file_path ),
						$lines
					);
					$result = false;
				}
			}
		}

		return $result;
	}
}
