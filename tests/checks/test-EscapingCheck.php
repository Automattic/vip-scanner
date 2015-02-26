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

	public function test_printing_template_tags_in_escape_functions() {
		$file_contents = <<<'EOT'
			<a href="<?php echo esc_url( the_permalink() ); ?>">
			<a href="<?php print esc_url_raw( the_permalink() ); ?>">
			<h2><?php echo esc_html( the_title() ); ?></h2>
			<script><?php echo esc_js( the_date() ) ); ?></script>
			<textarea><?php echo esc_textarea( the_excerpt() ); ?></textarea>
			<?php echo esc_sql( the_content() ); ?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );
		$expected = array_fill( 0, 6, 'functions-file' ); // One for each instance above

		$this->assertEquals( $expected, $error_slugs );
	}

}