<?php
/**
 * Checks for CSS and JS resources being loaded from a CDN.
 */

class CDNCheck extends BaseCheck {

	function check( $files ) {

		$result = true;
		$php_code = $this->merge_files( $files, 'php' );

		$cdn_list = array(
			'bootstrap-maxcdn'      => 'maxcdn.bootstrapcdn.com/bootstrap',
			'bootstrap-netdna'      => 'netdna.bootstrapcdn.com/bootstrap',
			'bootswatch-maxcdn'     => 'maxcdn.bootstrapcdn.com/bootswatch',
			'bootswatch-netdna'     => 'netdna.bootstrapcdn.com/bootswatch',
			'font-awesome-maxcdn'   => 'maxcdn.bootstrapcdn.com/font-awesome',
			'font-awesome-netdna'   => 'netdna.bootstrapcdn.com/font-awesome',
			'html5shiv-google'      => 'html5shiv.googlecode.com/svn/trunk/html5.js',
			'html5shiv-maxcdn'      => 'oss.maxcdn.com/libs/html5shiv',
			'jquery'                => 'code.jquery.com/jquery-',
			'respond-js'            => 'oss.maxcdn.com/libs/respond.js',
		);

		$this->increment_check_count();
		foreach( $cdn_list as $cdn_slug => $cdn_url ) {
			if ( false !== strpos( $php_code, $cdn_url ) ) {
				$this->add_error(
					'cdn-' . $cdn_slug,
					sprintf( 'Found the URL of a CDN in the code: %s. You cannot load CSS or Javascript resources from a CDN, please bundle them with the theme.', '<code>' . esc_html( $cdn_url ) . '</code>' ),
					BaseScanner::LEVEL_BLOCKER
				);
				$result = false;
			}
		}

		return $result;
	}
}
