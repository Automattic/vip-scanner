<?php

class TemplateForbiddenFunctionsCheckTest extends WP_UnitTestCase {
	protected $_TemplateForbiddenFunctionsCheck;

	public function setUp() {
		parent::setUp();
		require_once VIP_SCANNER_DIR . '/checks/TemplateForbiddenFunctionsCheck.php';

		$this->_TemplateForbiddenFunctionsCheck = new TemplateForbiddenFunctionsCheck();
	}

	public function testRedirectInHeader() {
		$input = array( 
			'php' => array(
				'header.php' => "<?php

				wp_redirect( 'wordpress.com' );

				?>
				"
			)
		);

		$result = $this->_TemplateForbiddenFunctionsCheck->check( $input );

		$errors = $this->_TemplateForbiddenFunctionsCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );
		$this->assertContains( 'template-forbidden-functions-wp_redirect', $error_slugs );
		$this->assertFalse( $result );
	}

	public function testRedirectInSpecificPageTemplate() {
		$input = array(
			'php' => array(
				'page-about.php' => "<?php

				wp_redirect( 'automattic.com' );

				?>
				"
			)
		);

		$result = $this->_TemplateForbiddenFunctionsCheck->check( $input );

		$errors = $this->_TemplateForbiddenFunctionsCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );
		$this->assertContains( 'template-forbidden-functions-wp_redirect', $error_slugs );
		$this->assertFalse( $result );
	}

	public function testSafeRedirectInPageTemplateWithinSubDirectory() {
		$input = array(
			'php' => array(
				'page-templates/contributors.php' => "<?php
				/*
				Template Name: Contributors
				*/

				wp_safe_redirect( 'automattic.com' );

				?>
				"
			)
		);

		$result = $this->_TemplateForbiddenFunctionsCheck->check( $input );

		$errors = $this->_TemplateForbiddenFunctionsCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );
		$this->assertContains( 'template-forbidden-functions-wp_safe_redirect', $error_slugs );
		$this->assertFalse( $result );
	}

	public function testAllowedRedirectInFunctionsPHPFile() {
		$input = array(
			'php' => array(
				'functions.php' => "<?php

				wp_redirect( 'automattic.com' );

				?>
				"
			)
		);

		$result = $this->_TemplateForbiddenFunctionsCheck->check( $input );

		$errors = $this->_TemplateForbiddenFunctionsCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );
		$this->assertNotContains( 'template-forbidden-functions-wp_redirect', $error_slugs );
		$this->assertTrue( $result );
	}
}
