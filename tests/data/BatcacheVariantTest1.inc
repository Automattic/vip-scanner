<?php
function is_dangerous() {
    if ( function_exists( 'vary_cache_on_function' ) ) {
		vary_cache_on_function(	'require_once("something_dangerous.php");' );
    }
    return require_once("something_dangerous.php");
}
