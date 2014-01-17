<?php

class WordPressCodingStandardsCheck extends BaseCheck {
	const COMMAND  = 'phpcs';
	const STANDARD = 'WordPress';
	
	private $output_parts = array(
		'file_header'	=> 'FILE: ',
		'v_splitter'	=> '/^-+$/',
		'h_splitter'	=> '|',
		'output_line'	=> '/\s+\S+\s+|\s+\S+\s+|\s+\S+\s*/',
	);

	protected $exclude_dir_regexes 	= array( '\.svn', '\.git' );
	protected $exclude_file_regexes = array();
	protected $include_extensions   = array( 'php', 'css' );

	protected $output_error_slug = false;

	/*
	 * The error level for individual checks as reported by code sniffer
	 */
	protected $check_level = array(
		'WordPress.Functions.FunctionCallSignature' => BaseScanner::LEVEL_NOTE,
		'WordPress.Arrays.ArrayDeclaration'			=> BaseScanner::LEVEL_NOTE,
		'WordPress.Strings.DoubleQuoteUsage'		=> BaseScanner::LEVEL_WARNING,
	);

	/*
	 * The error level for check classes
	 */
	protected $check_class_level = array(
		'WordPress.WhiteSpace.' => BaseScanner::LEVEL_NOTE,
		'WordPress.XSS.'		=> BaseScanner::LEVEL_BLOCKER,
	);

	/*
	 * Regex for finding the error slug within the error output text
	 */
	private $sniffer_slug_regex = '/\((?P<slug>(\w+\.)+\w+)\)/';

	function check( $files ) {
		// Check for PHP CodeSniffer
		if ( ! self::is_phpcs_available() ) {
			$this->add_error(
					'no_php_code_sniffer',
					'PHP CodeSniffer not available',
					BaseScanner::LEVEL_WARNING,
					null,
					array( 'PHP CodeSniffer (phpcs) is not available on this system. No code sniffing will be performed.' )
			);
			return true;
		}

		// Check that PHP CodeSniffer has the WordPress standard installed
		if ( ! self::is_wordpress_standard_available() ) {
			$this->add_error(
					'no_php_code_sniffer_wordpress_standard',
					'The WordPress standard for PHP CodeSniffer is not installed',
					BaseScanner::LEVEL_WARNING,
					null,
					array( 'The WordPress Coding Standard (' . self::STANDARD . ') for PHP CodeSniffer is not installed. No code sniffing will be performed. Please see https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards for information.' )
			);
			return true;
		}

		$command = escapeshellcmd( self::COMMAND );

		$scan_path = escapeshellarg( $this->get_path() );

		// Format the shell arguments
		$exclude_dir_regex = array_map(
			function( $regex ) { return escapeshellarg( $regex ); },
			$this->get_exclude_dir_regexes()
		);

		$exclude_file_regex	= array_map(
			function( $regex ) { return escapeshellarg( $regex ); },
			$this->get_exclude_file_regexes()
		);

		$ignore_regex = '--ignore=' . implode( ',', array_merge( $exclude_dir_regex, $exclude_file_regex ) );

		$include_extensions = '';
		if ( ! empty( $this->include_extensions ) )
			$include_extensions = '--extensions=' . escapeshellarg( implode( ',', $this->include_extensions ) );

		$command_string = sprintf( '%s -s --standard=%s %s %s %s', $command, escapeshellarg( self::STANDARD ), $ignore_regex, $include_extensions, $scan_path );

		set_time_limit( 120 );

		$result = shell_exec( $command_string );

		if ( ! $result )
			return true;

		$this->output_error_slug = apply_filters( 'vip_scanner_wp_code_standards_check-output_error_slug', $this->output_error_slug );

		$this->parse_results( explode( "\n", $result ) );

		return true;
	}

	/**
	 * Parses the results from phpcs
	 *
	 * @param array $results The array of lines from the output of phpcs
	 */
	private function parse_results( $results ) {
		$current_file = null;
		$current_file_path = null;
		$found_issues = array();
		
		foreach ( $results as $result ) {
			// Check for the file header
			$file_header_pos = strpos( $result, $this->output_parts['file_header'] );
			if ( $file_header_pos !== false ) {
				if ( ! is_null( $current_file ) ) {
					$this->report_issues( $found_issues, $current_file );
					$found_issues = array();
				}

				// File header found, set current file
				$current_file_path = trim( substr( $result, $file_header_pos + strlen( $this->output_parts['file_header'] ) ) );
				$current_file = end( explode( '/', $current_file_path ) );
				$this->increment_check_count();
				continue;
			} else if ( is_null( $current_file ) ) {
				// If we haven't encountered a file header yet we can't parse
				continue;
			}

			// A line of output will contain several pipes (|). Check that this contains that and is not a splitter line.
			if ( preg_match( $this->output_parts['v_splitter'], $result ) || strpos( $result, $this->output_parts['h_splitter'] ) === false )
				continue;

			// Split the output line into at mose three parts and trim each one
			list( $line, $severity, $problem ) = array_map( 'trim', explode( $this->output_parts['h_splitter'], $result, 3 ) );

			// Check if this is a new issue, or more detail on the same issue
			if ( ! empty( $line ) ) {
				// Try and open the file that had the problem
				$issue_file = file_exists( $current_file_path ) ? file( $current_file_path ) : false;
				$issue = array(
					'line'		=> array( $issue_file ? "Line $line: " . trim( $this->get_line( intval( $line ), $issue_file ) ) : "Line $line." ),
					'level'		=> $severity,
					'problem'	=> array( $problem ),
				);

				$found_issues[] = $issue;
			} else {
				// Not a new issue, append problem details
				$found_issues[count( $found_issues ) - 1]['problem'][] = $problem;
			}
		}
		
		return true;
	}

	/**
	 * Adds all the issues encountered in the given file
	 * 
	 * @param array $issues The issues encountered in the given file
	 * @param str $file The file to add issues for
	 */
	private function report_issues( $issues, $file ) {
		foreach ( $issues as $issue ) {
			$problem = implode( ' ', $issue['problem'] );
			$level   = BaseScanner::LEVEL_NOTE;
			preg_match( $this->sniffer_slug_regex, $problem, $matches );
			$issue_slug = trim( $matches['slug'] );

			if ( isset( $this->check_level[$issue_slug] ) ) {
				// This issue has a given level
				$level = $this->check_level[$issue_slug];
			} else {
				// Check for a known issue class
				$found = false;
				foreach ( $this->check_class_level as $class => $class_level ) {
					if ( strpos( $issue_slug, $class ) !== 0 ) continue;

					$found = true;
					$level = $class_level;
					break;
				}

				if ( ! $found && $issue['level'] === 'ERROR' )
					$level = BaseScanner::LEVEL_WARNING;

				// Save the check level for this slug to speed up future searches
				$this->check_level[$issue_slug] = $level;
			}

			if ( ! $this->output_error_slug )
				$problem = preg_replace( $this->sniffer_slug_regex, '', $problem );

			$this->add_error(
					esc_attr( $issue_slug ),
					esc_html( trim( $problem ) ),
					$level,
					esc_attr( $file ),
					array_map( 'esc_html', $issue['line'] )
			);
		}
	}

	/**
	 * Set the file extensions to be scanned
	 *
	 * @param array $regex An array of regexes to exclude
	 *
	 * @return WordPressCodingStandardsCheck The check
	 */
	public function set_include_file_extensions( $extensions = array() ) {
		$this->include_extensions = $extensions;

		return $this;
	}

	/**
	 * Get the file extensions to be scanned
	 *
	 * @return array The extensions to be included
	 */
	public function get_include_file_extensions() {
		return $this->include_extensions;
	}

	/**
	 * Set the regexes for directories to exclude during the scan
	 *
	 * @param array $regex An array of regexes to exclude
	 *
	 * @return WordPressCodingStandardsCheck The check
	 */
	public function set_exclude_dir_regexes( $regexes = array() ) {
		$this->exclude_dir_regexes = $regexes;

		return $this;
	}

	/**
	 * Get the regexes for directories to exclude during the scan
	 *
	 * @return array The regexes to exclude
	 */
	public function get_exclude_dir_regexes() {
		return $this->exclude_dir_regexes;
	}

	/**
	 * Set the regexes for files to exclude during the scan
	 *
	 * @param array $regex An array of regexes to exclude
	 *
	 * @return WordPressCodingStandardsCheck The check
	 */
	public function set_exclude_file_regexes( $regexes = array() ) {
		$this->exclude_file_regexes = $regexes;

		return $this;
	}

	/**
	 * Get the regexes for files to exclude during the scan
	 *
	 * @return array The regexes to exclude
	 */
	public function get_exclude_file_regexes() {
		return $this->exclude_file_regexes;
	}

	public static function is_phpcs_available() {
		$command = escapeshellarg( self::COMMAND );

		$result = shell_exec( "which $command" );

		return ( empty( $result ) ? false : true );
	}

	public static function is_wordpress_standard_available() {
		$command = escapeshellcmd( self::COMMAND );

		$result = shell_exec( "$command -i" );

		return ( stripos( $result, self::STANDARD ) !== false );
	}
}
