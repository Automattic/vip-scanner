<?php

require_once( 'CheckTestBase.php' );

class CheckedSelectedDisabledTest extends CheckTestBase {
	public function testInvalidChecked() {
		$file_contents = <<<'EOT'
		<li class="inline">
			<input type="checkbox" name="sendCopy" id="sendCopy" value="true"<?php if( isset( $_POST['sendCopy'] ) && $_POST['sendCopy'] == true ) { echo ' checked="checked"'; } ?> />
			<label for="sendCopy"><?php _e( 'Send a copy of this email to yourself', 'theme-slug' ); ?></label>
		</li>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'invalid-checked', $error_slugs );
	}

	public function testInvalidSelected() {
		$file_contents = <<<'EOT'
			$html .= '<select name="' . esc_attr( $k ) . '" id="' . esc_attr( $k ) . '" class="select">' . "\n";
			foreach ( $v['options'] as $value => $label ) {
				$selected = '';
				if ( $value == ${$k} ) { $selected = ' selected="selected"'; } // End IF Statement
					$html .= '<option value="' . esc_attr( $value ) . '"' . $selected . '>' . esc_html( $label ) . '</option>' . "\n";
				}
			$html .= '</select></p>' . "\n";
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'invalid-selected', $error_slugs );
	}

	public function testInvalidDisabled() {
		$file_contents = <<<'EOT'
			$output .= '<textarea ' . ( ! current_user_can( 'unfiltered_html' ) && in_array( $value['id'], theme_disabled_if_not_unfiltered_html_option_keys() ) ? 'disabled="disabled" ' : '' ) . name="'. esc_attr( $value['id'] ) .'">'.esc_textarea( $ta_value ).'</textarea>';
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'invalid-disabled', $error_slugs );
	}

}