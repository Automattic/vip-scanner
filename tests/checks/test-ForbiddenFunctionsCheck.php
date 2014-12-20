<?php

require_once( 'CodeCheckTestBase.php' );

class ForbiddenFunctionsTest extends CodeCheckTestBase {

	public function testForbiddenFunctions() {
		$description_template = 'The function <code>%s</code> was found in the theme. Themes cannot use this function, please remove it.';
		$line = 4;
		$expected_errors = array(
			array( 'slug' => 'forbidden-function', 'level' => 'blocker', 'description' => sprintf( $description_template, 'register_post_type' ), 'file' => 'ForbiddenFunctionsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'forbidden-function', 'level' => 'blocker', 'description' => sprintf( $description_template, 'register_taxonomy' ),  'file' => 'ForbiddenFunctionsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'forbidden-function', 'level' => 'blocker', 'description' => sprintf( $description_template, 'add_shortcode' ),      'file' => 'ForbiddenFunctionsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'forbidden-function', 'level' => 'blocker', 'description' => sprintf( $description_template, 'add_meta_box' ),       'file' => 'ForbiddenFunctionsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'forbidden-function', 'level' => 'blocker', 'description' => sprintf( $description_template, 'add_help_tab' ),       'file' => 'ForbiddenFunctionsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'forbidden-function', 'level' => 'blocker', 'description' => sprintf( $description_template, 'query_posts' ),        'file' => 'ForbiddenFunctionsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'forbidden-function', 'level' => 'blocker', 'description' => sprintf( $description_template, 'get_children' ),       'file' => 'ForbiddenFunctionsTest.inc', 'lines' => ++$line ),
		);
		$actual_errors = $this->checkFile( 'ForbiddenFunctionsTest.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}
}