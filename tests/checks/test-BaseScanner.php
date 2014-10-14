<?php

class BaseScannerTest extends WP_UnitTestCase {
	protected $_BaseScanner;

	public function setUp() {
		parent::setUp();
		require_once VIP_SCANNER_DIR . '/class-base-scanner.php';

		$this->resetBaseScanner();
	}

	private function resetBaseScanner( $files = null ) {
		if ( null === $files ) {
			$files = array(
				'index.php' => '<?php echo $ahoj;'
			);
		}
		$this->_BaseScanner = new BaseScanner( $files, array() );
	}

	public function test_check_filename_for_swf_file() {
		$filename = '/file.swf';
		$this->assertFalse( $this->_BaseScanner->check_filename( $filename, $this->_BaseScanner->get_file_type( $filename ) ) );
	}


}