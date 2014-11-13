<?php

require_once( 'CheckTestBase.php' );

class PHPShortTagsTest extends CheckTestBase {

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
