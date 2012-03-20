<?php

class ThemeNavMenuCheck extends BaseCheck {

	function check( $files ) {

		$result = true;
		$this->increment_check_count();


		$php = $this->merge_files( $files, 'php' );

		if ( strpos( $php, 'nav_menu' ) === false ) {
			$this->add_error(
				'theme-nav-menu',
				'No reference to `nav_menu` was found in the theme. Note that if your theme has a menu bar, it is required to use the WordPress `nav_menu` functionality for it.',
				'' // recommended
			);
		}
		return $result;
	}
}