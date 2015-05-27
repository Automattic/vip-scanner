<?php
/*
 * Checks for invalid batcache variant function vary_cache_on_function() that:
 * - includes dangerous illegal functions
 * - does not reference superglobal variable
 */

class BatcacheVariantCheck extends CodeCheck {

	function __construct() {
		parent::__construct( array(
			'PhpParser\Node\Expr\FuncCall' => function( $node ) {
				$name = $node->name->toString();
				if ( 'vary_cache_on_function' == $name ) {
					$value = $node->args[0]->value;
					if ( $value instanceof PhpParser\Node\Scalar\String )  {
						$function_string = $value->value;
						if ( preg_match('/include|require|echo|print|dump|export|open|sock|unlink|`|eval/i', $function_string, $matches) ) {
							$this->add_error(
								'batcache-variant-error',
								'Illegal word "'.$matches[0].'" in variant determiner.',
								BaseScanner::LEVEL_BLOCKER
							);
						}
						if ( !preg_match('/\$_/', $function_string) ) {
							$this->add_error(
								'batcache-variant-error',
								'Variant determiner should refer to at least one $_ superglobals.',
								BaseScanner::LEVEL_BLOCKER
							);
						}
					}
				}
			}
		) );
	}
}
