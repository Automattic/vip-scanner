<?php

class ForbiddenVariablesTest extends WP_UnitTestCase {
	protected $_ForbiddenVariablesCheck;

	public function setUp() {
		parent::setUp();
		require_once VIP_SCANNER_DIR . '/checks/ForbiddenVariablesCheck.php';

		$this->_ForbiddenVariablesCheck = new ForbiddenVariablesCheck();
	}

	public function testAssigningForbiddenVariable() {
		$input = array( 
			'php' => array(
				'test.php' => '<?php

				$normal = "Hey";

				$_REQUEST = "teste";

				?>
				'
			)
		);

		$result = $this->_ForbiddenVariablesCheck->check( $input );

		$errors = $this->_ForbiddenVariablesCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertContains( 'forbidden-variable', $error_slugs );
		$this->assertFalse( $result );
	}

	public function testAssignedAllowedVariables() {
		$input = array( 
			'php' => array(
				'test.php' => '<?php

				$bbq = "is so tasty";

				?>
				'
			)
		);

		$result = $this->_ForbiddenVariablesCheck->check( $input );

		$errors = $this->_ForbiddenVariablesCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertNotContains( 'forbidden-variable', $error_slugs );
		$this->assertTrue( $result );
	} 

	public function testReadingForbiddenVariables() {
		$input = array( 
			'php' => array(
				'test.php' => '<?php

				if( $_REQUEST["lol"] == "hey" )
					echo "how are you?";

				?>
				'
			)
		);

		$result = $this->_ForbiddenVariablesCheck->check( $input );

		$errors = $this->_ForbiddenVariablesCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertContains( 'forbidden-variable', $error_slugs );
		$this->assertFalse( $result );
	}


	public function testReadingAllowedVariables() {
		$input = array( 
			'php' => array(
				'test.php' => '<?php

				if( $imnormal == "hey" )
					echo "how are you?";

				?>
				'
			)
		);

		$result = $this->_ForbiddenVariablesCheck->check( $input );

		$errors = $this->_ForbiddenVariablesCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertNotContains( 'forbidden-variable', $error_slugs );
		$this->assertTrue( $result );
	}

}
