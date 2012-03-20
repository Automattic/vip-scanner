<?php
class HardcodedLinksCheck extends BaseCheck {
	function check( $files ) {

		$result = true;

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			$this->increment_check_count();

			// regex borrowed from TAC
			$url_regex ='([[:alnum:]\-\.])+(\\.)([[:alnum:]]){2,4}([[:blank:][:alnum:]\/\+\=\%\&\_\\\.\~\?\-]*)';
			$title_regex ='[[:blank:][:alnum:][:punct:]]*';	// 0 or more: any num, letter(upper/lower) or any punc symbol
			$space_regex ='(\\s*)';

			if ( preg_match_all( "/(<a)(\\s+)(href" . $space_regex . "=" . $space_regex . "\"" . $space_regex . "((http|https|ftp):\\/\\/)?)" . $url_regex . "(\"".$space_regex . $title_regex . $space_regex .">)" . $title_regex . "(<\\/a>)/is", $file_content, $out, PREG_SET_ORDER ) ) {
				$filename = $this->get_filename( $file_path );
				foreach( $out as $key ) {
					if ( $key[0] && !strpos( $key[0], 'wordpress.org' ) ) {
						$lines = $this->grep_content( $key[0], $file_content );
					}
				}
				if ( ! empty( $lines ) ) {
					$this->add_error(
						'hardcoded-links',
						'Possible hard-coded links were found in the file',
						'info',
						$filename,
						$lines
					);
				}
			}
		}
		return $result;
	}
}