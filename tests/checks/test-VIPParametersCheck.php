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

	public function testNoTargetFunctionCall() {
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

	public function testCorrectErrorLevel() {
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

		$error_slugs = wp_list_pluck( $errors, 'level' );

		$this->assertContains( 'warning', $error_slugs );
		$this->assertFalse( $result );
	}

	public function testTargetParameterInSecondPosition() {
		$file_path     = 'test.php';
		$file_contents = '<?php

				wpcom_vip_load_plugin( "dummy", "livefyre" );

				?>
				';

		$checks = array(
			'wpcom_vip_load_plugin' => array(
				array(
					'value'    => 'livefyre',
					'position' => 1,
					'level'    => 'warning',
					'note'     => 'Deprecated VIP Plugin. Use livefyre3 instead.'
				),
			),
		);

		$result = $this->_VIPParametersCheck->check_file_contents( $checks, $file_path, $file_contents );

		$errors = $this->_VIPParametersCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertContains( 'livefyre', $error_slugs );
		$this->assertFalse( $result );
	}

	public function testTargetParameterPresentInWrongPosition() {
		$file_path     = 'test.php';
		$file_contents = '<?php

				wpcom_vip_load_plugin( "livefyre", "dummy" );

				?>
				';

		$checks = array(
			'wpcom_vip_load_plugin' => array(
				array(
					'value'    => 'livefyre',
					'position' => 1,
					'level'    => 'warning',
					'note'     => 'Deprecated VIP Plugin. Use livefyre3 instead.'
				),
			),
		);

		$result = $this->_VIPParametersCheck->check_file_contents( $checks, $file_path, $file_contents );

		$errors = $this->_VIPParametersCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertNotContains( 'livefyre', $error_slugs );
		$this->assertTrue( $result );
	}

	public function testTargetParameterAnyPosition() {
		$file_path     = 'test.php';
		$file_contents = '<?php

				wpcom_vip_load_plugin( "first", "second", "livefyre" );

				?>
				';

		$checks = array(
			'wpcom_vip_load_plugin' => array(
				array(
					'value'    => 'livefyre',
					'level'    => 'warning',
					'note'     => 'Deprecated VIP Plugin. Use livefyre3 instead.'
				),
			),
		);

		$result = $this->_VIPParametersCheck->check_file_contents( $checks, $file_path, $file_contents );

		$errors = $this->_VIPParametersCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertContains( 'livefyre', $error_slugs );
		$this->assertFalse( $result );
	}

	public function testNumericParameter() {
		$file_path     = 'test.php';
		$file_contents = '<?php

				wpcom_vip_load_plugin( 5 );

				?>
				';

		$checks = array(
			'wpcom_vip_load_plugin' => array(
				array(
					'value'    => 5,
					'position' => 0,
					'level'    => 'warning',
					'note'     => 'Numeric Parameter Found.'
				),
			),
		);

		$result = $this->_VIPParametersCheck->check_file_contents( $checks, $file_path, $file_contents );

		$errors = $this->_VIPParametersCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertContains( 5, $error_slugs );
		$this->assertFalse( $result );
	}

	public function testBooleanParameter() {
		$file_path     = 'test.php';
		$file_contents = '<?php

				wpcom_vip_load_plugin( false );

				?>
				';

		$checks = array(
			'wpcom_vip_load_plugin' => array(
				array(
					'value'    => "false",
					'position' => 0,
					'level'    => 'warning',
					'note'     => 'Numeric Parameter Found.'
				),
			),
		);

		$result = $this->_VIPParametersCheck->check_file_contents( $checks, $file_path, $file_contents );

		$errors = $this->_VIPParametersCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertContains( 'false', $error_slugs );
		$this->assertFalse( $result );
	}
}
