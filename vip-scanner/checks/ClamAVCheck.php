<?php

class ClamAVCheck extends BaseCheck {
	const COMMAND = 'clamscan';

	protected $exclude_dir_regexes 	= array( '\.svn', '\.git' );
	protected $exclude_file_regexes = array();

	public function check( $files ) {
		// If the scan is not a theme scan, skip (for example, diff scan)
		$scanner = $this->get_scanner();

		if ( ! $scanner instanceof ThemeScanner )
			return true;
		
		// We can only actually do a ClamAV scan if it's installed :)
		if ( ! self::isClamScanAvailable() ) {
			$this->add_error( 'clamav', 'Antivirus Scan', BaseScanner::LEVEL_WARNING, null, array( 'ClamAV is not present on this system - as such, no antivirus scanning was performed on this theme.') );

			return true;
		}

		$command = escapeshellcmd( self::COMMAND );

		$scan_path = escapeshellarg( $this->get_path() );

		// Format the shell arguments
		$exclude_dir_regex 	= array_map( function( $regex ) { 
			return '--exclude-dir=' . escapeshellarg( $regex ); 
		}, $this->get_exclude_dir_regexes() );

		$exclude_file_regex 	= array_map( function( $regex ) { 
			return '--exclude=' . escapeshellarg( $regex ); 
		}, $this->get_exclude_file_regexes() );

		$exclude_dir_regex 		= implode( ' ', $exclude_dir_regex );
		$exclude_file_regiex 	= implode( ' ', $exclude_file_regex );

		$command_string = sprintf( '%s -r -i %s %s %s', $command, $exclude_dir_regex, $exclude_file_regex, $scan_path );

		$result = shell_exec( $command_string );

		if ( ! $result )
			return true;

		$results = explode( "\n", $result );

		foreach( $results as $result ) {
			// if we're in the results section, skip
			if ( false !== strpos( $result, '-- SCAN SUMMARY --' ) )
				break;

			if ( false === strpos( $result, 'FOUND' ) )
				continue;

			list( $file, $virus, $result ) = explode( ' ', $result );

			$file = $this->get_filename( trim( $file, ':' ) );

			$this->add_error( 'clamav', 'Antivirus Scan', BaseScanner::LEVEL_BLOCKER, $file, array( $virus . ' found' ) );
		}

		return true;
	}

	/**
	 * Set the regexes for directories to exclude during the scan
	 * 
	 * @param array $regex An array of regexes to exclude
	 *
	 * @return ClamAVCheck The check
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
	 * @return ClamAVCheck The check
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

	public static function isClamScanAvailable() {
		$command = escapeshellarg( self::COMMAND );

		$result = shell_exec( "which $command" );

		return ( empty( $result ) ? false : true );
	}
}
