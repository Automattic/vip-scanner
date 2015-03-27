<?php
/**
 * Checks for different types of XSS vectors
 *
 */
class XSSVectorsCheck extends BaseCheck {
	function sanitize_string( $string ) {

		/**
		 * Removes comment blocks
		 */
		$string = preg_replace( '/(\/\(*(?:.*)?\*\/)/misU', '', $string );

		/**
		 * Removes slashes
		 */
		$string = str_replace( array( '/', '\\' ), '', $string );

		/**
		 * Removes encoded tabs (&#x09;), newlines (&#x0A;), and carriage returns (&#x0D;)
		 */
		$string = str_replace( array( '&#x09;', '&#x0A;', '&#x0D;' ), '', $string );

		/**
		 * Removes null characters
		 */
		$string = str_replace( "\0", "", $string );

		/**
		 * Removes all whitespace
		 */
		$string = preg_replace( '/(\s)/misU', '', $string );

		return $string;
	}

	function check( $files ) {
		$result = true;

		$checks = array(
			'xss-in-base-tag-href' => array(
				'expression' => '/<[\s]*?base(?:[^\>]*?)href(?:\s)*?=[\s]*?(?<MATCHTEXT>.*)(?=\s|=|>)/ims',
				'match-text' => 'javascript:',
				'level'      => 'Warning',
				'note'       => 'XSS Attack Vector found in <base> tag href attribute (javascript:)',
			),
			'xss-in-link-tag-href' => array(
				'expression' => '/<[\s]*?link(?:[^\>]*?)href(?:\s)*?=[\s]*?(?<MATCHTEXT>.*)(?=\s|=|>)/ims',
				'match-text' => 'javascript:',
				'level'      => 'Warning',
				'note'       => 'XSS Attack Vector found in <link> tag href attribute (javascript:)',
			),
			'xss-in-meta-tag-content' => array(
				'expression' => '/<[\s]*?meta(?:[^\>]*?)content(?:\s)*?=[\s]*?(?<MATCHTEXT>.*)(?=>)/ims',
				'match-text' => 'javascript:',
				'level'      => 'Warning',
				'note'       => 'XSS Attack Vector found in <meta> tag content attribute (javascript:)',
			),
			'xss-javascript-in-any-tag-src' => array(
				'expression' => '/<[\s]*?[a-z]*(?:[^\>]*?)src(?:\s)*?=[\s]*?(?<MATCHTEXT>.*)(?=\s|=|>)/ims',
				'match-text' => 'javascript:',
				'level'      => 'Warning',
				'note'       => 'XSS Attack Vector found in HTML tag src attribute (javascript:)',
			),
			'xss-vbscript-in-any-tag-src' => array(
				'expression' => '/<[\s]*?[a-z]*(?:[^\>]*?)src(?:\s)*?=[\s]*?(?<MATCHTEXT>.*)(?=\s|=|>)/ims',
				'match-text' => 'vbscript:',
				'level'      => 'Warning',
				'note'       => 'XSS Attack Vector found in HTML tag src attribute (vbscript:)',
			),
			'xss-livescript-in-any-tag-src' => array(
				'expression' => '/<[\s]*?[a-z]*(?:[^\>]*?)src(?:\s)*?=[\s]*?(?<MATCHTEXT>.*)(?=\s|=|>)/ims',
				'match-text' => 'livescript:',
				'level'      => 'Warning',
				'note'       => 'XSS Attack Vector found in HTML tag src attribute (livescript:)',
			),
			'xss-javascript-in-style-tag' => array(
				'expression' => '/<[\s]*?style(?:.*?)?>(?<MATCHTEXT>.*?)<[\s]*?\/[\s]*?[a-z]*?[\s]*?>/ims',
				'match-text' => 'javascript:',
				'level'      => 'Warning',
				'note'       => 'XSS Attack Vector found in <style> tag (javascript:)',
			),
			'xss-javascript-in-style-attribute' => array(
				'expression' => '/<[\s]*?[a-z]*(?:[^\>]*?)style(?:\s)*?=[\s]*?(?<MATCHTEXT>.*)(>)/ims',
				'match-text' => 'javascript:',
				'level'      => 'Warning',
				'note'       => 'Possible XSS Attack Vector found in HTML tag style attribute (javascript:)',
			),
			'xss-unicode-obfuscated-javascript-in-style-attribute' => array(
				'expression' => '/<[\s]*?[a-z]*(?:[^\>]*?)style(?:\s)*?=[\s]*?(?<MATCHTEXT>.*)(>)/ims',
				'match-text' => '006a006100760061007300630072006900700074003a', // Unicode escaped 'javascript:' minus the slashes that get sanitized out.
				'level'      => 'Warning',
				'note'       => 'Possible XSS Attack Vector found in HTML tag style attribute (unicode obfuscated javascript:)',
			),
			'xss-in-background-attribute' => array(
				'expression' => '/<[\s]*?[a-z]*(?:[^\>]*?)background(?:\s)*?=[\s]*?(?<MATCHTEXT>.*)(?=\s|=|>)/ims',
				'match-text' => 'javascript:',
				'level'      => 'Warning',
				'note'       => 'Possible XSS Attack Vector found in HTML tag background attribute (javascript:)',
			),
			'moz-binding-xss-in-style-tag' => array(
				'expression' => '/<[\s]*?style(?:.*?)?>(?<MATCHTEXT>.*?)<[\s]*?\/[\s]*?[a-z]*?[\s]*?>/ims',
				'match-text' => '-moz-binding',
				'level'      => 'Warning',
				'note'       => 'XSS Attack Vector found in <style> tag (-moz-binding)',
			),
			'moz-binding-xss-in-style-attribute' => array(
				'expression' => '/<[\s]*?[a-z]*(?:[^\>]*?)style(?:\s)*?=[\s]*?(?<MATCHTEXT>.*)(>)/ims',
				'match-text' => '-moz-binding',
				'level'      => 'Warning',
				'note'       => 'XSS Attack Vector found in HTML tag style attribute (-moz-binding)',
			),
			'css-expression-xss-in-style-attribute' => array(
				'expression' => '/<[\s]*?[a-z]*(?:[^\>]*?)style(?:\s)*?=[\s]*?(?<MATCHTEXT>.*)(>)/ims',
				'match-text' => 'expression(',
				'level'      => 'Warning',
				'note'       => 'XSS Attack Vector found in HTML tag style attribute (CSS expression property)',
			),
			'css-expression-xss-in-style-tag' => array(
				'expression' => '/<[\s]*?style(?:.*?)?>(?<MATCHTEXT>.*?)<[\s]*?\/[\s]*?[a-z]*?[\s]*?>/ims',
				'match-text' => 'expression(',
				'level'      => 'Warning',
				'note'       => 'XSS Attack Vector found in <style> tag  (CSS expression property)',
			),
			'css-behavior-xss-in-style-attribute' => array(
				'expression' => '/<[\s]*?[a-z]*(?:[^\>]*?)style(?:\s)*?=[\s]*?(?<MATCHTEXT>.*)(>)/ims',
				'match-text' => 'behavior:',
				'level'      => 'Warning',
				'note'       => 'Possible XSS Attack Vector found in HTML tag style attribute (CSS behavior property)',
			),
			'css-behavior-xss-in-style-tag' => array(
				'expression' => '/<[\s]*?style(?:.*?)?>(?<MATCHTEXT>.*?)<[\s]*?\/[\s]*?[a-z]*?[\s]*?>/ims',
				'match-text' => 'behavior:',
				'level'      => 'Warning',
				'note'       => 'XSS Attack Vector found in <style> tag  (CSS behavior property)',
			),
			'malformed-img-tag-xss-script' => array(
				'expression' => '/<[\s]*?img(?:[^\>]*?)[\s]*?(?:\"\"\"|\\\'\\\'\\\'|\`\`\`)[\s]*?>[\s]*?(?<MATCHTEXT>.*)(?:\"|\\\'|\`)[\s]*?>/ims',
				'match-text' => 'script',
				'level'      => 'Warning',
				'note'       => 'XSS Attack Vector found in malformed <img> tag (<script> tag)',
			),
			'xss-in-img-dynsrc-attr' => array(
				'expression' => '/<[\s]*?img*(?:[^\>]*?)dynsrc(?:\s)*?=[\s]*?(?<MATCHTEXT>.*)(?=\s|=|>)/ims',
				'match-text' => 'javascript:',
				'level'      => 'Warning',
				'note'       => 'XSS Attack Vector found in <img> tag (dynsrc attribute)',
			),
			'xss-in-img-lowsrc-attr' => array(
				'expression' => '/<[\s]*?img*(?:[^\>]*?)lowsrc(?:\s)*?=[\s]*?(?<MATCHTEXT>.*)(?=\s|=|>)/ims',
				'match-text' => 'javascript:',
				'level'      => 'Warning',
				'note'       => 'XSS Attack Vector found in <img> tag (lowsrc attribute)',
			),
			'xss-object-type-x-scriptlet' => array(
				'expression' => '/<[\s]*?object(?:[^\>]*?)type(?:\s)*?=[\s]*?(?<MATCHTEXT>.*)(?=\s|=|>)/ims',
				'match-text' => 'x-scriptlet',
				'level'      => 'Warning',
				'note'       => 'XSS Attack Vector found in <object> tag (x-scriptlet)',
			),
		);

		foreach ( $this->filter_files( $files, array( 'html', 'php' ) ) as $file_path => $file_content ) {
			foreach ( $checks as $check => $check_info ) {
				$this->increment_check_count();
				if ( preg_match_all( $check_info['expression'], $file_content, $matches ) ) {
					$lines = array();
					foreach ( $matches['MATCHTEXT'] as $match ) {
						$sanitized_string = $this->sanitize_string( $match );
						if ( stripos( $sanitized_string, $check_info['match-text'] )  !== false ) {
							$filename = $this->get_filename( $file_path );
							$lines = array_merge( $this->grep_content( $match, $file_content ), $lines );
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
			}
		}

		return $result;
	}
}