<?php

require_once( 'CheckTestBase.php' );

abstract class CodeCheckTestBase extends CheckTestBase {

	/**
	 * Given a filename, run a check on it and return errors found by the check
	 * @param string $filename
	 */
	protected function checkFile( $filename ) {
		$file = $this->loadFile( $filename );
		$this->assertFalse( $this->check->check( $file ) );
		return $this->check->get_errors();
	}

	/**
	 * Return the contents of a file, pre-processed for running a check on them
	 * @param string $filename The name of the file within the ../data/ dir
	 * @return array
	 */
	protected function loadFile( $filename ) {
		return array( 'php' => array( file_get_contents( dirname( __FILE__ ) . '/../data/' . $filename ) ) );
	}

	/**
	 * Assert that a list of actual errors matches the expected values
	 * @param array $expected_errors
	 * @param array $actual_errors
	 */
	protected function assertEqualErrors( $expected_errors, $actual_errors ) {
		$this->assertEquals( count( $expected_errors ), count( $actual_errors ) );
		$error = current( $actual_errors );
		foreach( $expected_errors as $expected ) {
			$this->individualCheck( $expected, $error );
			$error = next( $actual_errors );
		}
	}

	/**
	 * Check if an error generated by the check matches the expected values
	 *
	 * The 'slug' and 'level' properties are always compared. The
	 * 'description', 'file', and 'lines' properties are only compared if
	 * present in the actual error. This is to allow legacy checks to pass
	 * tests and should be tightened once they report filenames, lines, etc.
	 *
	 * @param array $expected The expected error
	 * @param array $error The actual error
	 */
	protected function individualCheck( $expected, $error ) {
		$message = "Failed asserting that the actual error's %s matches the expected error's.";

		$this->assertEquals( $error['slug'], $expected['slug'], sprintf( $message, 'slug' ) );
		$this->assertEquals( $error['level'], $expected['level'], sprintf( $message, 'level' ) );

		if ( ! empty( $error['description'] ) ) {
			$this->assertEquals( $error['description'], $expected['description'], sprintf( $message, 'description' ) );
		}

		if ( ! empty( $error['file'] ) ) {
			$this->assertEquals( $error['file'], $expected['file'] );
		}

		// Check if line numbers match
		if ( ! empty( $error['lines'] ) ) {
			if ( ! is_array( $expected['lines'] ) ) {
				$expected['lines'] = array( $expected['lines'] );
			}
			$this->assertEquals( $expected['lines'], array_keys( $error['lines'] ), sprintf( $message, 'lines' ) );
		}
	}
}