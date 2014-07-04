<?php
/**
 * Checks for deprecated WordPress functions.
 */

class DeprecatedFunctionsCheck extends BaseCheck {

	function check( $files ) {
		$result = true;
		$this->increment_check_count();

		$checks = array(
			/**
			 * wp-includes
			 */
			// 0.71
			'the_category_ID'   => 'get_the_category()',
			'the_category_head' => 'get_the_category_by_ID()',
			// 1.2
			'permalink_link' => 'the_permalink()',
			// 1.5
			'start_wp' => '',
			// 1.5.1
			'get_postdata' => 'get_post()',
			// 2.0
			'create_user'                   => 'wp_create_user()',
			'next_post'                     => 'next_post_link()',
			'previous_post'                 => 'previous_post_link()',
			'user_can_create_draft'         => 'current_user_can()',
			'user_can_create_post'          => 'current_user_can()',
			'user_can_delete_post'          => 'current_user_can()',
			'user_can_delete_post_comments' => 'current_user_can()',
			'user_can_edit_post'            => 'current_user_can()',
			'user_can_edit_post_comments'   => 'current_user_can()',
			'user_can_edit_post_date'       => 'current_user_can()',
			'user_can_edit_user'            => 'current_user_can()',
			'user_can_set_post_date'        => 'current_user_can()',
			// 2.1 
			'dropdown_cats'              => 'wp_dropdown_categories()',
			'get_archives'               => 'wp_get_archives()',
			'get_author_link'            => 'get_author_posts_url()',
			'get_autotoggle'             => '',
			'get_link'                   => 'get_bookmark()',
			'get_linkcatname'            => 'get_category()',
			'get_linkrating'             => 'sanitize_bookmark_field()',
			'get_links'                  => 'get_bookmarks()',
			'get_links_list'             => 'wp_list_bookmarks()',
			'get_links_withrating'       => 'get_bookmarks()',
			'get_linksbyname'            => 'get_bookmarks()',
			'get_linkobjects'            => 'get_bookmarks()',
			'get_linkobjectsbyname'      => 'get_bookmarks()',
			'get_linksbyname_withrating' => 'get_bookmarks()',
			'get_settings'               => 'get_option()',
			'link_pages'                 => 'wp_link_pages()',
			'links_popup_script'         => '',
			'list_authors'               => 'wp_list_authors()',
			'list_cats'                  => 'wp_list_categories()',
			'wp_get_links'               => 'wp_list_bookmarks()',
			'wp_get_linksbyname'         => 'wp_list_bookmarks()',
			'wp_get_post_cats'           => 'wp_get_post_categories()',
			'wp_list_cats'               => 'wp_list_categories()',
			'wp_set_post_cats'           => 'wp_set_post_categories()',
			// 2.2
			'comments_rss' => 'get_post_comments_feed_link()',
			// 2.3
			'permalink_single_rss' => 'the_permalink_rss()',
			// 2.5
			'comments_rss_link'        => 'post_comments_feed_link()',
			'get_attachment_icon'      => 'wp_get_attachment_image()',
			'get_attachment_icon_src'  => 'wp_get_attachment_image_src()',
			'get_attachment_innerHTML' => 'wp_get_attachment_image()',
			'get_author_rss_link'      => 'get_author_feed_link()',
			'get_category_rss_link'    => 'get_category_feed_link()',
			'get_the_attachment_link'  => 'wp_get_attachment_link()',
			'gzip_compression'         => '',
			'wp_clearcookie'           => 'wp_clear_auth_cookie()',
			'wp_get_cookie_login'      => '',
			'wp_login'                 => 'wp_signon()',
			'wp_setcookie'             => 'wp_set_auth_cookie()',
			// 2.7
			'get_commentdata' => 'get_comment()',
			// 2.8
			'__ngettext'                 => '_n()',
			'__ngettext_noop'            => '_n_noop()',
			'attribute_escape'           => 'esc_attr()',
			'get_author_name'            => "get_the_author_meta( 'display_name' )",
			'get_category_children'      => 'get_term_children()',
			'get_catname'                => 'get_cat_name()',
			'get_the_author_aim'         => "get_the_author_meta( 'aim' )",
			'get_the_author_description' => "get_the_author_meta( 'description' )",
			'get_the_author_email'       => "get_the_author_meta( 'email' )",
			'get_the_author_firstname'   => "get_the_author_meta( 'first_name' )",
			'get_the_author_icq'         => "get_the_author_meta( 'icq' )",
			'get_the_author_ID'          => "get_the_author_meta( 'ID' )",
			'get_the_author_lastname'    => "get_the_author_meta( 'last_name' )",
			'get_the_author_login'       => "get_the_author_meta( 'login' )",
			'get_the_author_msn'         => "get_the_author_meta( 'msn' )",
			'get_the_author_nickname'    => "get_the_author_meta( 'nickname' )",
			'get_the_author_url'         => "get_the_author_meta( 'url' )",
			'get_the_author_yim'         => "get_the_author_meta( 'yim' )",
			'js_escape'                  => 'esc_js()',
			'register_sidebar_widget'    => 'wp_register_sidebar_widget()',
			'register_widget_control'    => 'wp_register_widget_control()',
			'sanitize_url'               => 'esc_url_raw()',
			'the_author_aim'             => "the_author_meta( 'aim' )",
			'the_author_description'     => "the_author_meta( 'description' )",
			'the_author_email'           => "the_author_meta( 'email' )",
			'the_author_firstname'       => "the_author_meta( 'first_name' )",
			'the_author_icq'             => "the_author_meta( 'icq' )",
			'the_author_ID'              => "the_author_meta( 'ID' )",
			'the_author_lastname'        => "the_author_meta( 'last_name' )",
			'the_author_login'           => "the_author_meta( 'login' )",
			'the_author_msn'             => "the_author_meta( 'msn' )",
			'the_author_nickname'        => "the_author_meta( 'nickname' )",
			'the_author_url'             => "the_author_meta( 'url' )",
			'the_author_yim'             => "the_author_meta( 'yim' )",
			'unregister_sidebar_widget'  => 'wp_unregister_sidebar_widget()',
			'unregister_widget_control'  => 'wp_unregister_widget_control()',
			'wp_specialchars'            => 'esc_html()',
			// 2.9
			'_c'                => '_x()',
			'make_url_footnote' => '',
			'the_content_rss'   => 'the_content_feed()',
			// 3.0
			'_nc'                       => '_nx()',
			'automatic_feed_links'      => "add_theme_support( 'automatic-feed-links' )",
			'clean_url'                 => 'esc_url_raw()',
			'clear_global_post_cache'   => 'clean_post_cache()',
			'delete_usermeta'           => 'delete_user_meta()',
			'funky_javascript_callback' => '',
			'funky_javascript_fix'      => '',
			'generate_random_password'  => 'wp_generate_password()',
			'get_alloptions'            => 'wp_load_alloptions()',
			'get_blog_list'             => 'wp_get_sites()',
			'get_most_active_blogs'     => '',
			'get_profile'               => 'get_the_author_meta()',
			'get_user_details'          => 'get_user_by()',
			'get_usermeta'              => 'get_user_meta()',
			'get_usernumposts'          => 'count_user_posts()',
			'graceful_fail'             => 'wp_die()',
			'is_main_blog'              => 'is_main_site()',
			'is_site_admin'             => 'is_super_admin()',
			'is_taxonomy'               => 'taxonomy_exists()',
			'is_term'                   => 'term_exists()',
			'set_current_user'          => 'wp_set_current_user()',
			'translate_with_context'    => '_x()',
			'update_usermeta'           => 'update_user_meta()',
			'validate_email'            => 'is_email()',
			// 3.1
			'get_dashboard_blog'    => '',
			'get_users_of_blog'     => 'get_users()',
			'is_plugin_page'        => '',
			'update_category_cache' => '',
			// 3.2
			'wp_timezone_supported' => '',
			'wp_clone'              => '',
			// 3.3
			'get_boundary_post_rel_link'            => '',
			'get_index_rel_link'                    => '',
			'get_parent_post_rel_link'              => '',
			'get_user_by_email'                     => "get_user_by( 'email' )",
			'get_user_metavalues'                   => '',
			'get_userdatabylogin'                   => "get_user_by( 'login' )",
			'index_rel_link'                        => '',
			'is_blog_user'                          => 'is_user_member_of_blog()',
			'parent_post_rel_link'                  => '',
			'sanitize_user_object'                  => '',
			'start_post_rel_link'                   => '',
			'the_editor'                            => 'wp_editor()',
			'wp_admin_bar_dashboard_view_site_menu' => '',
			'wpmu_admin_do_redirect'                => '',
			'wpmu_admin_redirect_add_updated_param' => '',
			// 3.4
			'add_custom_background'      => 'add_theme_support( "custom-background", $args )',
			'add_custom_image_header'    => 'add_theme_support( "custom-header", $args )',
			'clean_page_cache'           => 'clean_post_cache()',
			'clean_pre'                  => '',
			'debug_fclose'               => 'error_log()',
			'debug_fopen'                => 'error_log()',
			'debug_fwrite'               => 'error_log()',
			'get_current_theme'          => 'wp_get_theme()',
			'get_theme'                  => 'wp_get_theme()',
			'get_theme_data'             => 'wp_get_theme()',
			'get_themes'                 => 'wp_get_themes()',
			'remove_custom_background'   => "remove_theme_support( 'custom-background' )",
			'remove_custom_image_header' => "remove_theme_support( 'custom-header' )",
			'update_page_cache'          => 'update_post_cache()',
			'wp_explain_nonce'           => 'wp_nonce_ays',
			// 3.5
			'_get_post_ancestors'   => '',
			'_save_post_hook'       => '',
			'gd_edit_image_support' => 'wp_image_editor_supports()',
			'get_page'              => 'get_post()',
			'image_resize'          => 'wp_get_image_editor()',
			'sticky_class'          => 'post_class()',
			'user_pass_ok'          => 'wp_authenticate()',
			'wp_cache_reset'        => '',
			'wp_get_single_post'    => 'get_post()',
			'wp_load_image'         => 'wp_get_image_editor()',
			// 3.6
			'get_user_id_from_string' => 'get_user_by()',
			'wp_convert_bytes_to_hr'  => 'size_format()',
			// 3.7
			'_search_terms_tidy'        => '',
			'get_blogaddress_by_domain' => '',
			// 3.9
			'rich_edit_exists'         => '',
			'default_topic_count_text' => '',
			'format_to_post'           => '',
			'get_current_site_name'    => 'get_current_site()',
			'wpmu_current_site'        => '',
			/**
			 * wp-admin
			 */
			// MU
			'install_blog_defaults' => 'wp_install_defaults()',
			// 2.1
			'tinymce_include' => 'wp_editor()',
			// 2.5
			'documentation_link' => '',
			// 2.6
			'dropdown_categories'      => 'wp_category_checklist()',
			'dropdown_link_categories' => 'wp_link_category_checklist()',
			// 2.9
			'get_real_file_to_edit' => '',
			// 3.0
			'activate_sitewide_plugin'     => 'activate_plugin()',
			'add_option_update_handler'    => 'register_setting()',
			'codepress_footer_js'          => '',
			'codepress_get_lang'           => '',
			'deactivate_sitewide_plugin'   => 'deactivate_plugin()',
			'is_wpmu_sitewide_plugin'      => 'is_network_only_plugin()',
			'ms_deprecated_blogs_file'     => '',
			'mu_options'                   => '',
			'remove_option_update_handler' => 'unregister_setting()',
			'use_codepress'                => '',
			'wp_dropdown_cats'             => 'wp_dropdown_categories()',
			'wp_shrink_dimensions'         => 'wp_constrain_dimensions()',
			'wpmu_checkAvailableSpace'     => 'is_upload_space_available()',
			'wpmu_get_blog_allowedthemes'  => 'WP_Theme::get_allowed_on_site()',
			'wpmu_menu'                    => '',
			// 3.1
			'get_author_user_ids'          => 'get_users()',
			'get_editable_authors'         => 'get_users()',
			'get_editable_user_ids'        => 'get_users()',
			'get_nonauthor_user_ids'       => 'get_users()',
			'get_others_drafts'            => '',
			'get_others_unpublished_posts' => '',
			'get_others_pending'           => '',
			'install_themes_feature_list'  => 'get_theme_feature_list()',
			// 3.2
			'favorite_actions'                => 'WP_Admin_Bar',
			'wp_dashboard_quick_press_output' => 'wp_dashboard_quick_press()',
			// 3.3
			'add_contextual_help' => 'get_current_screen()->add_help_tab()',
			'media_upload_audio'  => 'wp_media_upload_handler()',
			'media_upload_file'   => 'wp_media_upload_handler()',
			'media_upload_image'  => 'wp_media_upload_handler()',
			'media_upload_video'  => 'wp_media_upload_handler()',
			'screen_layout'       => 'get_current_screen()->render_per_page_options()',
			'screen_meta'         => 'get_current_screen()->render_screen_meta()',
			'screen_options'      => 'get_current_screen()->render_per_page_options()',
			'type_url_form_audio' => "wp_media_insert_url_form( 'audio' )",
			'type_url_form_file'  => "wp_media_insert_url_form( 'file' )",
			'type_url_form_image' => "wp_media_insert_url_form( 'image' )",
			'type_url_form_video' => "wp_media_insert_url_form( 'video' )",
			'wp_preload_dialogs'  => 'wp_editor()',
			'wp_print_editor_js'  => 'wp_editor()',
			'wp_quicktags'        => 'wp_editor()',
			'wp_tiny_mce'         => 'wp_editor()',
			// 3.4
			'current_theme_info'      => 'wp_get_theme()',
			'get_allowed_themes'      => "wp_get_themes( array( 'allowed' => true ) )",
			'get_broken_themes'       => "wp_get_themes( array( 'errors' => true )",
			'get_site_allowed_themes' => 'WP_Theme::get_allowed_on_network()',
			'current_theme_info'      => 'wp_get_theme()',
			'display_theme'           => '',
			'get_allowed_themes'      => "wp_get_themes( array( 'allowed' => true ) )",
			'get_broken_themes'       => "wp_get_themes( array( 'errors' => true )",
			// 3.5
			'_insert_into_post_button' => '',
			'_media_button'            => '',
			'get_default_page_to_edit' => "get_default_post_to_edit( 'page' )",
			'get_post_to_edit'         => 'get_post()',
			'get_udims'                => 'wp_constrain_dimensions()',
			'wp_create_thumbnail'      => 'image_resize()',
			// 3.6
			'wp_nav_menu_locations_meta_box' => '',
			// 3.7
			'the_attachment_links' => '',
			'wp_update_core'       => 'new Core_Upgrader()',
			'wp_update_plugin'     => 'new Plugin_Upgrader()',
			'wp_update_theme'      => 'new Theme_Upgrader()',
			// 3.8
			'get_screen_icon'                      => '',
			'screen_icon'                          => '',
			'wp_dashboard_incoming_links'          => '',
			'wp_dashboard_incoming_links_control'  => '',
			'wp_dashboard_incoming_links_output'   => '',
			'wp_dashboard_plugins'                 => '',
			'wp_dashboard_primary_control'         => '',
			'wp_dashboard_recent_comments_control' => '',
			'wp_dashboard_secondary'               => '',
			'wp_dashboard_secondary_control'       => '',
			'wp_dashboard_secondary_output'        => '',
			// 3.9
			'_relocate_children' => '',
			/**
			 * Root Folder
			 */
			// 3.4
			'logIO' => 'error_log()',
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {

			foreach ( $checks as $check => $replacement ) {
				
				/**
				 * Before a function, there's either a start of a line, whitespace, . or (
				 * This is to avoid false positives, like wp_link_pages() being flagged as link_pages().
				 */
				if ( preg_match( '/(?:^|[\s\.\(])' . $check . '\(/m', $file_content, $matches ) ) {
					$deprecated_function = trim( rtrim( $matches[0], '(' ) );

					// Indicate the deprecated function that has been found.
					$message = 'The function <code>' . $deprecated_function . '</code> is deprecated.';

					// Indicate the replacement function if it exists.
					if ( ! empty( $replacement ) ) {
						$message .= ' Use <code>' . $replacement . '</code> instead.';
					}

					$this->add_error(
						'deprecated',
						$message,
						BaseScanner::LEVEL_BLOCKER,
						$this->get_filename( $file_path )
					);
					$result = false;
				}
			}

		}

		return $result;
	}
}
