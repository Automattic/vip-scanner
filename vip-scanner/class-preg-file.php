<?php

/**
 * Scan a file for a given regex.
 *
 * Merged files should not use this class.
 */
class VIP_PregFile {

	private $line    = 0;
	private $subject = '';
	private $pattern = '';
	private $matches = array();
	private $results = array();

	/**
	 * Create an instance of this object.
	 *
	 * @uses VIP_PregFile::$pattern
	 * @uses VIP_PregFile::$subject
	 * @uses VIP_PregFile::$matches
	 * @uses VIP_PregFile::scan()
	 *
	 * @param string $needle A Perl Compatible Regular Expression.
	 * @param string $haystack A file to search through.
	 * @return void
	 */
	public function __construct( $needle, $haystack ) {
		$this->pattern = $needle;
		$this->subject = $haystack;

		preg_match_all( $this->pattern, $this->subject, $matches );

		if ( empty( $matches[0] ) )
			return;

		$this->matches = $matches[0];
		$this->subject = preg_split( '/$\R?^/m', $this->subject );

		foreach ( $matches[0] as $match ) {
			$length = count( preg_split( '/$\R?^/m', $match ) );
			$this->scan( $length );
		}
	}

	/**
	 * Find the needle in the haystack.
	 *
	 * Iterates through the lines in VIP_PregFile::$subject.
	 * Sets VIP_PregFile::$results
	 *
	 * @uses VIP_PregFile::$subject
	 * @uses VIP_PregFile::$line
	 * @uses VIP_PregFile::$results
	 *
	 * @param int $length Number of lines that the regex match spans.
	 * @return void
	 */
	public function scan( $length ) {
		foreach ( $this->subject as $line ) {
			$subject = array();
			for ( $i = $this->line; $i < ( $this->line + $length ); $i++ ) {
				if ( isset( $this->subject[$i] ) )
					$subject[] = $this->subject[$i];
			}

			$subject = implode( "\n", $subject );

			if ( preg_match( $this->pattern, $subject ) ) {
				$line_number = $this->line + 1;
				$this->results[$line_number] = $subject;
				unset( $this->subject[$this->line] );
				$this->line++;
				break;
			}

			unset( $this->subject[$this->line] );
			$this->line++;
		}
	}

	/**
	 * Get the results of the scan.
	 *
	 * @uses VIP_PregFile::$results
	 *
	 * @return array Matched lines indexed by line number.
	 */
	public function get() {
		return $this->results;
	}
}