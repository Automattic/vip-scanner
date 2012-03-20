<?php
class TimThumbCheck extends BaseCheck {
	const TIM_THUMB_CURRENT_VERSION = 1.19;

	function check( $files ) {
		$result = true;

		foreach ( $this->filter_files( $files, 'php' ) as $path => $content ) {
			$this->increment_check_count();

			if ( strpos( $content, 'cleanSource($src);') !== false ) {
				preg_match( "/define\s\('VERSION',\s'([0-9]\.[0-9]+)'\)/", $content, $matches );
				$version = $matches[1];
				$filename = $this->get_filename( $path );

				if ($version < self::TIM_THUMB_CURRENT_VERSION) {
					$this->add_error(
						'timthumb-outdated',
						sprintf( 'TimThumb detected in file. Version `%s` is out of date!', $version ),
						'warning',
						$filename
					);
					$result = false;
				} else {
					$this->add_error(
						'timthumb-outdated',
						sprintf( 'TimThumb detected in file. Version detected was `%s`.', $version ),
						'info',
						$filename
					);
				}
			}
		}
		return $result;
	}
}