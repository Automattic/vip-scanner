<?php

require_once( 'CodeCheckTestBase.php' );

class CustomizerTest extends CodeCheckTestBase {

	public function testCustomizerCheck() {
		$expected_errors = array(
			array( 'slug' => 'customizer', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => 'Found a Customizer setting that had an empty value passed as sanitization callback. You need to pass a function name as sanitization callback.', 'file' => 'CustomizerTest.inc', 'lines' => array( 23, 28 ) ),
			array( 'slug' => 'customizer', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => 'Found a Customizer setting that did not have a sanitization callback function. Every call to the <code>add_setting()</code> method needs to have a sanitization callback function passed.', 'file' => 'CustomizerTest.inc', 'lines' => 33 ),
			array( 'slug' => 'customizer', 'level' => BaseScanner::LEVEL_WARNING, 'description' => 'The theme uses the <code>WP_Customize_Image_Control</code> class. Custom logo options should be implemented using the <a href="http://en.support.wordpress.com/site-logo/">Site Logo</a> feature.', 'file' => 'CustomizerTest.inc', 'lines' => 38 ),
			array( 'slug' => 'customizer', 'level' => BaseScanner::LEVEL_WARNING, 'description' => 'The theme creates a new Customizer control by extending <code>WP_Customize_Control</code>.', 'file' => 'CustomizerTest.inc', 'lines' => 41 ),
		);
		$actual_errors = $this->checkFile( 'CustomizerTest.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}
}
