<?php

class VIPRestrictedPatternsCheck extends BaseCheck
{
	function check( $files ) {
		$result = true;

		$vip_plugins_check_regex = $this->get_vip_plugins_check_regex();

		$checks = array(
			"/(\\\$isIE)+/msiU" => array( "level" => "Warning", "note" => 'Using $isIE conflicts with full page caching' ),
			"/(\\\$_REQUEST)+/msiU" => array( "level" => "Blocker", "note" => 'Using $_REQUEST is forbidden. You should use $_POST or $_GET' ),
			"/WordPress VIP/msiU" => array( "level" => "Warning", "note" => 'Please use "WordPress.com VIP" rather than "WordPress VIP"' ),
			"/(kses)+/msiU" => array ( "level" => "Note", "note" => "Working with kses" ),
			"/(\\\$wpdb->|mysql_)+.+(ALTER)+\s+/msiU" => array( "level" => "Blocker", "note" => "Possible database table alteration" ),
			"/(\\\$wpdb->|mysql_)+.+(CREATE)+\s+/msiU" => array( "level" => "Blocker", "note" => "Possible database table creation" ),
			"/(\\\$wpdb->|mysql_)+.+(DROP)+\s+/msiU" => array( "level" => "Blocker", "note" => "Possible database table deletion" ),
			"/(\\\$wpdb->|mysql_)+.+(DELETE)+\s+(FROM)+\s+/msiU" => array( "level" => "Note", "note" => "Direct database delete query" ),
			"/(\\\$wpdb->|mysql_)+.+(SELECT)+\s.+/msiU" => array( "level" => "Note", "note" => "Direct Database select query" ),
			"/(^GLOBAL)(\\\$wpdb->|mysql_)+/msiU" => array( "level" => "Warning", "note" => "Possible direct database query" ),
			"/(echo|print|\<\?\=)+.+(\\\$GLOBALS|\\\$_SERVER|\\\$_GET|\\\$_POST)+/msiU" => array( "level" => "Note", "note" => "Possible output of restricted variables" ),
			"/(echo|print|\<\?\=)+.+(get_search_query)+/msiU" => array( "level" => "Warning", "note" => "Output of search query" ),
			"/(\\\$GLOBALS|\\\$_SERVER|\\\$_GET|\\\$_POST)+/msiU" => array( "level" => "Note", "note" => "Working with superglobals" ),
			"/(\\\$_SERVER\[(?!('|\"REQUEST_URI|SCRIPT_FILENAME|HTTP_HOST'|\"))([^]]+|)\])+/msiU" => array( "level" => "Blocker", "note" => 'Non whitelisted $_SERVER superglobals found in this file' ),
			$vip_plugins_check_regex => array( "level" => "Note", "note" => "Possible inclusion of plugins that exist as a VIP Shared Plugin" ),
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			foreach ( $checks as $check => $check_info ) {
				$this->increment_check_count();

				if ( preg_match( $check, $file_content, $matches ) ) {
					$filename = $this->get_filename( $file_path );
					$error = rtrim( $matches[0], '(' );//esc_html( rtrim( $matches[0],'(') );
					$lines = $this->grep_content( $matches[0], $file_content );
					$this->add_error(
						$check,
						$check_info['note'],
						$check_info['level'],
						$filename,
						$lines
					);
					$result = false;
				}
			}
		}

		return $result;
	}

	/* 
	 * Regex of Plugins we have, used to check for duplicates
	 *
	 * @return string regex string to check for duplicates
	 */
	private function get_vip_plugins_check_regex() {
		// The list of plugins are hardcoded for now, 
		// this can be adjusted in the future
		$vip_plugins = array(
			'ad-code-manager',
			'adbusters',
			'add-meta-tags-mod',
			'advanced-excerpt',
			'ajax-comment-loading',
			'ajax-comment-preview',
			'angellist',
			'art-direction-redux',
			'ays-publish',
			'bitly',
			'blimply',
			'breadcrumb-navxt',
			'breadcrumb-navxt-39',
			'brightcove',
			'browsi',
			'byline',
			'cache-nav-menu',
			'camptix',
			'category-posts-widget',
			'chartbeat',
			'cheezcap',
			'cheeztest',
			'co-authors-plus',
			'co-authors-plus-social-pack',
			'column-shortcodes',
			'custom-metadata',
			'daylife',
			'disable-comments-query',
			'disqus',
			'document-feedback',
			'dynamic-content-gallery',
			'easy-custom-fields',
			'ecwid',
			'edit-flow',
			'editorial-calendar',
			'editorize',
			'expiring-posts',
			'external-links-new-window',
			'external-permalinks-redux',
			'facebook',
			'feedwordpress',
			'fieldmanager',
			'five-min-video-suggest',
			'flag-comments',
			'formategory',
			'gallery-style-cleanup',
			'get-the-image',
			'getty-images',
			'google-calendar-events',
			'hidden-posts',
			'history-bar',
			'ice',
			'image-metadata-cruncher',
			'inform',
			'intensedebate',
			'internacional',
			'janrain-capture',
			'json-feed',
			'kapost-byline',
			'kimili-flash-embed',
			'lazy-load',
			'lib',
			'lift-search',
			'lightbox-plus',
			'livefyre3',
			'localtime',
			'maintenance-mode',
			'mce-table-buttons',
			'mediapass',
			'most-commented',
			'most-popular-feed-wpcom',
			'msm-sitemap',
			'multiple-post-thumbnails',
			'navt',
			'nbcs-advanced-blacklist',
			'nbcs-moderation-queue-alerts',
			'new-device-notification',
			'newscred',
			'ooyala',
			'post-author-box',
			'post-forking',
			'post-meta-inspector',
			'post-revision-workflow',
			'postrelease-vip',
			'publishthis',
			'push-syndication',
			'recent-comments',
			'responsive-images',
			'safe-redirect-manager',
			'safe-report-comments',
			'sailthru',
			'scrollkit-wp',
			'search-excerpt',
			'sem-frame-buster',
			'seo-auto-linker',
			'seo-friendly-images-mod',
			'share-this-classic-wpcom',
			'share-this-wpcom',
			'shopify-store',
			'shoplocket',
			'simple-page-ordering',
			'simply-show-ids',
			'socialflow',
			'sticky-custom-post-types',
			'stipple',
			'storify',
			'subheading',
			'table-of-contents',
			'taxonomy-images',
			'taxonomy-list-widget',
			'term-management-tools',
			'the-attached-image',
			'thePlatform',
			'tinypass',
			'tw-print',
			'uppsite',
			'view-all-posts-pages',
			'vip-do-not-include-on-wpcom',
			'vip-helper-stats-wpcom',
			'vip-helper-wpcom',
			'vip-helper',
			'vip-init',
			'voce-settings-api',
			'watermark-image-uploads',
			'wordtwit-1.3-mod',
			'wp-frontend-uploader',
			'wp-google-analytics',
			'wp-help',
			'wp-large-options',
			'wp-page-numbers',
			'wp-pagenavi',
			'wp-paginate',
			'wpcom-allow-contributors-to-upload',
			'wpcom-elasticsearch',
			'wpcom-geo-uniques',
			'wpcom-legacy-redirector',
			'wpcom-related-posts',
			'wpcom-thumbnail-editor',
			'zemanta',
			'zoninator',
		);

		// include, require, include_once, require_once followed by optional space
		$regex = "/(include|require)(_once)?(\s)?";
		// anything followed by one of the plugin names from the above array
		$regex .= "(.*)(%s)";
		// then it needs .php finally it needs ', "
		$regex .= "(.php('|\"))/";

		// Add in the vip plugins names to check against
		return sprintf($regex, implode('|', $vip_plugins));
	}
}
