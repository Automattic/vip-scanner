<?php

require_once( 'CodeCheckTestBase.php' );

class VIPParametersTest extends CodeCheckTestBase {

	public function testVIPParameters() {
		$expected_errors = array(
			array( 'slug' => 'vip-parameters-livefyre', 'level' => BaseScanner::LEVEL_WARNING, 'description' => 'Deprecated VIP Plugin. Use livefyre3 instead.', 'file' => 'VIPParametersTest.inc', 'lines' => 6 ),
			array( 'slug' => 'vip-parameters-wpcom-related-posts', 'level' => BaseScanner::LEVEL_WARNING, 'description' => 'Deprecated VIP Plugin. Functionality included in Jetpack.', 'file' => 'VIPParametersTest.inc', 'lines' => 9 ),
		);
		$actual_errors = $this->checkFile( 'VIPParametersTest.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}
}
