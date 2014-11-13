<?php

require_once( 'CheckTestBase.php' );

class TitleTest extends CheckTestBase {

	/**
	 * Test for the presence of <title> and </title> tags.
	 */
	public function testValidTitleTags() {
		$file_contents = <<<'EOT'
<!DOCTYPE html><html <?php language_attributes(); ?>><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head();?></head>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'title-no-title-tags', $error_slugs );
	}

	/**
	 * Test for the presence of a call to wp_title().
	 */
	public function testCallToTitleFunction() {
		$file_contents = '<title>Untitled Document</title>';

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'title-no-wp_title', $error_slugs );
	}

	/**
	 * Test the contents of the <title> tags.
	 */
	public function testValidTitle() {
		$file_contents = "<title><?php wp_title( '|', true, 'right' ); ?></title>";

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertNotContains( 'header-title-contents', $error_slugs );
	}

	public function testInvalidTitle() {
		$file_contents = <<<'EOT'
<title>
<?php wp_title(' '); ?>
<?php if(wp_title(' ', false)) { echo '|'; } ?>
<?php bloginfo('name'); ?>
</title>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'header-title-contents', $error_slugs );
	}
}
