<?php

class VIPInitCheckTest extends WP_UnitTestCase {
	protected $_VIPInitCheckTest;
	protected $_functions_file;

	public function setUp() {
		parent::setUp();
		require_once VIP_SCANNER_DIR . '/checks/VIPInitCheck.php';

		$this->_VIPInitCheckTest = new VIPInitCheck();
		$this->_functions_file = dirname(__FILE__) . '/../data/functions.php';
	}

	public function testIsMainFunctions() {
		$this->_VIPInitCheckTest->set_scanner( new DirectoryScanner( dirname( $this->_functions_file ), "VIP Theme Review" ) );
		$this->assertTrue( $this->_VIPInitCheckTest->file_is_main_functions( $this->_functions_file ) );
	}

	public function testCheckRequire() {
		$this->assertTrue( $this->_VIPInitCheckTest->vip_init_is_included( $this->_functions_file ) );
	}

}