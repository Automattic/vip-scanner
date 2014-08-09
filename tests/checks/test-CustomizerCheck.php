<?php

class CustomizerTest extends WP_UnitTestCase {
	protected $_CustomizerCheck;

	public function setUp() {
		require_once VIP_SCANNER_DIR . '/checks/CustomizerCheck.php';

		$this->_CustomizerCheck = new CustomizerCheck();
	}

	public function runCheck( $file_contents ) {
		$input = array( 'php' => array( 'test.php' => $file_contents ) );

		$result = $this->_CustomizerCheck->check( $input );

		$errors = $this->_CustomizerCheck->get_errors();

		return wp_list_pluck( $errors, 'slug' );
	}

	public function testValidSanitizeCallback() {
		$file = <<<'EOT'
<?php
$wp_customize->add_setting( 'setting', array(
	'sanitize_callback' => 'sanitize_text_field',
) );

$wp_customize->add_setting( 'setting', array(
	'default'           => array( 'id' => 0, 'url' => '' ),
	'sanitize_callback' => 'sanitize_text_field',
) );
EOT;

		$error_slugs = $this->runCheck( $file );

		$this->assertNotContains( 'customizer', $error_slugs );
	}

	public function testClassMethodSanitizeCallback() {
		$file = <<<'EOT'
<?php
$wp_customize->add_setting( 'setting', array(
	'sanitize_callback' => array( $this, 'sanitize_method' ),
) );
EOT;

		$error_slugs = $this->runCheck( $file );

		$this->assertNotContains( 'customizer', $error_slugs );
	}

	public function testEmptySanitizeCallback() {
		$file = <<<'EOT'
<?php
$wp_customize->add_setting( 'setting', array(
	'sanitize_callback' => '',
) );

$wp_customize->add_setting( 'setting', array(
	'sanitize_callback' => '   ',
) );
EOT;

		$error_slugs = $this->runCheck( $file );

		$this->assertContains( 'customizer', $error_slugs );
		$this->assertEquals( 2, count( $error_slugs ) );
	}

	public function testMissingSanitizeCallback() {
		$file = <<<'EOT'
<?php
$wp_customize->add_setting( 'setting', array(
	'default'   => '',
) );
EOT;

		$error_slugs = $this->runCheck( $file );

		$this->assertContains( 'customizer', $error_slugs );
	}
}
