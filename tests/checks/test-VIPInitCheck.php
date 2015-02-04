<?php

class VIPInitCheckTest extends WP_UnitTestCase {
	protected $_VIPInitCheckTest;
	protected $_functions_file;

	public function setUp() {
		parent::setUp();
		require_once VIP_SCANNER_DIR . '/checks/VIPInitCheck.php';

		$this->_VIPInitCheckTest = new VIPInitCheck();
		$this->_functions_file = dirname(__FILE__) . '/../data/test-theme/functions.php';
	}

	public function testCheckRequire() {
		$this->assertTrue( $this->_VIPInitCheckTest->vip_init_is_included( $this->_functions_file ) );
	}

	public function testStatementRequire() {
		$this->assertTrue( $this->_VIPInitCheckTest->vip_init_is_included( dirname(__FILE__) . '/../data/test-theme2/functions.php' ) );
	}

	public function testScanner() {
		$vipsccanner = VIP_Scanner::get_instance();
		$vipsccanner->register_review( 'VIPInitCheck', array(
				'VIPInitCheck'
			), array(
				'ThemeAnalyzer'
			) );
		$review = $vipsccanner->get_review( 'VIPInitCheck' );
		$scanner = new ThemeScanner( dirname( $this->_functions_file ), $review );
		$scanner->scan( array('checks') );

		$this->assertFalse( $scanner->has_error( 'vip-init' ) );
	}

}