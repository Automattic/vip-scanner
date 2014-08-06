<?php

class YUICompressorTest extends WP_UnitTestCase {
	protected $_YUICompressorCheck;

	public function setUp() {
		parent::setUp();
		require_once VIP_SCANNER_DIR . '/checks/YUICompressorCheck.php';

		$this->_YUICompressorCheck = new YUICompressorCheck();
		// We should force the Check to use a ThemeScanner
		$this->_YUICompressorCheck->set_scanner( new ThemeScanner("Tests", array() ) );
	}

	public function testJavaScriptSyntaxError() {
		$input = array( 
			'js' => array(
					'/tmp/test.js' => 'funtion test() { 
alert("This is a test");
}',
				),

		);

		//Temporary create the files
		foreach( $input as $files ) {
			foreach ( $files as $file_name => $file_content ) {
				$file = fopen( $file_name, 'w');
				fwrite($file, $file_content);
				fclose($file);
			}
		}

		$result = $this->_YUICompressorCheck->check( $input );
		$this->assertFalse( $result );
		
		$errors = $this->_YUICompressorCheck->get_errors();
		$error_slugs = wp_list_pluck( $errors, 'slug' );
		$this->assertContains( 'yuicompressor', $error_slugs );

	}

	public function testJavaScriptCorrectSyntax() {
		$input = array( 
			'js' => array(
					'/tmp/test.js' => 'function test() { 
alert("This is a test");
}',
				),

		);

		//Temporary create the files
		foreach( $input as $files ) {
			foreach ( $files as $file_name => $file_content ) {
				$file = fopen( $file_name, 'w');
				fwrite($file, $file_content);
				fclose($file);
			}
		}

		$result = $this->_YUICompressorCheck->check( $input );
		$this->assertTrue( $result );
		
		$errors = $this->_YUICompressorCheck->get_errors();
		$error_slugs = wp_list_pluck( $errors, 'slug' );
		$this->assertNotContains( 'yuicompressor', $error_slugs );

	}

}
