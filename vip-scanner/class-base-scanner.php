<?php

class BaseScanner {
	const LEVEL_BLOCKER = 'blocker';
	const LEVEL_WARNING = 'warning';
	const LEVEL_NOTE = 'note';

	public $files = array();
	public $checks = array();
	public $analyzers = array();
	public $total_checks = 0;
	public $errors = array();
	public $renderers = array();
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

	public function get_adbusters_array() {

		if ( function_exists( 'wpcom_vip_load_plugin' ) ) {
			wpcom_vip_load_plugin( 'Adbusters' );
			if ( function_exists( 'wpcom_vip_get_ad_busters_array' ) ) {
				return wpcom_vip_get_ad_busters_array();
			}
		}

		return array(
			'adcentric/ifr_b.html',              // AdCentric
			'adinterax/adx-iframe-v2.html',      // AdInterax
			'atlas/atlas_rm.htm',                // Atlas
			'blogads/iframebuster-4.html',       // BlogAds
			'checkm8/CM8IframeBuster.html',      // CheckM8
			'comscore/cs-arIframe.htm',          // comScore
			'doubleclick/DARTIframe.html',       // Google - DoubleClick
			'doubleclick/fif.html',              // Flite
			'eyeblaster/addineyeV2.html',        // MediaMind - EyeBlaster
			'eyewonder/interim.html',            // EyeWonder
			'flashtalking/ftlocal.html',         // Flashtalking
			'flite/fif.html',                    // Flite
			'gumgum/iframe_buster.html',         // gumgum
			'interpolls/pub_interpolls.html',    // Interpolls
			'jivox/jivoxIBuster.html',           // Jivox
			'jpd/jpxdm.html',                    // Jetpack Digital
			'mediamind/MMbuster.html',           // MediaMind - addineye (?)
			'mixpo/framebust.html',              // Mixpo
			'oggifinogi/oggiPlayerLoader.htm',   // Collective - OggiFinogi
			'pictela/Pictela_iframeproxy.html',  // AOL - Pictela
			'pointroll/PointRollAds.htm',        // PointRoll
			'rubicon/rp-smartfile.html',         // Rubicon
			'saymedia/iframebuster.html',        // Say Media
			'smartadserver/iframeout.html',      // SmartAdserver
			'undertone/iframe-buster.html',      // Intercept Interactive - Undertone
			'undertone/UT_iframe_buster.html',   // Intercept Interactive - Undertone
			'xaxis/InfinityIframe.html',         // Xaxis
			'_uac/adpage.html',                  // AOL - atwola.com
			'adcom/aceFIF.html',                 // Advertising.com (ad.com)
		);
	}


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

	public function is_adbuster( $file ) {

		//parse file path to get filename
		$filename = basename( $file );

		//grab adbusters array
		$adbusters = $this->get_adbusters_array();

		//grab filenames only
		$adbusters = array_map( 'basename', $adbusters );

		//compare!
		if ( true === in_array( $filename, $adbusters ) ) {
			return true;
		}

		return false;
	}

	public function maybe_adbuster( $file ) {

		//checkout the file extension - we are looking for htm and html files only
		$path_parts = pathinfo( $file );
		$suspicious_extensions = array(
			'html',
			'htm'
		);
		$extension = mb_strtolower( $path_parts['extension'] );
		if ( false === in_array( $extension, $suspicious_extensions ) ) {
			return false;
		}

		//first - check on the file size, frame busters are usually small files
		if ( filesize( $file ) > 1024 ) {
			return false;
		}

		//"buster" in name is highly suspicious - let's flag such file
		if ( false !== mb_strpos( mb_strtolower( $path_parts['basename'] ), 'buster' ) ) {
			return true;
		}

		//ok, so the file is relatively small and it is a static HTML file - that's suspicious, let's do some more tests
		return $this->possible_adbuster_body_check( $file );
	}

	public function possible_adbuster_body_check( $file ) {
		$dom = new DOMDocument();
		$dom->loadHTML( '<?xml encoding="UTF-8">' . file_get_contents( $file ) );
		$scripts = $dom->getElementsByTagName('script');
		//such iframebuster has to have a script tag, at least one
		if ( 0 !== $scripts->length ) {
			//examine body - body without content or body containing script nodes only is suspicious
			$body = $dom->getElementsByTagName( 'body' );
			if ( 0 !== $body->length ) {
				//empty body - flag it!
				if ( '' === trim( $body->item(0)->nodeValue, " \n\r\t\0\xC2\xA0") ) {
					return true;
				}
				//todo: the empty body check above is not catching files with only script nodes in the body
			}
			//static HTML without styles is suspicious as well, flag it
			$styles = $dom->getElementsByTagName('style');
			if( 0 === $styles->length ) {
				return true;
			}
		}
		//looks good
		return false;
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

		if ( $this->is_adbuster( $filename ) ) {
			$this->add_error(
				'adbuster-error',
				'Found a file which is an ad frame buster. Please use <a href="https://github.com/Automattic/Adbusters">Adbusters plugin</a> instead.',
				BaseScanner::LEVEL_BLOCKER,
				basename( $filename )
			);
			return false;
		}

		if ( $this->maybe_adbuster( $filename ) ) {
			$this->add_error(
				'adbuster-error',
				'Found a file which may be an ad frame buster. Please use <a href="https://github.com/Automattic/Adbusters">Adbusters plugin</a> instead.',
				BaseScanner::LEVEL_WARNING,
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

		if ( in_array( 'checks', $scanners ) ) {
			$this->run_scanners( 'checks', $pass );
			$this->result = $pass;
		}

		if ( in_array( 'analyzers', $scanners ) ) {
			$this->run_scanners( 'analyzers' );
		}

		return $pass;
	}
	
	protected function run_scanners( $type, &$pass = null ) {
		if ( 'checks' !== $type && 'analyzers' !== $type ) {
			return;
		}
		
		if ( 'analyzers' === $type ) {
			$analyzed_files = array();

			if ( is_array( $this->files['php'] ) ) {
				foreach ( $this->files['php'] as $filepath => $filecontents ) {
					$analyzed_files[] = new AnalyzedPHPFile( $filepath, $filecontents );
				}
			}
			
			if ( is_array( $this->files['css'] ) ) {
				foreach ( $this->files['css'] as $filepath => $filecontents ) {
					$analyzed_files[] = new AnalyzedCSSFile( $filepath, $filecontents );
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
			if ( 'checks' === $type && $check instanceof BaseCheck ) {
				$check->set_scanner( $this );

				$pass = $pass & $check->check( $this->files );
				$results = $check->get_results();

				if ( ! empty( $results['errors'] ) ) {
					$this->errors = array_merge( $results['errors'], $this->errors );
				}

				$this->total_checks += $results['count'];
			} elseif ( 'analyzers' === $type && $check instanceof BaseAnalyzer ) {
				$check->set_scanner( $this );
				$check->analyze( $analyzed_files );
				$this->renderers = array_merge( $check->get_renderers(), $this->renderers );
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
