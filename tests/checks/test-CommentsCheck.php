<?php

require_once( 'CheckTestBase.php' );

class CommentsTest extends CheckTestBase {
	public function testCommentFormDefaultsFiltering() {
		$file_contents = <<<'EOT'
			function theme_slug_comment_form( $args ) {
				$args[ 'comment_notes_after' ] = '';
				return $args;
			}
			add_filter( 'comment_form_defaults', 'theme_slug_comment_form' );
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'filtering_comment_form_defaults', $error_slugs );
	}

}