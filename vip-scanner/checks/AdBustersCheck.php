<?php
/**
 * Checks for correct @package information.
 */

class AdBustersCheck extends BaseCheck {

	function check( $files ) {

		$result = true;

		$this->increment_check_count();

		$html_files = $this->filter_files( $files, 'html' );

		foreach ( $html_files as $file_path => $file_contents ) {

			if ( $this->is_adbuster( $file_path ) ) {
				$this->add_error(
					'adbuster-error',
					'Found a file which is an ad frame buster. Please use <a href="https://github.com/Automattic/Adbusters">Adbusters plugin</a> instead.',
					BaseScanner::LEVEL_BLOCKER,
					basename( $file_path )
				);
				$result = false;
			} else if ( $this->maybe_adbuster( $file_path ) ) {
				$this->add_error(
					'adbuster-error',
					'Found a file which may be an ad frame buster. Please use <a href="https://github.com/Automattic/Adbusters">Adbusters plugin</a> instead.',
					BaseScanner::LEVEL_WARNING,
					basename( $file_path )
				);
				$result = false;
			}

		}

		return $result;
	}

	public function get_adbusters_array() {

		if ( function_exists( 'wpcom_vip_load_plugin' ) ) {
			wpcom_vip_load_plugin( 'Adbusters' );
			if ( function_exists( 'wpcom_vip_get_ad_busters_array' ) ) {
				return wpcom_vip_get_ad_busters_array();
			}
		}

		return array(
			'adcentric/ifr_b.html',              // AdCentric
			'adinterax/adx-iframe-v2.html',      // AdInterax
			'atlas/atlas_rm.htm',                // Atlas
			'blogads/iframebuster-4.html',       // BlogAds
			'checkm8/CM8IframeBuster.html',      // CheckM8
			'comscore/cs-arIframe.htm',          // comScore
			'doubleclick/DARTIframe.html',       // Google - DoubleClick
			'doubleclick/fif.html',              // Flite
			'eyeblaster/addineyeV2.html',        // MediaMind - EyeBlaster
			'eyewonder/interim.html',            // EyeWonder
			'flashtalking/ftlocal.html',         // Flashtalking
			'flite/fif.html',                    // Flite
			'gumgum/iframe_buster.html',         // gumgum
			'interpolls/pub_interpolls.html',    // Interpolls
			'jivox/jivoxIBuster.html',           // Jivox
			'jpd/jpxdm.html',                    // Jetpack Digital
			'mediamind/MMbuster.html',           // MediaMind - addineye (?)
			'mixpo/framebust.html',              // Mixpo
			'oggifinogi/oggiPlayerLoader.htm',   // Collective - OggiFinogi
			'pictela/Pictela_iframeproxy.html',  // AOL - Pictela
			'pointroll/PointRollAds.htm',        // PointRoll
			'rubicon/rp-smartfile.html',         // Rubicon
			'saymedia/iframebuster.html',        // Say Media
			'smartadserver/iframeout.html',      // SmartAdserver
			'undertone/iframe-buster.html',      // Intercept Interactive - Undertone
			'undertone/UT_iframe_buster.html',   // Intercept Interactive - Undertone
			'xaxis/InfinityIframe.html',         // Xaxis
			'_uac/adpage.html',                  // AOL - atwola.com
			'adcom/aceFIF.html',                 // Advertising.com (ad.com)
		);
	}

	public function is_adbuster( $file ) {

		//parse file path to get filename
		$filename = basename( $file );

		//grab adbusters array
		$adbusters = $this->get_adbusters_array();

		//grab filenames only
		$adbusters = array_map( 'basename', $adbusters );

		//compare!
		if ( true === in_array( $filename, $adbusters ) ) {
			return true;
		}

		return false;
	}

	public function maybe_adbuster( $file, $filesize_check = true, $file_examination = true ) {

		//checkout the file extension - we are looking for htm and html files only
		$path_parts = pathinfo( $file );

		if ( false === isset( $path_parts['extension'] ) ) {
			return false;
		}

		$suspicious_extensions = array(
			'html',
			'htm'
		);

		$extension = mb_strtolower( $path_parts['extension'] );
		if ( false === in_array( $extension, $suspicious_extensions ) ) {
			return false;
		}

		//first - check on the file size, frame busters are usually small files
		if ( true === $filesize_check && filesize( $file ) > 1024 ) {
			return false;
		}

		//some strings in name are highly suspicious - let's flag such files
		$suspicious_strings = array(
			'buster'
		);
		foreach( $suspicious_strings as $string ) {
			if ( false !== mb_strpos( mb_strtolower( $path_parts['basename'] ), $string ) ) {
				return true;
			}
		}

		if ( true === $file_examination ) {
			//ok, so the file is relatively small and it is a static HTML file - that's suspicious, let's do some more tests
			return $this->possible_adbuster_body_check( file_get_contents( $file ) );
		}

		return false;
	}

	public function possible_adbuster_body_check( $file_content ) {
		$dom = new DOMDocument();
		$dom->loadHTML( '<?xml encoding="UTF-8">' . $file_content );
		$scripts = $dom->getElementsByTagName('script');
		//such iframebuster has to have a script tag, at least one
		if ( 0 !== $scripts->length ) {
			//examine body - body without content or body containing script nodes only is suspicious
			$body = $dom->getElementsByTagName( 'body' );
			if ( 0 !== $body->length ) {
				//empty body - flag it!
				if ( '' === trim( $body->item(0)->nodeValue, " \n\r\t\0\xC2\xA0") ) {
					return true;
				}
				//todo: the empty body check above is not catching files with only script nodes in the body
			}
			//static HTML without styles is suspicious as well, flag it
			$styles = $dom->getElementsByTagName('style');
			if( 0 === $styles->length ) {
				return true;
			}
		}
		//looks good
		return false;
	}
}