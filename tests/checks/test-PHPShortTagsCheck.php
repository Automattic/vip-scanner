<?php

class PHPShortTagsTest extends WP_UnitTestCase {
	protected $_PHPShortTagsCheck;

	public function setUp() {
		parent::setUp();
		require_once VIP_SCANNER_DIR . '/checks/PHPShortTagsCheck.php';

		$this->_PHPShortTagsCheck = new PHPShortTagsCheck();
	}

	public function runCheck( $file_contents ) {
		$input = array( 'php' => array( 'test.php' => $file_contents ) );

		$result = $this->_PHPShortTagsCheck->check( $input );
		$errors = $this->_PHPShortTagsCheck->get_errors();

		return wp_list_pluck( $errors, 'slug' );
	}

	public function testValidTags() {
		$file_contents = <<<'EOT'
				<?php

				echo "doing things";

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertNotContains( 'php-shorttags', $error_slugs );
	}

	public function testOpeningShortTags() {
		$file_contents = <<<'EOT'
				<?

				echo "doing things";
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'php-shorttags', $error_slugs );
	}

	public function testOutputShortTags() {
		$file_contents = <<<'EOT'
					<?php $foo = "bar"; ?>

					<?=$foo; ?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'php-shorttags', $error_slugs );
	}
}
