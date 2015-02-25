<?php

require_once( 'CheckTestBase.php' );

class EscapingTest extends CheckTestBase {

	public function test_printf_in_esc_attr() {
		$file_contents = <<<'EOT'
			<?php
				esc_attr( printf( 'Test # %d', $n ) );
			?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'functions-file', $error_slugs );
	}

	public function test_print_in_esc_attr() {
		$file_contents = <<<'EOT'
			<?php
				esc_attr( print 'Test' );
			?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'functions-file', $error_slugs );
	}

	public function test_echo_in_esc_attr() {
		$file_contents = <<<'EOT'
			<?php
				esc_attr( echo 'Test' );
			?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'functions-file', $error_slugs );
	}

	public function test__e_in_attributes() {
		$file_contents = <<<'EOT'
			<a href="#" title="<?php _e( 'Doing it wrong' ); ?>">
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'functions-file', $error_slugs );
	}

	// Same as test__e_in_attributes() but w/ single quotes
	public function test__e_in_attributes_with_single_quotes() {
		$file_contents = <<<'EOT'
			<a href="#" title='<?php _e( 'Doing it wrong' ); ?>''>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'functions-file', $error_slugs );
	}

}