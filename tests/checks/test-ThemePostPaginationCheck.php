<?php

require_once( 'CheckTestBase.php' );

class ThemePostPaginationTest extends CheckTestBase {
    
	/**
	 * Test that no pagination gets flagged as a blocker.
	 */
	public function testInvalidPagination() {
		$file_contents = <<<'EOT'
			<?php if ( have_posts () ) : while (have_posts()):the_post();?>
				<h2><a href="<?php echo get_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h2>
				<div class="contents">
					<?php the_content('Read more...'); ?>
				<div class="clear"></div>  
			</div>
		<?php endwhile; endif; ?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'post-pagination-no-pagination', $error_slugs );
	}

	/**
	 * Test that the_pagination() function is detected as valid pagination.
	 */
	public function testValidPaginationThePagination() {
		$file_contents = <<<'EOT'
			<?php
				if ( have_posts() ) :
					while ( have_posts() ) : the_post();
						get_template_part( 'content', get_post_format() );
					endwhile;

					the_pagination( array(
						'prev_text'          => __( 'Previous page', 'theme-slug' ),
						'next_text'          => __( 'Next page', 'theme-slug' ),
						'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'theme-slug' ) . ' </span>',
					) );
				endif;
			?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertNotContains( 'post-pagination-no-pagination', $error_slugs );
	}
        
	/**
	 * Test that posts_nav_link() function is detected as valid pagination.
	 */
	public function testValidPaginationPostsNavLink() {
		$file_contents = <<<'EOT'
			<?php foreach ($posts as $post) : the_post(); ?>
				<?php require('post.php'); ?>
			<?php endforeach; ?>
			<p align="center"><?php posts_nav_link() ?></p>
			?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertNotContains( 'post-pagination-no-pagination', $error_slugs );
	}
        
	/**
	 * Test that paginate_links() function is detected as valid pagination.
	 */
	public function testValidPaginationPaginateLinks() {
		$file_contents = <<<'EOT'
			<?php
			global $wp_query;
			$big = 999999999; // need an unlikely integer
			
			echo paginate_links( array(
				'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format' => '?paged=%#%',
				'current' => max( 1, get_query_var( 'paged' ) ),
				'total' => $wp_query->max_num_pages
			) );
			?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertNotContains( 'post-pagination-no-pagination', $error_slugs );
	}
	
	/**
	 * Test that the previous_posts_link() and next_posts_link function are detected as valid pagination.
	 */
	public function testValidPaginationNextPreviousPostsLink() {
		$file_contents = <<<'EOT'
			<div class="nav-links">
				<?php if ( get_next_posts_link() ) : ?>
					<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'theme-slug' ) ); ?></div>
				<?php endif; ?>
			
				<?php if ( get_previous_posts_link() ) : ?>
					<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'theme-slug' ) ); ?></div>
				<?php endif; ?>
			
			</div><!-- .nav-links -->
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertNotContains( 'post-pagination-no-pagination', $error_slugs );
	}

}
