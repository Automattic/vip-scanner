<?php

require_once( 'CodeCheckTestBase.php' );

class CustomizerTest extends CodeCheckTestBase {

	public function testCustomizerCheck() {
		$expected_errors = array(
			array( 'slug' => 'customizer', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => 'Found a Customizer setting that had an empty value passed as sanitization callback. You need to pass a function name as sanitization callback.', 'file' => 'CustomizerTest.inc', 'lines' => array( 23, 28 ) ),
			array( 'slug' => 'customizer', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => 'Found a Customizer setting that did not have a sanitization callback function. Every call to the <code>add_setting()</code> method needs to have a sanitization callback function passed.', 'file' => 'CustomizerTest.inc', 'lines' => 33 ),
		);
		$actual_errors = $this->checkFile( 'CustomizerTest.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}
}
