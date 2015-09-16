<?php
/**
 * Verifies that extra queries are run correctly:
 * - No use of `query_posts()`.
 * - No use of `wp_reset_query()`.
 */

class QueryCheck extends CodeCheck {

	function __construct() {
		parent::__construct( array(
			'PhpParser\Node\Expr\FuncCall' => function( $node ) {
				if ( ! $node->name instanceof PhpParser\Node\Name ) {
					return;
				}

				$name = $node->name->toString();

				//var_dump( $name );

				if ( 'query_posts' === $name ) {
					$this->add_error(
						'query_query_posts',
						wp_kses( __( '<code>query_posts()</code> should not be used. Use <code>WP_Query</code> to retrieve additional posts.', 'vip-scanner' ),
							array( 'code' => array() )
						),
						BaseScanner::LEVEL_BLOCKER
					);
				}

				if ( 'wp_reset_query' === $name ) {
					$this->add_error(
						'query_wp_reset_query',
						wp_kses( __( '<code>wp_reset_query()</code> should not be used. Use <code>wp_reset_postdata()</code> to reset after running an additional <code>WP_Query</code>', 'vip-scanner' ),
							array( 'code' => array() )
						),
						BaseScanner::LEVEL_BLOCKER
					);
				}
			}
		) );
	}
}

