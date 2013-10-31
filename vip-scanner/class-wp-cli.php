<?php

/**
 * Perform automated scanning with VIP Scanner
 *
 * @package wp-cli
 * @since 0.5
 * @see https://github.com/wp-cli/wp-cli
 */
WP_CLI::add_command( 'vip-scanner', 'VIPScanner_Command' );

class VIPScanner_Command extends WP_CLI_Command {
	/**
	 * Perform checks on a theme
	 *
	 * @subcommand scan-theme
	 * @synopsis --theme=<theme-name> --scan_type=<scan-type> [--format=<format>] [--summary=<summary>]
	 */
	public function scan_theme( $args, $assoc_args ) {
		$defaults = array(
			'theme'			=> null,
			'scan_type' 	=> 'WP.org Theme Review',
			'format' 		=> 'table',
			'summary' 		=> false
		);

		$args = wp_parse_args( $assoc_args, $defaults );

		$scanner = VIP_Scanner::get_instance()->run_theme_review( $args['theme'], $args['scan_type'] );

		if ( ! $scanner )
			WP_CLI::error( sprintf( 'Scanning of %s failed', $args['theme'] ) );

		if ( $args['summary'] ) {
			$results = $scanner->get_results();

			$data = array();

			$data[] = array(
				'key' 	=> __( 'Result' ),
				'value' => $results['result']
			);

			$data[] = array(
				'key' 	=> __( 'Total Files' ),
				'value' => $results['total_files']
			);

			$data[] = array(
				'key' 	=> __( 'Total Checks' ),
				'value' => $results['total_checks']
			);

			$data[] = array(
				'key' 	=> __( 'Total Errors' ),
				'value' => count( $results['errors'] )
			);

			foreach ( $scanner->get_error_levels() as $level ) {
				$label 			= __( ucfirst( $level ) . 's' );
				$error_count 	= count( $scanner->get_errors( array( $level ) ) );

				$data[] = array(
					'key' 	=> $label,
					'value' => $error_count
				);
			}

			WP_CLI\Utils\format_items( $args['format'], $data, array( 'key', 'value' ) );
		} else {
			$data = array();

			foreach ( $scanner->get_error_levels() as $level ) {
				$errors 	= $scanner->get_errors( array( $level ) );

				foreach ( $errors as $error ) {
					$lines = array();

					// Not all errors have lines
					if ( isset( $error['lines'] ) )
						$lines = $error['lines'];

					// In JSON output, group the lines together
					if ( 'json' == $args['format'] ) {
						$data[] = array( 
							'level' 		=> $error['level'],
							'description' 	=> $error['description'],
							'lines' 		=> $lines,
							'file'			=> $error['file']
						);
					} else { // In other output, each line gets its own entry
						foreach ( $lines as $line ) {
							$data[] = array( 
								'level' 		=> $error['level'],
								'description' 	=> $error['description'],
								'lines' 		=> $line,
								'file'			=> $error['file']
							);
						}
					}
				}
			}

			WP_CLI\Utils\format_items( $args['format'], $data, array( 'level', 'description', 'lines', 'file' ) );
		}
	}
}
