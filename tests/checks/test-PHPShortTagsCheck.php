<?php

class PHPShortTagsTest extends WP_UnitTestCase {
	protected $_PHPShortTagsCheck;

	public function setUp() {
		parent::setUp();
		require_once VIP_SCANNER_DIR . '/checks/PHPShortTagsCheck.php';

		$this->_PHPShortTagsCheck = new PHPShortTagsCheck();
	}

	public function testValidTags() {
		$input = array( 
			'php' => array(
				'test.php' => '<?php

				echo "doing things";

				?>
				'
			)
		);

		$result = $this->_PHPShortTagsCheck->check( $input );

		$errors = $this->_PHPShortTagsCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertNotContains( 'php-shorttags', $error_slugs );
	}

	public function testOpeningShortTags() {
		$input = array( 
			'php' => array(
				'test.php' => '<?

				echo "doing things";
				'
			)
		);

		$result = $this->_PHPShortTagsCheck->check( $input );

		$errors = $this->_PHPShortTagsCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertContains( 'php-shorttags', $error_slugs );
	}

	public function testOutputShortTags() {
		$input = array( 
			'php' => array(
				'test.php' => '
					$foo = "bar";

					<?=$foo; ?>
				'
			)
		);

		$result = $this->_PHPShortTagsCheck->check( $input );

		$errors = $this->_PHPShortTagsCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		$this->assertContains( 'php-shorttags', $error_slugs );
	}
}
