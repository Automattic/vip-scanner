<?php

require_once( 'CodeCheckTestBase.php' );

class QueryTest extends CodeCheckTestBase {
	public function testQueryPostsUsage() {
		$file_contents = <<<'EOT'
		<?php
		query_posts( 'showposts=25' );

		while ( have_posts() ) : the_post();
		?>
			<li class="clear">
				<a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
				<span class="archives-date"><?php the_time( get_option( 'date_format' ) ); ?></span>
			</li>
		<?php
		endwhile;
		wp_reset_query();
		?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'query_query_posts', $error_slugs );
	}

}
