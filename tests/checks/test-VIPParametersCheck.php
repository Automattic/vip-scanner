<?php

require_once( 'CheckTestBase.php' );

class VIPParametersTest extends CheckTestBase {

	public function testUseDeprecatedVIPPlugin() {
		$file_contents = <<<'EOT'
				<?php

				wpcom_vip_load_plugin( "livefyre" );

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'vip-parameters-livefyre', $error_slugs );
	}

	public function testNoDeprecatedVIPPlugins() {
		$file_contents = <<<'EOT'
				<?php

				wpcom_vip_load_plugin( "livefyre3" );

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertNotContains( 'vip-parameters-livefyre3', $error_slugs );
	}

	public function testNoTargetFunctionCall() {
		$file_contents = <<<'EOT'
				<?php

				add_filter( "wp_title", "twentyfourteen_wp_title", 10, 2 );

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertNotContains( 'vip-parameters-wp_title', $error_slugs );
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

		$result = $this->check->check( $input );

		$errors = $this->check->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'level' );

		$this->assertContains( 'warning', $error_slugs );
		$this->assertFalse( $result );
	}

	public function testTargetParameterInSecondPosition() {
		$file_path     = 'test.php';
		$file_contents = <<<'EOT'
				<?php

				wpcom_vip_load_plugin( "dummy", "livefyre" );

				?>
EOT;

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

		$result = $this->check->check_file_contents( $checks, $file_path, $file_contents );

		$errors = $this->check->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertContains( 'vip-parameters-livefyre', $error_slugs );
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

		$result = $this->check->check_file_contents( $checks, $file_path, $file_contents );

		$errors = $this->check->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertNotContains( 'vip-parameters-livefyre', $error_slugs );
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

		$result = $this->check->check_file_contents( $checks, $file_path, $file_contents );

		$errors = $this->check->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertContains( 'vip-parameters-livefyre', $error_slugs );
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

		$result = $this->check->check_file_contents( $checks, $file_path, $file_contents );

		$errors = $this->check->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertContains( 'vip-parameters-5', $error_slugs );
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

		$result = $this->check->check_file_contents( $checks, $file_path, $file_contents );

		$errors = $this->check->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertContains( 'vip-parameters-false', $error_slugs );
		$this->assertFalse( $result );
	}
}
