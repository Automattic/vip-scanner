<?php

require_once( 'CodeCheckTestBase.php' );

class ForbiddenPHPFunctionsTest extends CodeCheckTestBase {

	public function testForbiddenPHPFunctions() {
		$description_template = 'The PHP function <code>%s()</code> was found. Themes cannot use this function.';
		$line = 4;
		$expected_errors = array(
			array( 'slug' => 'forbidden-php', 'level' => 'blocker', 'description' => sprintf( $description_template, 'eval' ),            'file' => 'ForbiddenPHPFunctionsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'forbidden-php', 'level' => 'blocker', 'description' => sprintf( $description_template, 'popen' ),           'file' => 'ForbiddenPHPFunctionsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'forbidden-php', 'level' => 'blocker', 'description' => sprintf( $description_template, 'proc_open' ),       'file' => 'ForbiddenPHPFunctionsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'forbidden-php', 'level' => 'blocker', 'description' => sprintf( $description_template, 'exec' ),            'file' => 'ForbiddenPHPFunctionsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'forbidden-php', 'level' => 'blocker', 'description' => sprintf( $description_template, 'shell_exec' ),      'file' => 'ForbiddenPHPFunctionsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'forbidden-php', 'level' => 'blocker', 'description' => sprintf( $description_template, 'system' ),          'file' => 'ForbiddenPHPFunctionsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'forbidden-php', 'level' => 'blocker', 'description' => sprintf( $description_template, 'passthru' ),        'file' => 'ForbiddenPHPFunctionsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'forbidden-php', 'level' => 'blocker', 'description' => sprintf( $description_template, 'base64_decode' ),   'file' => 'ForbiddenPHPFunctionsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'forbidden-php', 'level' => 'blocker', 'description' => sprintf( $description_template, 'base64_encode' ),   'file' => 'ForbiddenPHPFunctionsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'forbidden-php', 'level' => 'blocker', 'description' => sprintf( $description_template, 'uudecode' ),        'file' => 'ForbiddenPHPFunctionsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'forbidden-php', 'level' => 'blocker', 'description' => sprintf( $description_template, 'str_rot13' ),       'file' => 'ForbiddenPHPFunctionsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'forbidden-php', 'level' => 'blocker', 'description' => sprintf( $description_template, 'ini_set' ),         'file' => 'ForbiddenPHPFunctionsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'forbidden-php', 'level' => 'blocker', 'description' => sprintf( $description_template, 'create_function' ), 'file' => 'ForbiddenPHPFunctionsTest.inc', 'lines' => ++$line ),
			array( 'slug' => 'forbidden-php', 'level' => 'blocker', 'description' => sprintf( $description_template, 'extract' ),         'file' => 'ForbiddenPHPFunctionsTest.inc', 'lines' => ++$line ),
		);
		$actual_errors = $this->checkFile( 'ForbiddenPHPFunctionsTest.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}
}