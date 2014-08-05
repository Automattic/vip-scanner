<?php

class HeaderTest extends WP_UnitTestCase {
	protected $_HeaderCheck;

	public function setUp() {
		parent::setUp();
		require_once VIP_SCANNER_DIR . '/checks/HeaderCheck.php';

		$this->_HeaderCheck = new HeaderCheck();
	}

	public function testValidTitle() {
		$input = array(
			'php' => array(
				'test.php' => "<title><?php wp_title( '|', true, 'right' ); ?></title>"
			)
		);

		$result = $this->_HeaderCheck->check( $input );
		$errors = $this->_HeaderCheck->get_errors();
		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertNotContains( 'header-title', $error_slugs );
	}

	public function testInvalidTitle() {
		$input = array(
			'php' => array(
				'test.php' => "<title>
<?php wp_title(' '); ?>
<?php if(wp_title(' ', false)) { echo '|'; } ?>
<?php bloginfo('name'); ?>
</title>"
			)
		);

		$result = $this->_HeaderCheck->check( $input );
		$errors = $this->_HeaderCheck->get_errors();
		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertContains( 'header-title', $error_slugs );
	}
}
