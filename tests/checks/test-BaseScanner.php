<?php

class BaseScannerTest extends WP_UnitTestCase {
	protected $_BaseScanner;

	public function setUp() {
		parent::setUp();
		require_once VIP_SCANNER_SCANNERS_DIR . '/class-base-scanner.php';

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

	public function test_get_file_type() {
		$filenames = array (
			'swf' => '/test-theme/file.swf',
			'php' => '/test-theme/files/index.php',
			'html' => '/test-theme/file.html',
		);
		foreach( $filenames as $type => $filename ) {
			$this->assertEquals( $type, $this->_BaseScanner->get_file_type( $filename ) );
		}
	}

	public function test_get_html_file_type() {
		//.htm should be translated to html file type
		$filename = '/test-theme/file.htm';
		$this->assertEquals( 'html', $this->_BaseScanner->get_file_type( $filename ) );
	}

	public function test_check_filename_for_swf_file() {
		$filename = '/file.swf';
		$this->assertFalse( $this->_BaseScanner->check_filename( $filename, $this->_BaseScanner->get_file_type( $filename ) ) );
	}

	public function test_catch_swf_file() {
		$filename = 'file.swf';
		$files = array(
			$filename => 'somecontent'
		);
		$this->resetBaseScanner( $files );
		$this->_BaseScanner->scan();

		$this->assertTrue( $this->_BaseScanner->has_error( 'filetype-error' ) );

		$error_levels = $this->_BaseScanner->get_error_levels();
		$this->assertEqualSets( array( 'blocker' ),$error_levels );

		$errors = $this->_BaseScanner->get_errors();
		$this->assertEquals( 'Blocker', $errors[0]['level'] );

		$this->assertEquals( $filename, $errors[0]['file'] );
	}
}