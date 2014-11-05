<?php

/**
 * Checks for redirect calls in core template files.
 */
class TemplateForbiddenFunctionsCheck extends BaseCheck {

	function check( $files ) {
		$result = true;
		$this->increment_check_count();

		$theme_file_patterns = array(
			'/(\/)?404.php/',
			'/(\/)?archive(-([^\/]+))?.php/',
			'/(\/)?attachment.php/',
			'/(\/)?author(-(\d+|([^\/]+)))?.php/',
			'/(\/)?category(-(\d+|([^\/]+)))?.php/',
			'/(\/)?comments.php/',
			'/(\/)?(featured-)?content(-([^\/]+))?.php/',
			'/(\/)?date.php/',
			'/(\/)?footer.php/',
			'/(\/)?front-page.php/',
			'/(\/)?header.php/',
			'/(\/)?home.php/',
			'/(\/)?image.php/',
			'/(\/)?index.php/',
			'/(\/)?search.php/',
			'/(\/)?page(-(\d+|([^\/]+)))?.php/',
			'/(\/)?paged.php/',
			'/(\/)?sidebar(-([^\/]+))?.php/',
			'/(\/)?single(-(post|([^\/]+)))?.php/',
			'/(\/)?tag(-(\d+|([^\/]+)))?.php/',
			'/(\/)?taxonomy(-(\d+|([^\/]+)))?.php/',
		);

		$mime_types = get_allowed_mime_types();

		foreach ( $mime_types as $extension => $mime_type ) {
			$theme_file_patterns[] = '/' . str_replace( '/', '_', $mime_type ) . '.php/';

			preg_match( '/(.+)\/(.+)/', $mime_type, $patterns );

			if ( ! empty( $patterns ) ) {
				for ( $index = 1; $index <= 2; $index ++ ) {
					if ( isset( $patterns[ $index ] ) && ! empty( $patterns[ $index ] ) ) {
						$new_pattern = '/(^-)' . str_replace( '/', '_', $patterns[ $index ] ) . '.php/';
						if ( ! in_array( $new_pattern, $theme_file_patterns ) ) {
							$theme_file_patterns[] = $new_pattern;
						}
					}
				}
			}
		}

		$checks = array(
			'auth_redirect',
			'maybe_redirect_404',
			'redirect_canonical',
			'redirect_post',
			'wp_old_slug_redirect',
			'wp_redirect',
			'wp_redirect_admin_locations',
			'wp_safe_redirect',
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {

			// Match template files that can have any names
			if ( preg_match( '/Template Name:/', $file_content, $file_matches ) ) {
				if ( false == $this->check_file_contents( $checks, $file_path, $file_content ) ) {
					$result = false;
				}
			} else {
				// Test incoming file against all theme file name patterns
				foreach ( $theme_file_patterns as $theme_file_pattern ) {
					if ( preg_match( $theme_file_pattern, $file_path, $file_matches ) ) {
						if ( false == $this->check_file_contents( $checks, $file_path, $file_content ) ) {
							$result = false;
						};
					}
				}
			}
		}

		return $result;
	}

	function check_file_contents( $checks, $file_path, $file_content ) {
		$result = true;

		foreach ( $checks as $check ) {
			if ( false !== strpos( $file_content, $check ) ) {
				$lines = $this->grep_content( $check, $file_content );
				$this->add_error(
					$check,
					sprintf( 'Redirection functions should not be called from template files' ),
					'blocker',
					$this->get_filename( $file_path ),
					$lines
				);
				$result = false;
			}
		}

		return $result;
	}
}
