<?php

require_once( 'CheckTestBase.php' );

class JavaScriptLintTest extends CheckTestBase {

	public function setUp() {
		parent::setUp();

		// We should force the Check to use a ThemeScanner
		$this->check->set_scanner( new ThemeScanner("Tests", array() ) );
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

		$result = $this->check->check( $input );
		$this->assertFalse( $result );
		
		$errors = $this->check->get_errors();
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

		$result = $this->check->check( $input );
		$this->assertTrue( $result );
		
		$errors = $this->check->get_errors();
		$error_slugs = wp_list_pluck( $errors, 'slug' );
		$this->assertNotContains( 'yuicompressor', $error_slugs );

	}

}
