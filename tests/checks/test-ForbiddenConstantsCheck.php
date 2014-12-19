<?php

require_once( 'CodeCheckTestBase.php' );

class ForbiddenConstantsTest extends CodeCheckTestBase {

	public function testForbiddenConstants() {
		$description_template = 'Themes cannot use the constant <code>%s</code>.';
		$line = 4;
		$expected_errors = array(
			array( 'slug' => 'forbidden', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, 'PLUGINDIR' ),       'file' => 'ForbiddenConstantsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'forbidden', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, 'WP_PLUGIN_DIR' ),   'file' => 'ForbiddenConstantsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'forbidden', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, 'WPMU_PLUGIN_DIR' ), 'file' => 'ForbiddenConstantsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'forbidden', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, 'MUPLUGINDIR' ),     'file' => 'ForbiddenConstantsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'forbidden', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, 'IS_WPCOM' ),        'file' => 'ForbiddenConstantsTest.inc', 'lines' => ++$line ),
		);
		$actual_errors = $this->checkFile( 'ForbiddenConstantsTest.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}
}