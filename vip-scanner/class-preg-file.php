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
	 * @param string $needle A Perl compatible regular expression.
	 * @param string $haystack A file to search through.
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
			$this->scan( count( preg_split( '/$\R?^/m', $match ) ) );
		}
	}

	public function scan( $length ) {
		$subject = array();
		$scanned = array();
		for ( $i = $this->line; $i < ( $this->line + $length ); $i++ ) {
			if ( isset( $this->subject[$i] ) ) {
				$subject[] = $this->subject[$i];
				$scanned[] = $i;
			}
		}

		$subject = implode( "\n", $subject );

		if ( preg_match( $this->pattern, $subject ) ) {
			$line_number = $this->line + 1;
			$this->results[$line_number] = $subject;
			foreach ( $scanned as $remove ) {
				unset( $this->subject[$remove] );
			}
			return;
		}

		unset( $this->subject[$this->line] );
		$this->line++;

		if ( 0 < count( $this->subject ) )
			$this->scan( $length );

		return;
	}

	public function get() {
		return $this->results;
	}
}