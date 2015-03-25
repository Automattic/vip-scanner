<?php

class VIPRestrictedPatternsCheck extends BaseCheck
{
	function check( $files ) {
		$result = true;

		$checks = array(
			'using-isie-variable' => array(
				'expression' => '/(\$isIE)+/msiU',
				'level'      => 'Warning',
				'note'       => 'Using $isIE conflicts with full page caching',
			),
			'using-request-variable' => array(
				'expression' => '/(\$_REQUEST)+/msiU',
				'level'      => 'Blocker',
				'note'       => 'Using $_REQUEST is forbidden. You should use $_POST or $_GET',
			),
			'missing-dotcom-from-vip' => array(
				'expression' => '/WordPress VIP/msiU',
				'level'      => 'Warning',
				'note'       => 'Please use "WordPress.com VIP" rather than "WordPress VIP"',
			),
			'database-table-alteration' => array(
				'expression' => '/(\$wpdb->|mysql_)+.+(ALTER)+\s+/msiU',
				'level'      => 'Blocker',
				'note'       => 'Possible database table alteration',
			),
			'database-table-creation' => array(
				'expression' => '/(\$wpdb->|mysql_)+.+(CREATE)+\s+/msiU',
				'level'      => 'Blocker',
				'note'       => 'Possible database table creation',
			),
			'database-table-deletion' => array(
				'expression' => '/(\$wpdb->|mysql_)+.+(DROP)+\s+/msiU',
				'level'      => 'Blocker',
				'note'       => 'Possible database table deletion',
			),
			'database-delete-query' => array(
				'expression' => '/(\$wpdb->|mysql_)+.+(DELETE)+\s+(FROM)+\s+/msiU',
				'level'      => 'Warning',
				'note'       => 'Direct database delete query',
			),
			'database-select-query' => array(
				'expression' => '/(\$wpdb->|mysql_)+.+(SELECT)+\s.+/msiU',
				'level'      => 'Note',
				'note'       => 'Direct Database select query',
			),
			'direct-database-query' => array(
				'expression' => '/(^GLOBAL)(\$wpdb->|mysql_)+/msiU',
				'level'      => 'Warning',
				'note'       => 'Possible direct database query',
			),
			'output-of-restricted-variables' => array(
				'expression' => '/(echo|\<\?\=)+(?!\s+\(?\s*(?:isset|typeof)\(\s*)[^;]+(\$GLOBALS|\$_SERVER|\$_GET|\$_POST|\$_REQUEST)+/msiU',
				'level'      => 'Warning',
				'note'       => 'Possible output of restricted variables',
			),
			'working-with-superglobals' => array(
				'expression' => '/(\$GLOBALS|\$_SERVER|\$_GET|\$_POST|\$_REQUEST)+/msiU',
				'level'      => 'Note',
				'note'       => 'Working with superglobals',
			),
			'non-whitelisted-server-superglobal' => array(
				'expression' => '/(\$_SERVER\[(?!(\'|"REQUEST_URI|SCRIPT_FILENAME|HTTP_HOST\'|"))([^]]+|)\])+/msiU',
				'level'      => 'Blocker',
				'note'       => 'Non whitelisted $_SERVER superglobals found in this file',
			),
			'unsafe-pre_option-hook-use' => array(
				'expression' => '/(pre_)?option_(blogname|siteurl|post_count)/msiU',
				'level'      => 'Blocker',
				'note'       => 'possible unsafe use of pre_option_* hook',
			),
			'direct-query_vars-access' => array(
				'expression' => '/\$wp_query->query_vars\[.*?\][^=]*?\;/msi',
				'level'      => 'Warning',
				'note'       => 'Possible direct query_vars access, should use get_query_var() function',
			),
			'direct-query_vars-modification' => array(
				'expression' => '/\$wp_query->query_vars\[.*?\]\s*?\=.*?\;/msi',
				'level'      => 'Warning',
				'note'       => 'Possible direct query_vars modification, should use set_query_var() function',
			),
			'variable-variables' => array(
				/*
					This regex is a bit complicated, so I will break it down:
					\$\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*?
					This is matching simple variable variables: $$foo = 'bar';
					It searches for a double dollar sign ($$), followed by a valid PHP variable name.

					\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*?\-\>\$
					This is matching variable class properties: $foo->$bar = 'foobar';
					It searches for a single dollar sign ($), followed by a valid PHP variable name, followed by an object operator (->), followed by a single dollar sign ($).

					\$\{(?:.*)[\}]
					This is matching complex syntax variable variables.
					It searches for a single dolalr sign ($), followed by opening and closing curly braces ({}) with anything inside them.

					(?<![\'\"]) ... (?![\'\"])
					Each of these searches is surrounded by a pair of lookarounds that tell the regex not to search inside string literals where variable variables do not work.
				*/
				'expression' => '/((?<![\\\'\"])\$\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*?(?![\\\'\"])|(?<![\\\'\"])\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*?\-\>\$(?![\\\'\"])|(?<![\\\'\"])\$\{(?:.*)[\}](?![\\\'\"]))/msiU',
				'level'      => 'Warning',
				'note'       => 'Possible PHP variable variables',
			),

		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			foreach ( $checks as $check => $check_info ) {
				$this->increment_check_count();

				if ( preg_match_all( $check_info['expression'], $file_content, $matches ) ) {
					$filename = $this->get_filename( $file_path );
					$lines = array();
					foreach ( $matches[0] as $match ) {
						$lines = array_merge( $this->grep_content( $match, $file_content ), $lines );
					}
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
}
