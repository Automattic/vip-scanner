<?php

class ResourceElementFactory {
	public static $resource_types = array(
		'PhpParser\Node\Expr\MethodCall' => array(
			array(
				'func_name'	=> array( 'add_cap' ),
				'plural'	=> 'capabilities',
				'singular'  => 'capability',
			),
		),
		'PhpParser\Node\Expr\FuncCall' => array(
			array(
				'func_name' => array( 'apply_filters' ),
				'plural'	=> 'filters',
				'singular'  => 'filter',
			),

			array(
				'func_name' => array( 'do_action' ),
				'plural'	=> 'actions',
				'singular'  => 'action',
			),

			array(
				'func_name' => array( 'add_role' ),
				'plural'	=> 'roles',
				'singular'  => 'role',
			),

			array(
				'func_name' => array( 'add_shortcode' ),
				'plural'    => 'shortcodes',
				'singular'  => 'shortcode',
			),

			array(
				'func_name' => array( 'register_post_type' ),
				'plural'    => 'custom post types',
				'singular'  => 'custom post type',
			),

			array(
				'func_name' => array( 'register_taxonomy' ),
				'plural'    => 'taxonomies',
				'singular'  => 'taxonomy',
			),

			array(
				'func_name' => array( 'wp_enqueue_script', 'wp_register_script' ),
				'plural'    => 'scripts',
				'singular'  => 'script',
			),

			array(
				'func_name' => array( 'wp_enqueue_style', 'wp_register_style' ),
				'plural'    => 'styles',
				'singular'  => 'style',
			),
		)
	);

	static function create_element( PhpParser\Node $node ) {
		$node_class = get_class( $node );
		if ( array_key_exists( $node_class, self::$resource_types ) ) {
			$infos = self::$resource_types[ $node_class ];
			foreach( $infos as $info ) {
				if ( in_array( $node->name, $info[ 'func_name' ] ) ) {
					$resource = new ResourceCodeElement( $node );
					$resource->set_resource_type( $info['singular'], $info['plural'] );
					return $resource;
				}
			}
		}
		return false;
	}
}
