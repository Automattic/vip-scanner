<?php

require_once( 'CodeCheckTestBase.php' );

class VIPEscapingTest extends CodeCheckTestBase {

	public function testEscaping() {
		$lines = 3;

		$expected_errors = array();

	
		/*
		 * Why 4 * 5? Why a the loop?
		 * Because we are specifying very similar
		 * test-result, again and again.
		 *
		 * First, four tests for echo (without brackets)
		 * 	echo __( ... );
		 * 	echo _x( ... );
		 *	echo _n( ... );
		 * 	echo _nx( ... );
		 *
		 * Second, four similar tests for echo with brackets
		 * Third, four similar tests for print without brackets
 		 * Fourth, four similar tests for print with brackets
		 * Fifth, four simila tests for vprintf (with brackets)
		 *
		 * Note: If you find yourself adding 'ifs' within the
		 * for-loop, stop using a loop and write out each
		 * expected error individually.
		 */

		for ($i = 0; $i < 4 * 5; $i++) {
			$expected_errors[] = array(
				'slug' => 'functions-file',
				'level' => BaseScanner::LEVEL_BLOCKER,
				'description' => sprintf(
					esc_html__( 'Printing output of non-escaping localization functions (i.e. %1$s) is potentially dangerous, as they do not escape HTML. An escaping function (e.g. %2$s) should be used rather.', 'vip-scanner' ),
					'<code>__( ), _x( ), _n( ), _nx( )</code>',
					'<code>esc_html__( ), esc_attr__( ), esc_html_x( ), esc_attr_x( )</code>'
				),
				'file' => 'VIPEscapingTest.inc',
				'lines' => $lines++,
			);
		}

		$lines++;


		/*
		 * Now test for non-printing usage of __( ), _x( ),
		 * _n( ), and _nx( ).
		 *
		 * Note: If you find yourself adding 'ifs' within the
		 * for-loop, stop using a loop and write out each
		 * expected error individually.
		 */

		for ($i = 0; $i < 4; $i++) {
			$expected_errors[] = array(
				'slug' => 'functions-file',
				'level' => BaseScanner::LEVEL_WARNING,
				'description' => sprintf(
					esc_html__( 'Usage of non-escaping localization functions (i.e. %1$s) is discouraged as they do not escape HTML. An escaping function (e.g. %2$s) should be used rather.', 'vip-scanner' ),
					'<code>__( ), _x( ), _n( ), _nx( )</code>',
					'<code>esc_html__( ), esc_attr__( ), esc_html_x( ), esc_attr_x( )</code>'
				),
				'file' => 'VIPEscapingTest.inc',
				'lines' => $lines++,
			);
		}


		/*
		 * Now test for usage of _e( ), _ex( ).
		 *
		 * Note: If you find yourself adding 'ifs' within the
		 * for-loop, stop using a loop and write out each
		 * expected error individually.
		 */

		for ($i = 0; $i < 2; $i++) {
			$expected_errors[] = array(
				'slug' => 'functions-file',
				'level' => BaseScanner::LEVEL_BLOCKER,
				'description' => sprintf(
					esc_html__( 'Usage of non-escaping localization functions (i.e. %1$s) is discouraged as they do not escape HTML. An escaping function (e.g. %2$s) should be used rather.', 'vip-scanner' ),
					'<code>_e( ), _ex( )</code>',
					'<code>esc_html_e( ), esc_attr_e( ), esc_html_x( ), esc_attr_x( ), esc_attr__( )</code>'
				),
				'file' => 'VIPEscapingTest.inc',
				'lines' => $lines++,
			);
		}


		$actual_errors = $this->checkFile( 'VIPEscapingTest.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}
}
