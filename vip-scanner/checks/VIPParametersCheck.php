<?php

/**
 * Checks for deprecated or potentially problematic parameters.
 *
 * Parameter value will be matched with or without quotes
 * (e.g. 5, '5' will match 5 or 5, 'false' with match 'false' and false)
 */

class VIPParametersCheck extends CodeCheck {
	protected static $parameters = array(
		'wpcom_vip_load_plugin' => array(
				'breadcrumb-navxt' => array(
					'position' => 0,
					'level'    => 'warning',
					'note'     => 'Deprecated VIP Plugin. Use breadcrumb-navxt-39 instead.'
				),
				'livefyre' => array(
					'position' => 0,
					'level'    => 'warning',
					'note'     => 'Deprecated VIP Plugin. Use livefyre3 instead.'
				),
				'feedwordpress' => array(
					'position' => 0,
					'level'    => 'blocker',
					'note'     => 'Deprecated VIP Plugin. No alternative available'
				),
				'wordtwit-1.3-mod' => array(
					'position' => 0,
					'level'    => 'warning',
					'note'     => 'Deprecated VIP Plugin. Use publicize instead.'
				),
				'uppsite' => array(
					'position' => 0,
					'level'    => 'blocker',
					'note'     => 'Deprecated VIP Plugin. Retired from Featured Partner Program.'
				),
				'wpcom-related-posts' => array(
					'position' => 0,
					'level'    => 'warning',
					'note'     => 'Deprecated VIP Plugin. Functionality included in Jetpack.'
				),
				'scrollkit-wp' => array(
					'position' => 0,
					'level'    => 'blocker',
					'note'     => 'Deprecated VIP Plugin. Scroll Kit has shut down.'
				),
		),
	);

	function __construct() {
		parent::__construct( array(
			'PhpParser\Node\Expr\FuncCall' => function( $node ) {
				if ( ! $node->name instanceof PhpParser\Node\Name ) {
					return;
				}

				$name = $node->name->toString();
				if ( ! array_key_exists( $name, self::$parameters ) ) {
					return;
				}
				$pars = self::$parameters[ $name ];
				foreach ( $node->args as $idx => $arg) {
					$value = $arg->value;
					if ( ! ( $value instanceof PhpParser\Node\Scalar\String ) || ! array_key_exists( $value->value, $pars ) ) {
						continue;
					}
					$parameter_data = $pars[ $value->value ];
					if ( isset( $parameter_data['position'] ) && $idx === $parameter_data['position'] ) {
						$this->add_error(
							'vip-parameters-' . $value->value,
							esc_html( $parameter_data['note'] ),
							$parameter_data['level']
						);
					}
				}
			}
		) );
	}
}
