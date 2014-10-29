<?php

class VIPParametersCheckTest extends WP_UnitTestCase {
	protected $_VIPParametersCheck;

	public function setUp() {
		parent::setUp();
		require_once VIP_SCANNER_DIR . '/checks/VIPParametersCheck.php';

		$this->_VIPParametersCheck = new VIPParametersCheck();
	}

	public function testUseDeprecatedVIPPlugin() {
		$input = array( 
			'php' => array(
				'test.php' => '<?php

				wpcom_vip_load_plugin( "livefyre" );

				?>
				'
			)
		);

		$result = $this->_VIPParametersCheck->check( $input );

		$errors = $this->_VIPParametersCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );
		$this->assertContains( 'livefyre', $error_slugs );
		$this->assertFalse( $result );
	}

	public function testNoDeprecatedVIPPlugins() {
		$input = array(
			'php' => array(
				'test.php' => '<?php

				wpcom_vip_load_plugin( "livefyre3" );

				?>
				'
			)
		);

		$result = $this->_VIPParametersCheck->check( $input );

		$errors = $this->_VIPParametersCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertNotContains( 'livefyre3', $error_slugs );
		$this->assertTrue( $result );
	}

	public function testNoVIPPluginsLoadCall() {
		$input = array(
			'php' => array(
				'test.php' => '<?php

				add_filter( "wp_title", "twentyfourteen_wp_title", 10, 2 );

				?>
				'
			)
		);

		$result = $this->_VIPParametersCheck->check( $input );

		$errors = $this->_VIPParametersCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertNotContains( 'wp_title', $error_slugs );
		$this->assertTrue( $result );
	}
}
