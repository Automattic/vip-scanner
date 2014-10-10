<?php

class BaseScannerTest extends WP_UnitTestCase {
	protected $_BaseScanner;

	public function setUp() {
		parent::setUp();
		require_once VIP_SCANNER_DIR . '/class-base-scanner.php';

		$files = array(
			'/mytheme/adcentric/ifr_b.html'
		);
		$this->_BaseScanner = new BaseScanner( $files, array() );
	}

	public function test_known_adbusters() {
		$file = '/mytheme/adcentric/ifr_b.html';
		$this->assertTrue( $this->_BaseScanner->is_adbuster( $file ) );
	}

	public function test_non_adbuster() {
		$file = '/mytheme/this_is_not_adbuster/david.html';
		$this->assertFalse( $this->_BaseScanner->is_adbuster( $file ) );
	}
}