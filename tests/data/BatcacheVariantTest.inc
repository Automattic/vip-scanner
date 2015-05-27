<?php

die(); //Don't actually run the following code.

function is_feedburner() {
    if ( function_exists( 'vary_cache_on_function' ) ) {
		vary_cache_on_function(	'require("something_dangerous.php");' );
    }
    return (bool) preg_match("/feedburner|feedvalidator/i", $_SERVER["HTTP_USER_AGENT"]);
}


