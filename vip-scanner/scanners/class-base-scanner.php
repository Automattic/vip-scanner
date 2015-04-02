<?php

class BaseScanner {
	const LEVEL_BLOCKER = 'blocker';
	const LEVEL_WARNING = 'warning';
	const LEVEL_NOTE = 'note';

	public $files = array();
	public $analyzed_files = array();
	public $checks = array();
	public $analyzers = array();
	public $total_checks = 0;
	public $errors = array();
	public $elements = array();
	public $stats = array();
	//recognized extensions
	public $known_extensions = array(
		'php' => array( 'php', 'php5', 'inc' ),
		'css' => array( 'css', 'scss', 'sass', 'less' ),
		'js' => array( 'js', 'coffee' ),
		'gif' => 'gif',
		'jpg' => array( 'jpg', 'jpeg' ),
		'png' => 'png',
		'svg' => 'svg',
		'txt' => array( 'txt', 'md', 'markdown', 'text' ),
		'html' => array( 'html', 'htm' ),
		'font' => array( 'woff', 'eot', 'ttf', 'otf' ),
		'i18n' => array( 'pot', 'po', 'mo' ),
		'ico' => array( 'ico' ),
	);
	//these extensions are not allowed and will produce blocking errors
	public $known_bad_extensions = array(
		'gz',
		'zip',
		'tar',
		'orig',
		'rej',
		'bak',
		'log',
		'git',
		'asp',
		'py',
		'cfm',
		'htaccess',
		'swf'
	);
	//these patterns are not allowed anywhere in any filename and will produce blocking errors
	public $known_bad_file_patterns = array(
		'\.php\..*',
		'^\.DS_Store$',
		'^Thumbs.db$',
		'^WS_FTP.*',
	);

	public function __construct( $files, $review ) {
		// Given a set of files & a set of Checks
		// --- Group files by type
		// --- Run Checks against Files
		// --- Return results
		$this->files = $this->group_files( $files );

		if ( isset( $review['checks'] ) ) {
			// Is new API that supports analyzers
			$this->checks 		= $review['checks'];
			$this->analyzers 	= $review['analyzers'];
		} else {
			// Old api, treat $review as list of checks
			$this->checks 		= $review;
			$this->analyzers 	= array();
		}
	}

	// FIXME: We're currently duplicating functionality, as BaseCheck has an
	// add_error() method, too. We should remove either and replace it by a
	// call to the other.
	protected function add_error( $slug, $description, $level, $file = '', $lines = array() ) {
		$error = array(
			'slug' => $slug,
			'level' => $level,
			'description' => $description
		);

		$error['file'] = '';
		if ( ! empty( $file ) )
			$error['file'] = $file;

		if( ! empty( $lines ) ) {
			$lines = array_map( 'htmlspecialchars', (array) $lines );
			$lines = array_map( 'trim', $lines );
			$error['lines'] = $lines;
		}

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

	public function is_bad_file_type( $filename ) {
		return in_array( $this->get_file_type( $filename ), $this->known_bad_extensions );
	}

	public function has_bad_file_pattern( $filename ) {
		foreach ( $this->known_bad_file_patterns as $pattern ) {
			$pattern = '/' . $pattern . '/i';
			if ( 1 === preg_match( $pattern, $filename ) ) {
				return true;
			}
		}
	}

	public function check_filename( $filename, $type ) {
		if ( $this->has_bad_file_pattern( basename( $filename ) ) ) {
			$this->add_error(
				'badfile-error',
				'Found a file with an extension that is not allowed in a theme.',
				BaseScanner::LEVEL_BLOCKER,
				basename( $filename )
			);
			return false;
		}

		if ( $this->is_bad_file_type( $filename ) ) {
			$this->add_error(
				'filetype-error',
				'File type ' . $type . ' not permitted',
				'Blocker',
				basename( $filename )
			);
			return false;
		}

		if ( !$this->is_known_file_type( $filename ) ) {
			$this->add_error(
				'filetype-error',
				'File type ' . $type . ' detected',
				'Warning',
				basename( $filename )
			);
			return false;
		}

		return true;
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

			$this->check_filename( $filename, $file_type);

			if( !isset( $grouped_files[$file_type] ) )
				$grouped_files[$file_type] = array();
			$grouped_files[$file_type][$filename] = $file_contents;
		}
		return $grouped_files;
	}

	public function scan( $scanners = array( 'checks', 'analyzers' ) ) {
		$pass = true;

		if( empty( $this->files ) ) {
			$this->add_error(
				'no-files',
				'No files were found',
				'blocker'
			);
			return false;
		}

		$this->analyzed_files = array();

		$this->analyze_php_files();

		if ( in_array( 'checks', $scanners ) ) {
			$this->run_scanners( 'checks', $pass );
			$this->result = $pass;
		}

		if ( in_array( 'analyzers', $scanners ) ) {
			$this->run_scanners( 'analyzers' );
		}

		return $pass;
	}
	
	protected function analyze_php_files() {
		if ( true === isset( $this->files['php'] ) && true === is_array( $this->files['php'] ) ) {
			foreach ( $this->files['php'] as $filepath => $filecontents ) {
				try {
					$this->analyzed_files[] = new AnalyzedPHPFile( $filepath, $filecontents );
				} catch ( PhpParser\Error $error ) {
					$line_no = $error->getRawLine() - 1;
					$lines = array( BaseCheck::get_line( $line_no, $filecontents ) );
					$this->add_error( 'parse-error', esc_html( $error->getMessage() ), 'blocker', basename( $filepath ), $lines );
				}
			}
		}
	}

	protected function run_scanners( $type, &$pass = null ) {
		if ( 'checks' !== $type && 'analyzers' !== $type ) {
			return;
		}

		if ( 'analyzers' === $type ) {
			if ( true === isset( $this->files['css'] ) && true === is_array( $this->files['css'] ) ) {
				foreach ( $this->files['css'] as $filepath => $filecontents ) {
					$this->analyzed_files[] = new AnalyzedCSSFile( $filepath, $filecontents );
				}
			}
		}
		
		if ( ! is_array( $this->$type ) ) {
			return;
		}

		foreach( $this->$type as $check => $check_file ) {
			if ( is_numeric( $check ) ) { // a bit of a hack, but let's us pass in either associative or indexed or combined array
				$check = $check_file;
				$check_file = '';
			}

			if ( ! apply_filters( 'vip_scanner_run_check', true, $check ) ) {
				$this->add_error( 'skipped-check', sprintf( __( 'The "%s" check was skipped.', 'vip-scanner' ), $check ), BaseScanner::LEVEL_WARNING );
				continue;
			}

			$check_exists = $this->load_check( $check, $check_file, $type );

			if ( ! $check_exists ) {
				$this->add_error( 'invalid-check', sprintf( __( 'Check "%s" does not exist.', 'vip-scanner' ), $check ), 'blocker' );
				continue;
			}

			$check = new $check;

			if ( 'checks' === $type && $check instanceof BaseCheck || $check instanceof CodeCheckVisitor ) {
				if ( $check instanceof CodeCheckVisitor ) {
					// $check is actually a CodeCheckVisitor, so we need to
					// wrap it in a CodeCheck object.
					$check = new CodeCheck( $check );
					$check->set_scanner( $this );
					$pass = $pass & $check->check( $this->analyzed_files );
				} else {
					$check->set_scanner( $this );
					$pass = $pass & $check->check( $this->files );
				}
				$results = $check->get_results();

				if ( ! empty( $results['errors'] ) ) {
					$this->errors = array_merge( $results['errors'], $this->errors );
				}

				$this->total_checks += $results['count'];
			} elseif ( 'analyzers' === $type && $check instanceof BaseAnalyzer ) {
				$check->set_scanner( $this );
				$check->analyze( $this->analyzed_files );
				$this->elements = array_merge( $check->get_elements(), $this->elements );
				$this->stats = array_merge( $check->get_stats(), $this->stats );
			}
		}
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

	/**
	 * Determine if the Scanner has flagged a given error
	 * 
	 * @param  string  $slug The error slug to check for
	 * @return boolean       Boolean indicating if the Scanner flagged the error
	 */
	public function has_error( $slug ) {
		foreach( $this->get_errors() as $error ) {
			if ( $slug == $error['slug'] )
				return true;
		}

		return false;
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

	private function load_check( $check, $file = '', $type = 'checks' ) {

		if( ! class_exists( $check ) ) {
			if ( 'checks' === $type ) {
				$basepath = VIP_SCANNER_CHECKS_DIR;
			} elseif ( 'analyzers' === $type) {
				$basepath = VIP_SCANNER_ANALYZERS_DIR;
			}
			
			$path =  ! empty( $file ) ? $file : sprintf( '%1$s/%2$s.php', $basepath, $check );
			if ( file_exists( $path ) )
				include( $path );
		}

		return class_exists( $check );
	}
}
