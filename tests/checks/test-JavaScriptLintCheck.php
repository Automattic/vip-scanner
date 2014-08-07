<?php

class JavaScriptLintTest extends WP_UnitTestCase {
	protected $_JavaScriptLintCheck;

	public function setUp() {
		parent::setUp();
		require_once VIP_SCANNER_DIR . '/checks/JavaScriptLintCheck.php';

		$this->_JavaScriptLintCheck = new JavaScriptLintCheck();
		// We should force the Check to use a ThemeScanner
		$this->_JavaScriptLintCheck->set_scanner( new ThemeScanner("Tests", array() ) );
	}

	public function testJavaScriptSyntaxError() {
		$input = array( 
			'js' => array(
					'tests/data/javascript-syntax-error.js' => '',
				),

		);

		foreach($input['js'] as $filename => $content ) {
			$this->assertFileExists( $filename );
		}

		$result = $this->_JavaScriptLintCheck->check( $input );
		$this->assertFalse( $result );
		
		$errors = $this->_JavaScriptLintCheck->get_errors();
		$error_slugs = wp_list_pluck( $errors, 'slug' );
		$this->assertContains( 'yuicompressor', $error_slugs );

	}

	public function testJavaScriptCorrectSyntax() {
		$input = array( 
			'js' => array(
					'tests/data/javascript-syntax-valid.js' => '',
				),

		);

		foreach($input['js'] as $filename => $content ) {
			$this->assertFileExists( $filename );
		}

		$result = $this->_JavaScriptLintCheck->check( $input );
		$this->assertTrue( $result );
		
		$errors = $this->_JavaScriptLintCheck->get_errors();
		$error_slugs = wp_list_pluck( $errors, 'slug' );
		$this->assertNotContains( 'yuicompressor', $error_slugs );

	}

}
