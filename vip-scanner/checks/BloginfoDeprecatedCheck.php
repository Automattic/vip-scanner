<?php
class BloginfoDeprecatedCheck extends BaseCheck {
	function check( $files ) {
		$result = true;

		$checks = array(
			'/[\s|]get_bloginfo\((\s|)("|\')url("|\')(\s|)\)/m' => 'home_url()',
			'/[\s|]get_bloginfo\((\s|)("|\')wpurl("|\')(\s|)\)/m' => 'site_url()',
			'/[\s|]get_bloginfo\((\s|)("|\')stylesheet_directory("|\')(\s|)\)/m' => 'get_stylesheet_directory_uri()',
			'/[\s|]get_bloginfo\((\s|)("|\')template_directory("|\')(\s|)\)/m' => 'get_template_directory_uri()',
			'/[\s|]get_bloginfo\((\s|)("|\')template_url("|\')(\s|)\)/m' => 'get_template_directory_uri()',
			'/[\s|]get_bloginfo\((\s|)("|\')text_direction("|\')(\s|)\)/m' => 'is_rtl()',
			'/[\s|]get_bloginfo\((\s|)("|\')feed_url("|\')(\s|)\)/m' => 'get_feed_link( \'feed\' ) (where feed is rss, rss2, atom)',
			'/[\s|]bloginfo\((\s|)("|\')url("|\')(\s|)\)/m' => 'echo home_url()',
			'/[\s|]bloginfo\((\s|)("|\')wpurl("|\')(\s|)\)/m' => 'echo site_url()',
			'/[\s|]bloginfo\((\s|)("|\')stylesheet_directory("|\')(\s|)\)/m' => 'get_stylesheet_directory_uri()',
			'/[\s|]bloginfo\((\s|)("|\')template_directory("|\')(\s|)\)/m' => 'get_template_directory_uri()',
			'/[\s|]bloginfo\((\s|)("|\')template_url("|\')(\s|)\)/m' => 'get_template_directory_uri()',
			'/[\s|]bloginfo\((\s|)("|\')text_direction("|\')(\s|)\)/m' => 'is_rtl()',
			'/[\s|]bloginfo\((\s|)("|\')feed_url("|\')(\s|)\)/m' => 'get_feed_link( \'feed\' ) (where feed is rss, rss2, atom)',
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			foreach ( $checks as $key => $check ) {
				$this->increment_check_count();
				if ( preg_match( $key, $file_content, $matches ) ) {
					$filename = $this->get_filename( $file_path );
					$error = trim( rtrim( $matches[0], '(' ) ); //trim( esc_html( rtrim( $matches[0], '(' ) ) );
					$lines = $this->grep_content( rtrim( $matches[0], '(' ), $file_content );
					$this->add_error(
						$key,
						sprintf( '`%1$s` was found in the file. Use `%2$s` instead.', $error, $check ),
						'recommended',
						$filename,
						$lines
					);
					$result = false;
				}
			}
		}
		return $result;
	}
}