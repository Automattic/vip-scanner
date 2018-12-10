<?php

require_once( 'CodeCheckTestBase.php' );

class BatcacheVariantTest extends CodeCheckTestBase {

	public function testBatcacheVariant() {
		$actual_errors = $this->checkFile( 'BatcacheVariantTest1.inc' );
		$error_slug = wp_list_pluck( $actual_errors, 'slug' );
		$this->assertContains( 'batcache-variant-error', $error_slug );
	}

	public function testBatcacheVariantPositive() {
		$actual_errors = $this->checkFile( 'BatcacheVariantTest2.inc' );
		$error_slug = wp_list_pluck( $actual_errors, 'slug' );
		$this->assertNotContains( 'batcache-variant-error', $error_slug );
	}

}
