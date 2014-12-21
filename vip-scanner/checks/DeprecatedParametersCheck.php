<?php
/**
 * Checks for deprecated parameters.
 */

class DeprecatedParametersCheck extends CodeCheck {

	protected static $parameters = array(
		'get_bloginfo' => array(
			'home'                 => 'home_url()',
			'url'                  => 'home_url()',
			'wpurl'                => 'site_url()',
			'stylesheet_directory' => 'get_stylesheet_directory_uri()',
			'template_directory'   => 'get_template_directory_uri()',
			'template_url'         => 'get_template_directory_uri()',
			'text_direction'       => 'is_rtl()',
			'feed_url'             => "get_feed_link( 'feed' ), where feed is rss, rss2 or atom",
		),
		'bloginfo' => array(
			'home'                 => 'echo esc_url( home_url() )',
			'url'                  => 'echo esc_url( home_url() )',
			'wpurl'                => 'echo esc_url( site_url() )',
			'stylesheet_directory' => 'echo esc_url( get_stylesheet_directory_uri() )',
			'template_directory'   => 'echo esc_url( get_template_directory_uri() )',
			'template_url'         => 'echo esc_url( get_template_directory_uri() )',
			'text_direction'       => 'is_rtl()',
			'feed_url'             => "echo esc_url( get_feed_link( 'feed' ) ), where feed is rss, rss2 or atom",
		),
		'get_option' => array(
			'home'     => 'home_url()',
			'site_url' => 'site_url()',
		)
	);

	function __construct() {
		parent::__construct( array(
			'PhpParser\Node\Expr\FuncCall' => function( $node ) {
				$name = $node->name->toString();
				if ( ! array_key_exists( $name, self::$parameters ) || empty( $node->args ) ) {
					return;
				}
				$pars = self::$parameters[ $name ];
				$value = $node->args[0]->value;
				if ( $value instanceof PhpParser\Node\Scalar\String && array_key_exists( $value->value, $pars ) ) {
					$message = 'The deprecated function parameter %1$s was found. Use %2$s instead.';
					$this->add_error(
						'deprecated',
						sprintf( $message, '<code>' . esc_html( $name . "( '" . $value->value . "' )" ) . '</code>' , '<code>' . esc_html( $pars[ $value->value ] ) . '</code>' ),
						BaseScanner::LEVEL_BLOCKER
					);
				}
			},
		) );
	}
}
