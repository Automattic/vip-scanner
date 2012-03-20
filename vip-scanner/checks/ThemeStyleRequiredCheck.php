<?php
class ThemeStyleRequiredCheck extends BaseCheck {
	function check( $files ) {
		$result = true;

		$css = $this->merge_files( $files, 'css' );

		$checks = array(
			'^[ \t\/*#]*Theme Name:' => '`Theme name:` is missing from your style.css header.',
			'^[ \t\/*#]*Theme URI:' => '`Theme URI:` is missing from your style.css header.',
			'^[ \t\/*#]*Description:' => '`Description:` is missing from your style.css header.',
			'^[ \t\/*#]*Author:' => '`Author:` is missing from your style.css header.',
			'^[ \t\/*#]*Version' => '`Version:` is missing from your style.css header.',
			'^[ \t\/*#]*License:' => '`License:` is missing from your style.css header.',
			'^[ \t\/*#]*License URI:' => '`License URI:` is missing from your style.css header.',
			'\.alignleft' => '`.alignleft` css class is needed in your theme css.',
			'\.alignright' => '`.alignright` css class is needed in your theme css.',
			'\.aligncenter' => '`.aligncenter` css class is needed in your theme css.',
			'\.wp-caption' => '`.wp-caption` css class is needed in your theme css.',
			'\.wp-caption-text' => '`.wp-caption-text` css class is needed in your theme css.',
			'\.gallery-caption' => '`.gallery-caption` css class is needed in your theme css.',
		);

		foreach ( $checks as $key => $check ) {
			$this->increment_check_count();
			if ( ! preg_match( '/' . $key . '/mi', $css, $matches ) ) {
				$this->add_error(
					$key,
					$check,
					'required'
				);
				$result = false;
			}
		}
		return $result;
	}
}