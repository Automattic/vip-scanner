<?php

class VIPRestrictedPatternsTest extends WP_UnitTestCase {
	protected $_VIPRestrictedPatternsCheck;
	protected static $BOM_test_file;

	public static function setUpBeforeClass() {
		self::$BOM_test_file = file_get_contents( dirname( __FILE__ ) . '/../data/test-bom.php' );
	}

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

	public function testByteOrderMark() {
		$input = array(
			'php' => array(
				'test.php' => self::$BOM_test_file
			)
		);

		$result = $this->_VIPRestrictedPatternsCheck->check( $input );

		$errors = $this->_VIPRestrictedPatternsCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertContains( '/(\xFE|\xFF|\xFE\xFF|\xFF\xFE|\xEF\xBB\xBF|\x2B\x2F\x76|\xF7\x64\x4C|\x0E\xFE\xFF|\xFB\xEE\x28|\x00\x00\xFE\xFF|\xDD\x73\x66\x73|\x84\x31\x95\x33)/', $error_slugs );

		$this->assertFalse( $result );
	}

	public function testNoByteOrderMark() {
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

		$this->assertNotContains( '/(\xFE|\xFF|\xFE\xFF|\xFF\xFE|\xEF\xBB\xBF|\x2B\x2F\x76|\xF7\x64\x4C|\x0E\xFE\xFF|\xFB\xEE\x28|\x00\x00\xFE\xFF|\xDD\x73\x66\x73|\x84\x31\x95\x33)/', $error_slugs );
		$this->assertTrue( $result );
	}
}
