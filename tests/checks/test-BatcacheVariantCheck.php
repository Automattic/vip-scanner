<?php

require_once( 'CodeCheckTestBase.php' );

class BatcacheVariantTest extends CodeCheckTestBase {

	public function testBatcacheVariant() {
		$expected_errors = array(
			array(
				'slug' => 'batcache-variant-error',
				'level' => BaseScanner::LEVEL_BLOCKER,
				'description' => 'Illegal word in variant determiner.',
				'file' => 'BatcacheVariantTest.inc',
				'lines' => 7,
			),
			array(
				'slug' => 'batcache-variant-error',
				'level' => BaseScanner::LEVEL_BLOCKER,
				'description' => 'Variant determiner should refer to at least one $_ variable.',
				'file' => 'BatcacheVariantTest.inc',
				'lines' => 7,
			),
		);
		$actual_errors = $this->checkFile( 'BatcacheVariantTest.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}

}
