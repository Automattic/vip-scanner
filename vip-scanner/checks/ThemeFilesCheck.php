<?php
class ThemeFilesCheck extends BaseCheck {

	function check( $files ) {

		$this->increment_check_count();

		$filenames = array();

		foreach ( $this->get_all_files( $files ) as $filename => $file_contents ) {
			array_push( $filenames, strtolower( basename( $filename ) ) );
		}

		$must_have = array( 'index.php', 'comments.php', 'screenshot.png', 'style.css' );
		$recommended = array( 'readme.txt' );

		foreach( $must_have as $file ) {
			if ( ! in_array( $file, $filenames ) ) {
				$this->add_error(
					'missing-file',
					sprintf( 'Could not find the file `%s` in the theme.', $file ),
					'warning'
				);
			}
		}

		foreach( $recommended as $file ) {
			if ( ! in_array( $file, $filenames ) ) {
				$this->add_error(
					'missing-file',
					sprintf( 'Could not find the file `%s` in the theme.', $file ),
					'note'
				);

				$this->error[] = "<span class='tc-lead tc-recommended'>RECOMMENDED</span>: could not find the file <strong>{$file}</strong> in the theme.";
			}
		}
		return $this->get_results();
	}
}