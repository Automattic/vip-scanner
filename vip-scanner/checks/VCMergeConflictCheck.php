<?php

class VCMergeConflictCheck extends BaseCheck {
	protected $checks = array(
		'merge-conflict' => '/(<{4,}\W+(?<your_side>\w+)[\s\S]*>{4,}\W+(?<their_side>\w+))/im',
		'conflict_file'  => '/mine|r[0-9]+/im'
	);

	function check( $files ) {
		$result = true;

		foreach ( $files as $files_of_type ) {
			foreach ( $files_of_type as $file_path => $file_content ) {
				$file_extension = pathinfo( $file_path, PATHINFO_EXTENSION );

				$this->increment_check_count();
				if ( preg_match($this->checks['conflict_file'], $file_extension ) ) {
					$this->add_error(
						'vcs_merge_conflict_file',
						__( 'Possible version control merge conflict file found', 'vip-scanner' ),
						BaseScanner::LEVEL_WARNING,
						$this->get_filename( $file_path )
					);
				}

				$this->increment_check_count();
				if ( preg_match( $this->checks['merge-conflict'], $file_content, $matches ) ) {
					$this->add_error(
						'vcs_merge_conflict',
						__( 'Version control merge conflict', 'vip-scanner' ),
						BaseScanner::LEVEL_BLOCKER,
						$this->get_filename( $file_path ),
						explode( "\n", esc_html( $matches[0] ) )
					);

					$result = false;
				}
			}
		}

		return $result;
	}
}