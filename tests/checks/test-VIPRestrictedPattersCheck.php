<?php

class VIPRestrictedPatternsTest extends WP_UnitTestCase {
	protected $_VIPRestrictedPatternsCheck;

	public function setUp() {
		parent::setUp();
		require_once VIP_SCANNER_DIR . '/checks/VIPRestrictedPatternsCheck.php';

		$this->_VIPRestrictedPatternsCheck = new VIPRestrictedPatternsCheck();
	}

	public function testAssigningREQUESTVariable() {
		$input = array( 
			'php' => array(
				'test.php' => '<?php

				$normal = "Hey";

				$_REQUEST["lol"] = "dont do this";

				?>
				'
			)
		);

		$result = $this->_VIPRestrictedPatternsCheck->check( $input );

		$errors = $this->_VIPRestrictedPatternsCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );
		$this->assertContains( '/(\$_REQUEST)+/msiU', $error_slugs );
		$this->assertFalse( $result );
	}

	public function testAssignedNormalVariables() {
		$input = array( 
			'php' => array(
				'test.php' => '<?php

				$bbq = "is so tasty";

				?>
				'
			)
		);

		$result = $this->_VIPRestrictedPatternsCheck->check( $input );

		$errors = $this->_VIPRestrictedPatternsCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertNotContains( '/(\$_REQUEST)+/msiU', $error_slugs );
		$this->assertTrue( $result );
	} 

	public function testReadingREQUESTVariables() {
		$input = array( 
			'php' => array(
				'test.php' => '<?php

				if( $_REQUEST["lol"] == "hey" )
					echo "how are you?";

				?>
				'
			)
		);

		$result = $this->_VIPRestrictedPatternsCheck->check( $input );

		$errors = $this->_VIPRestrictedPatternsCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertContains( '/(\$_REQUEST)+/msiU', $error_slugs );
		$this->assertFalse( $result );
	}


	public function testReadingNormalVariables() {
		$input = array( 
			'php' => array(
				'test.php' => '<?php

				if( $imnormal == "hey" )
					echo "how are you?";

				?>
				'
			)
		);

		$result = $this->_VIPRestrictedPatternsCheck->check( $input );

		$errors = $this->_VIPRestrictedPatternsCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertNotContains( '/(\$_REQUEST)+/msiU', $error_slugs );
		$this->assertTrue( $result );
	}

	public function testQueryVarsDirectAccess() {
		$input = array(
			'php' => array(
				'test.php' => '<?php

				add_action( "init", function(){
					global $wp_query;
					$paged = $wp_query->query_vars["paged"];
				} );
				'
			)
		);

		$result = $this->_VIPRestrictedPatternsCheck->check( $input );
		
		$this->assertFalse( $result );
	}

}
