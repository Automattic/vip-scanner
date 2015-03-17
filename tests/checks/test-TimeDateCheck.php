<?php

require_once( 'CheckTestBase.php' );

class TimeDateTest extends CheckTestBase {

	public function testInvalidTime() {
		$file_contents = <<<'EOT'
		<div class="entry-meta">
			<time class="entry-time"><?php the_time('M'); ?><strong><?php the_time('d'); ?></strong></time>
	    </div><!-- .entry-meta -->
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'hardcoded-date-time', $error_slugs );
	}

	public function testValidTime() {
		$file_contents = <<<'EOT'
			<li>
				<span class="strong"><i class="icon-calendar"></i></span><time><?php echo get_the_date( get_option( 'date-format' ) ); ?></time>
			</li>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertNotContains( 'hardcoded-date-time', $error_slugs );
	}

}