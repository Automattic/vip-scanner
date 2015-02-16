<?php

die(); //Don't actually run the following code.

esc_attr( printf( 'unescaped string' ) );
esc_attr( print 'unescaped string' );
esc_attr( echo 'unescaped string' );
?>

<a title="<?php _e( 'unescaped HTML attribute' ); ?>" />
<a title='<?php _e( 'unescaped HTML attribute' ); ?>' />
