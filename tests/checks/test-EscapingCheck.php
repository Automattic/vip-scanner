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

	public function test_the_permalink_in_esc_url() {
		$file_contents = <<<'EOT'
			<a href="<?php echo esc_url( the_permalink() ); ?>">
EOT;
		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'functions-file', $error_slugs );
	}

	public function test_the_permalink_in_esc_url_raw() {
		$file_contents = <<<'EOT'
			<a href="<?php print esc_url_raw( the_permalink() ); ?>">
EOT;
		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'functions-file', $error_slugs );
	}

	public function test_the_title_in_esc_html() {
		$file_contents = <<<'EOT'
			<h2><?php echo esc_html( the_title() ); ?></h2>
EOT;
		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'functions-file', $error_slugs );
	}

	public function test_the_date_in_esc_js() {
		$file_contents = <<<'EOT'
			<script><?php echo esc_js( the_date() ) ); ?></script>
EOT;
		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'functions-file', $error_slugs );
	}

		public function test_the_excerpt_in_esc_textarea() {
		$file_contents = <<<'EOT'
			<textarea><?php echo esc_textarea( the_excerpt() ); ?></textarea>
EOT;
		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'functions-file', $error_slugs );
	}

	public function test_the_content_in_esc_sql() {
		$file_contents = <<<'EOT'
			<?php echo esc_sql( the_content() ); ?>
EOT;
		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'functions-file', $error_slugs );
	}

}