<?php

require_once( 'CodeCheckTestBase.php' );

class EscapingTest extends CodeCheckTestBase {

	public function testEscaping() {
		$expected_errors = array(
			array( 'slug' => 'functions-file',
				'level' => BaseScanner::LEVEL_BLOCKER,
				'description' => sprintf(
					__( 'The function %1$s is being passed as the first parameter of %2$s. This is problematic because %1$s echoes a string which will not be escaped by %2$s.', 'vip-scanner' ),
					'<code>printf()</code>',
					'<code>esc_attr()</code>'
				),
				'file' => 'EscapingTest.inc',
				'lines' => 5,
			),
			array( 'slug' => 'functions-file',
					'level' => BaseScanner::LEVEL_BLOCKER,
					'description' => sprintf(
							__( '%1$s is being passed as the first parameter of %2$s.', 'vip-scanner' ),
							'<code>print</code>',
							'<code>esc_attr()</code>'
					),
					'file' => 'EscapingTest.inc',
					'lines' => 6,
			),
			array( 'slug' => 'functions-file',
					'level' => BaseScanner::LEVEL_BLOCKER,
					'description' => sprintf(
							__( '%1$s is being passed as the first parameter of %2$s.', 'vip-scanner' ),
							'<code>echo</code>',
							'<code>esc_attr()</code>'
					),
					'file' => 'EscapingTest.inc',
					'lines' => 7,
			),
			array( 'slug' => 'functions-file',
					'level' => BaseScanner::LEVEL_BLOCKER,
					'description' => sprintf(
							__( 'Please use %1$s to echo internationalized text in html attributes.', 'vip-scanner' ),
							'<code>esc_attr_e()</code>'
					),
					'file' => 'EscapingTest.inc',
					'lines' => 10,
			),
			array( 'slug' => 'functions-file',
					'level' => BaseScanner::LEVEL_BLOCKER,
					'description' => sprintf(
							__( 'Please use %1$s to echo internationalized text in html attributes.', 'vip-scanner' ),
							'<code>esc_attr_e()</code>'
					),
					'file' => 'EscapingTest.inc',
					'lines' => 11,
			),
		);
		$actual_errors = $this->checkFile( 'EscapingTest.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}
}
