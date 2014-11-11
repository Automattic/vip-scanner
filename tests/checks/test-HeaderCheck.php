<?php

require_once( 'CheckTestBase.php' );

class HeaderTest extends CheckTestBase {
	protected static $valid_header_file;

	public static function setUpBeforeClass() {
		self::$valid_header_file = file_get_contents( dirname( __FILE__ ) . '/../data/valid-header.php' );
	}

	/**
	 * Test for valid HTML5 doctype.
	 */
	public function testValidTitleTags() {
		$error_slugs = $this->runCheck( self::$valid_header_file );

		$this->assertNotContains( 'header-doctype', $error_slugs );
	}
}
