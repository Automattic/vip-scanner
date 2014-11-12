<?php
/**
 * Checks for widgets and sidebars.
 */

class WidgetsCheck extends BaseCheck {

	function check( $files ) {
		$result = true;
		$php = $this->merge_files( $files, 'php' );

		/**
		 * Check if sidebars are registered, and if the proper hook is used.
		 */
		// Check if the theme registers sidebars.
		$this->increment_check_count();

		if ( false === strpos( $php, 'register_sidebar(' ) ) {
			$this->add_error(
				'widgets',
				'The theme does not seem to support Widgets.',
				BaseScanner::LEVEL_NOTE
			);
			$result = false;
		} else {
			// Sidebars are registered. Is the widgets_init action present?
			$this->increment_check_count();

			$widgets_init = false;
			foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
				if ( preg_match( '/add_action\((\s)*("|\')widgets_init("|\')(\s)*,/m', $file_content, $matches ) ) {
					$widgets_init = true;
				}
			}

			if ( ! $widgets_init ) {
				$this->add_error(
					'widgets',
					'<code>register_sidebar()</code> needs to be called on the <code>widgets_init</code> action.',
					BaseScanner::LEVEL_BLOCKER
				);
				$result = false;
			}
		}

		/**
		 * Check if there are registered sidebars with no sidebars being displayed in the templates.
		 */
		$this->increment_check_count();

		if ( false !== strpos( $php, 'register_sidebar' ) && false === strpos( $php, 'dynamic_sidebar' ) ) {
			$this->add_error(
				'widgets',
				'The theme uses <code>register_sidebar()</code>, but no <code>dynamic_sidebar()</code> was found.',
				BaseScanner::LEVEL_BLOCKER
			);
			$result = false;
		}

		/**
		 * Check if there are sidebars displayed in the template without being registered.
		 */
		$this->increment_check_count();

		if ( false === strpos( $php, 'register_sidebar' ) && false !== strpos( $php, 'dynamic_sidebar' ) ) {

			$this->add_error(
				'widgets',
				'The theme uses <code>dynamic_sidebar()</code>, but no <code>register_sidebar()</code> was found.',
				BaseScanner::LEVEL_BLOCKER
			);
			$result = false;
		}

        /**
         * Check if the theme registers new widgets.
         */
		$this->increment_check_count();

		if ( 0 !== preg_match_all( '/\sregister_widget\s*\(/', $php, $matches ) ) {
			$this->add_error(
				'widgets',
				'The theme uses <code>register_widget()</code> to register a new widget.',
				BaseScanner::LEVEL_WARNING
			);
			$result = false;
        }

        /**
         * Check if the theme unregisters widgets.
         */
		$this->increment_check_count();

		if ( false !== strpos( $php, 'unregister_widget' ) ) {
			$this->add_error(
				'widgets',
				'The theme uses <code>unregister_widget()</code>. Themes are not allowed to unregister widgets.',
				BaseScanner::LEVEL_BLOCKER
			);
			$result = false;
        }

		return $result;
	}
}
