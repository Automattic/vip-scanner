<?php
/**
 * Checks for different types of XSS vectors
 *
 */
class XSSVectorsCheck extends BaseCheck {
	function sanitize_string( $string ) {

		/**
		 * Removes slashes
		 */
		$string = str_replace( array( '/', '\\' ), '', $string );

		/**
		 * Removes comment blocks
		 */
		$string = preg_replace( '(/\*.*?\*/)', '', $string );

		return $string;
	}

	function check( $files ) {
		$result = true;

		/**
		 * Checks for javascript within href attribute of link tags
		 */
		$this->increment_check_count();
		foreach ( $this->filter_files( $files, array( 'html', 'php' ) ) as $file_path => $file_content ) {
			$regex = '/<link(?:[^\>]*?)href(?:\s)*?=(?:\s)*?(?<QUOTES>[\\\'\"])*(?<HREF>.*?)\k<QUOTES>(?:.*?)>/ims';
			if ( preg_match_all( $regex, $file_content, $matches ) ) {
				$lines = array();
				foreach ( $matches['HREF'] as $match ) {
					$sanitized_string = $this->sanitize_string( $match );
					if ( strpos( $sanitized_string, 'javascript:' )  !== false ) {
						$filename = $this->get_filename( $file_path );
						$lines = array_merge( $this->grep_content( $match, $file_content ), $lines );
						$this->add_error(
							'xss-in-link-tag-href',
							"XSS Attack Vector found in <link/> tag attribute",
							'Warning',
							$filename,
							$lines
						);
						$result = false;
					}
				}
			}
		}

		/**
		 * Checks for javascript within style attribute in all HTML tags
		 */
		$this->increment_check_count();
		foreach ( $this->filter_files( $files, array( 'html', 'php' ) ) as $file_path => $file_content ) {
			$regex = '/<[a-z]*(?:[^\>]*?)style(?:\s)*?=(?:\s)*?(?<QUOTES>[\\\'\"])*(?<ATTR>.*?)\k<QUOTES>(?:.*?)>/ims';
			if ( preg_match_all( $regex, $file_content, $matches ) ) {
				$lines = array();
				foreach ( $matches['ATTR'] as $match ) {
					$sanitized_string = $this->sanitize_string( $match );
					if ( strpos( $sanitized_string, 'javascript:' )  !== false ) {
						$filename = $this->get_filename( $file_path );
						$lines = array_merge( $this->grep_content( $match, $file_content ), $lines );
						$this->add_error(
							'xss-in-style-attribute',
							"XSS Attack Vector found in HTML tag style attribute",
							'Warning',
							$filename,
							$lines
						);
						$result = false;
					}
				}
			}
		}

		/**
		 * Checks for -moz-binding within style tags
		 */
		$this->increment_check_count();
		foreach ( $this->filter_files( $files, array( 'html', 'php' ) ) as $file_path => $file_content ) {
			$regex = '/<[\s]*?style(?:.*?)?>(?<CONTENT>.*?)<[\s]*?\/[\s]*?[a-z]*?[\s]*?>/ims';
			if ( preg_match_all( $regex, $file_content, $matches ) ) {
				$lines = array();
				foreach ( $matches['CONTENT'] as $match ) {
					$sanitized_string = $this->sanitize_string( $match );
					if ( strpos( $sanitized_string, '-moz-binding' )  !== false ) {
						$filename = $this->get_filename( $file_path );
						$lines = array_merge( $this->grep_content( $match, $file_content ), $lines );
						$this->add_error(
							'moz-binding-xss-in-style-tag',
							"XSS Attack Vector found in STYLE tag (-moz-binding)",
							'Warning',
							$filename,
							$lines
						);
						$result = false;
					}
				}
			}
		}

		/**
		 * Checks for -moz-binding within style attribute in all HTML tags
		 */
		$this->increment_check_count();
		foreach ( $this->filter_files( $files, array( 'html', 'php' ) ) as $file_path => $file_content ) {
			$regex = '/<[a-z]*(?:[^\>]*?)style(?:\s)*?=(?:\s)*?(?<QUOTES>[\\\'\"])*(?<ATTR>.*?)\k<QUOTES>(?:.*?)>/ims';
			if ( preg_match_all( $regex, $file_content, $matches ) ) {
				$lines = array();
				foreach ( $matches['ATTR'] as $match ) {
					$sanitized_string = $this->sanitize_string( $match );
					if ( strpos( $sanitized_string, '-moz-binding' )  !== false ) {
						$filename = $this->get_filename( $file_path );
						$lines = array_merge( $this->grep_content( $match, $file_content ), $lines );
						$this->add_error(
							'moz-binding-xss-in-style-attribute',
							"XSS Attack Vector found in STYLE tag (-moz-binding)",
							'Warning',
							$filename,
							$lines
						);
						$result = false;
					}
				}
			}
		}

		/**
		 * Checks for -moz-binding within style tags
		 */
		$this->increment_check_count();
		foreach ( $this->filter_files( $files, array( 'html', 'php' ) ) as $file_path => $file_content ) {
			$regex = '/<[\s]*?style(?:.*?)?>(?<CONTENT>.*?)<[\s]*?\/[\s]*?[a-z]*?[\s]*?>/ims';
			if ( preg_match_all( $regex, $file_content, $matches ) ) {
				$lines = array();
				foreach ( $matches['CONTENT'] as $match ) {
					$sanitized_string = $this->sanitize_string( $match );
					if ( strpos( $sanitized_string, '-moz-binding' )  !== false ) {
						$filename = $this->get_filename( $file_path );
						$lines = array_merge( $this->grep_content( $match, $file_content ), $lines );
						$this->add_error(
							'moz-binding-xss-in-style-tag',
							"XSS Attack Vector found in STYLE tag (-moz-binding)",
							'Warning',
							$filename,
							$lines
						);
						$result = false;
					}
				}
			}
		}

		/**
		 * Checks for 'javascript:' within style tags
		 */
		$this->increment_check_count();
		foreach ( $this->filter_files( $files, array( 'html', 'php' ) ) as $file_path => $file_content ) {
			$regex = '/<[\s]*?style(?:.*?)?>(?<CONTENT>.*?)<[\s]*?\/[\s]*?[a-z]*?[\s]*?>/ims';
			if ( preg_match_all( $regex, $file_content, $matches ) ) {
				$lines = array();
				foreach ( $matches['CONTENT'] as $match ) {
					$sanitized_string = $this->sanitize_string( $match );
					if ( strpos( $sanitized_string, 'javascript:' )  !== false ) {
						$filename = $this->get_filename( $file_path );
						$lines = array_merge( $this->grep_content( $match, $file_content ), $lines );
						$this->add_error(
							'xss-javascript-in-style-tag',
							"XSS Attack Vector found in STYLE tag (javascript:)",
							'Warning',
							$filename,
							$lines
						);
						$result = false;
					}
				}
			}
		}

		return $result;
	}
}