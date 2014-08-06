<?php

class HeaderTest extends WP_UnitTestCase {
	protected $_HeaderCheck;
	protected static $valid_header_file;

	public static function setUpBeforeClass() {
		self::$valid_header_file = file_get_contents( dirname( __FILE__ ) . '/../data/valid-header.php' );
	}

	public function setUp() {
		parent::setUp();
		require_once VIP_SCANNER_DIR . '/checks/HeaderCheck.php';

		$this->_HeaderCheck = new HeaderCheck();
	}

	public function runCheck( $file_contents ) {
		$input = array( 'php' => array( 'test.php' => $file_contents ) );

		$result = $this->_HeaderCheck->check( $input );
		$errors = $this->_HeaderCheck->get_errors();

		return wp_list_pluck( $errors, 'slug' );
	}

	/**
	 * Test for valid HTML5 doctype.
	 */
	public function testValidTitleTags() {
		$error_slugs = $this->runCheck( self::$valid_header_file );

		$this->assertNotContains( 'header-doctype', $error_slugs );
	}
}
