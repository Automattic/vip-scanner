<?php

class BaseScanner {
	const LEVEL_BLOCKER = 'blocker';
	const LEVEL_WARNING = 'warning';
	const LEVEL_NOTE = 'note';

	public $files = array();
	public $checks = array();
	public $total_checks = 0;
	public $errors = array();
	public $known_extensions = array(
		'php' => array( 'php', 'php5', 'inc' ),
		'css' => 'css',
		'js' => 'js',
	);


	public function __construct( $files, $checks ) {
		// Given a set of files & a set of Checks
		// --- Group files by type
		// --- Run Checks against Files
		// --- Return results
		$this->files = $this->group_files( $files );
		$this->checks = $checks;
	}

	protected function add_error( $slug, $description, $level, $file = '', $lines = array() ) {
		$error = array(
			'slug' => $slug,
			'level' => $level,
			'description' => $description
		);

		if( ! empty( $file ) )
			$error['file'] = $file;
		if( ! empty( $lines ) )
			$error['lines'] = $lines;

		$this->errors[] = $error;
	}

	public function get_file_type( $filename ) {
		
		$splosion = explode( '.', $filename );

		$file_extension = array_pop( $splosion );

		foreach( $this->known_extensions as $type => $extensions ) {
			if( is_array( $extensions ) && in_array( $file_extension, $extensions ) )
				return $type;
			if( $file_extension == $extensions )
				return $type;
		}
		return $file_extension;
	}

	public function is_known_file_type( $filename ) {
		return in_array( $this->get_file_type( $filename ), array_keys( $this->known_extensions ) );
	}

	public function get_file_count() {
		$count = 0;
		foreach( $this->files as $files_by_filetype ) {
			foreach ( $files_by_filetype as $filename => $file ) {
				$count++;
			}
		}
		return $count;
	}

	public function group_files( $files ) {

		$grouped_files = array();

		foreach( $files as $filename => $file_contents ) {
			$file_type = $this->get_file_type( $filename );

			// If we only want to scan files of a certain type
			//if ( ! $this->is_known_file_type( $filename ) )
			//	continue;

			if( !isset( $grouped_files[$file_type] ) )
				$grouped_files[$file_type] = array();
			$grouped_files[$file_type][$filename] = $file_contents;
		}
		return $grouped_files;
	}

	public function scan() {
		$pass = true;

		if( empty( $this->files ) ) {
			$this->add_error(
				'no-files',
				'No files were found',
				'blocker'
			);
			return false;
		}

		foreach( $this->checks as $check => $check_file ) {
			if ( is_numeric( $check ) ) { // a bit of a hack, but let's us pass in either associative or indexed or combined array
				$check = $check_file;
				$check_file = '';
			}
			$check_exists = $this->load_check( $check, $check_file );

			if ( ! $check_exists ) {
				$this->add_error( 'invalid-check', sprintf( __( 'Check "%s" does not exist.', 'vip-scanner' ), $check ), 'blocker' );
				continue;
			}

			$check = new $check;
			if ( $check instanceof BaseCheck ) {
				$check->set_scanner( $this );

				$pass = $pass & $check->check( $this->files );
				$results = $check->get_results();

				if ( ! empty( $results['errors'] ) ) {
					$this->errors = array_merge( $results['errors'], $this->errors );
				}

				$this->total_checks += $results['count'];
			}
		}
		$this->result = $pass;
		return $pass;
	}

	public function get_results() {
		return array(
			'result' => $this->result,
			'total_files' => $this->get_file_count(),
			'total_checks' => $this->total_checks,
			'errors' => $this->errors
		);
	}

	public function get_errors( $levels = array() ) {
		if( empty( $levels ) )
			return $this->errors;

		$levels = (array) $levels;
		$errors = array();

		for( $i = 0; $i < count( $this->errors ); $i++ ) {
			$error = $this->errors[$i];
			if( isset( $error['level'] ) && in_array( strtolower( $error['level'] ), $levels ) )
				$errors[] = $error;
		}

		return $errors;
	}

	public function get_error_levels() {
		$levels = array();

		foreach ( $this->errors as $error ) {
			if ( isset( $error['level'] ) && ! in_array( strtolower( $error['level'] ), $levels ) ) {
				$levels[] = strtolower( $error['level'] );
			}
		}

		return $levels;
	}

	private function load_check( $check, $file = '' ) {

		if( ! class_exists( $check ) ) {
			$path =  ! empty( $file ) ? $file : sprintf( '%1$s/%2$s.php', VIP_SCANNER_CHECKS_DIR, $check );
			if ( file_exists( $path ) )
				include( $path );
		}

		return class_exists( $check );
	}
}
