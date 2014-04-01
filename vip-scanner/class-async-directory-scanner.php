<?php

class AsyncDirectoryScanner extends DirectoryScanner {
	const ASYNC_SCAN_CPT = 'async-scan-result';

	protected $directory;
	protected $scanned_files = array();

	function __construct( $directory, $review ) {
		if( ! function_exists( 'get_theme_root' ) )
			return $this->add_error( 'wp-load', sprintf( '%s requires WordPress to be loaded.', get_class( $this ) ), 'blocker' );

		$this->directory = $this->get_normalized_dir_path( $directory );

		// Call Parent Constructor
		parent::__construct( $this->directory, $review );
	}

	function get_files_to_scan() {
		// Get a list of all files in the directory
		$files = $this->get_files_in_directory( $this->directory );

		// Get a list of files that have already been scanned
		$scanned_files = $this->get_scanned_files( $this->directory );

		// Get the difference in the list of files
		$scanned_dates = array();
		foreach ( $files as $file ) {
			if ( array_key_exists( $file, $scanned_files ) ) {
				// This file has already been scanned. Record that date and time
				$scanned_dates[$file] = $scanned_files[$file]['scan_timestamp'];
			} else {
				// This file has never been scanned. Record last scan as 0.
				$scanned_dates[$file] = 0;
			}
		}

		// Sort the dates
		asort( $scanned_dates );

		// Get the least recently scanned file
		$file = key( $scanned_dates );

		// Scan that file
		return array( $file => file_get_contents( $file ) );
	}

	function get_scanned_files( $directory = '.' ) {
		$directory = $this->get_normalized_dir_path( $directory );

		if ( isset( $this->scanned_files[$directory] ) ) {
			return $this->scanned_files[$directory];
		}

		$query_args = array(
			'post_type'		 => self::ASYNC_SCAN_CPT,
			'posts_per_page' => 0,
			'meta_key'		 => 'vip_scanner_dir',
			'meta_value'	 => $directory,
		);

		$files = array();

		foreach ( get_posts( $query_args ) as $post ) {
			$files[$post->post_title] = array(
				'post_id'		 => $post->ID,
				'scan_timestamp' => strtotime( $post->post_modified ),
			);
		}

		$this->scanned_files[$directory] = $files;

		return $files;
	}

	function get_normalized_dir_path( $directory = '.' ) {
		return rtrim( realpath( $directory ), '/' );
	}

	function scan( $scanners = array( 'checks', 'analyzers' ) ) {
		// Run the scan
		$scan_pass = parent::scan( $scanners );

		// Get the results
		$results = $this->get_results();

		// Get the previously scanned files
		$scanned_files = $this->get_scanned_files( $this->directory );

		$directory = $this->get_normalized_dir_path( $this->directory );

		// Save the results
		foreach ( $this->files as $file_group_type => $files ) {
			foreach ( $files as $file => $file_contents ) {
				// Get the post id to update
				if ( array_key_exists( $file, $scanned_files ) ) {
					$post_id = $scanned_files[$file]['post_id'];
				} else {
					// This file does not already have a db entry, insert one
					$post_id = wp_insert_post( array(
						'post_type'  => self::ASYNC_SCAN_CPT,
						'post_title' => $file,
					) );

					if ( is_wp_error( $post_id ) ) {
						continue;
					}

					update_post_meta( $post_id, 'vip_scanner_dir', $directory );
				}

				// Save the scan results for this file
				update_post_meta( $post_id, 'vip_scanner_async_results', $results );
				update_post_meta( $post_id, 'vip_scanner_async_pass', $scan_pass );
			}
		}

		return $scan_pass;
	}
}