<?php

require_once( 'CodeCheckTestBase.php' );

class VIPRestrictedClassesTest extends CodeCheckTestBase {

	public function testVIPRestrictedClasses() {
		$expected_errors = array(
			array( 'slug' => 'WP_User_Query', 'level' => 'Note', 'description' => 'Use of WP_User_Query', 'file' => 'VIPRestrictedClassesTest.inc', 'lines' => 5 ),
		);
		$actual_errors = $this->checkFile( 'VIPRestrictedClassesTest.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}
}