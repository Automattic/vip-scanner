<?php

require_once( 'CodeCheckTestBase.php' );

class DeprecatedConstantsTest extends CodeCheckTestBase {

	public function testDeprecatedConstants() {
		$description_template = 'The constant %1$s is deprecated. Use %2$s instead.';
		$line = 4;
		$expected_errors = array(
			array( 'slug' => 'deprecated', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, '<code>STYLESHEETPATH</code>', '<code>get_stylesheet_directory()</code>' ), 'file' => 'DeprecatedConstantsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'deprecated', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, '<code>TEMPLATEPATH</code>', '<code>get_template_directory()</code>' ),     'file' => 'DeprecatedConstantsTest.inc', 'lines' => ++$line ),
		);
		$actual_errors = $this->checkFile( 'DeprecatedConstantsTest.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}
}