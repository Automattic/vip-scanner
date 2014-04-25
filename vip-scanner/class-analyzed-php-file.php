<?php

class AnalyzedPHPFile extends AnalyzedFile {
	protected $filepath = '';
	protected $filecontents = '';
	protected $processed_file_contents = '';

	/**
	 * Analyzes this file.
	 */
	protected function analyze_file() {
		// Load the contents of the file
		if ( is_null( $this->filecontents ) || ! $this->filecontents ) {
			$this->filecontents = file_get_contents( $this->filepath );
		}
		
		if ( false === $this->filecontents ) {
			return;
		}

		// Parse the tokens
		require_once( VIP_SCANNER_DIR . '/class-token-parser.php' );
		$parser = new TokenParser();
		$items = $parser->parse_contents( $this->filecontents );

		// Parse the items
		$this->hierarchy_elements = array();
		$this->parse_token_results( $items );
	}

	protected function get_strings_and_comments_regexes() {
		return array();
	}

	private function parse_token_results( $items ) {
		foreach ( $items as $item ) {
			$type = '';
			switch ( $item['type'] ) {
				case 'class':
					$type = 'classes';
					break;

				case 'const':
					$type = 'constants';
					break;

				default:
					$type = $item['type'] . 's';
			}

			if ( !isset( $this->hierarchy_elements[$type] ) ) {
				$this->hierarchy_elements[$type] = array();
			}

			if ( !isset( $this->hierarchy_elements[$type][$item['path']] ) ) {
				$this->hierarchy_elements[$type][$item['path']] = array();
			}

			// There's a chance for duplicate items that are significant. Ie: two calls to one function within a block of code.
			if ( isset( $this->hierarchy_elements[$type][$item['path']][$item['name']] ) ) {
				if ( isset( $this->hierarchy_elements[$type][$item['path']][$item['name']][0] ) ) {
					$this->hierarchy_elements[$type][$item['path']][$item['name']][] = $item;
				} else {
					$this->hierarchy_elements[$type][$item['path']][$item['name']] = array(
						$this->hierarchy_elements[$type][$item['path']][$item['name']],
						$item,
					);
				}
			} else {
				$this->hierarchy_elements[$type][$item['path']][$item['name']] = $item;
			}

			if ( !empty( $item['children'] ) ) {
				$this->parse_token_results( $item['children'] );
			}
		}
	}
}