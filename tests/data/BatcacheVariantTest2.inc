<?php
// there should be no error for the following
function is_feedburner() {
    if ( function_exists( 'vary_cache_on_function' ) ) {
        vary_cache_on_function(
            'return (bool) preg_match("/feedburner|feedvalidator/i", $_SERVER["HTTP_USER_AGENT"]);'
        );
    }
    return (bool) preg_match("/feedburner|feedvalidator/i", $_SERVER["HTTP_USER_AGENT"]);
}
