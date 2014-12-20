<?php

die(); //Don't actually run the following code.

// Warning: Deprecated VIP Plugin. Use livefyre3 instead.
wpcom_vip_load_plugin( "livefyre" );

// Warning: Deprecated VIP Plugin. Functionality included in Jetpack.
wpcom_vip_load_plugin( "wpcom-related-posts", "plugins/" );

// No warning.
wpcom_vip_load_plugin( "livefyre3" );
