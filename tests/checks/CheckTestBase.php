<?php

abstract class CheckTestBase extends WP_UnitTestCase {
	protected $check;

	public function setUp() {
		parent::setUp();

		$check_class = substr( get_class( $this ), 0, - strlen( 'Test' ) ) . 'Check';
		require_once VIP_SCANNER_DIR . "/checks/$check_class.php";

		$this->check = new $check_class();
		if ( $this->check instanceof CodeCheckVisitor ) {
			$this->check = new CodeCheck( new $check_class() );
		} else {
			$this->check = new $check_class();
		}
	}

	public function runCheck( $file_contents ) {
		$input = array( 'php' => array( 'test.php' => $file_contents ) );

		if ( $this->check instanceof CodeCheckVisitor ) {
			$input = array( new AnalyzedPhpFile( 'test.php', $file_contents ) );
		}

		$result = $this->check->check( $input );

		$errors = $this->check->get_errors();

		return wp_list_pluck( $errors, 'slug' );
	}
}
