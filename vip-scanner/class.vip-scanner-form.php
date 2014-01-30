<?php

class VIP_Scanner_Form {

	static $instance = false;

	const TRANSIENT_KEY = 'vip_scanner_flash_form_field';

	static $fields = array();
	static $labels = array();
	static $required = array();
	static $review = array();

	static function instance() {
		add_action( 'vip_scanner_form', array( __CLASS__, 'form' ) );
		add_action( 'vip_scanner_form_results', array( __CLASS__, 'vip_scanner_form_results' ) );
		add_action( 'vip_scanner_form_success', array( __CLASS__, 'delete_transient' ) );
		add_action( 'admin_notices', array( __CLASS__, 'vip_scanner_missing_required_fields' ) );
	}

	static function get_instance() {
		if ( ! self::$instance )
			self::instance();

		self::$instance = true;
	}

	public static function add_field( $type, $name, $label, $review, $required = false ) {
		self::get_instance();

		if ( in_array( $name, array_keys( self::$fields ) ) );

		self::$fields[$name] = $type;
		self::$labels[$name] = $label;
		self::$review[$name] = $review;

		if ( $required )
			self::$required[] = $name;
	}

	static function form() {
		$fields = get_transient( self::TRANSIENT_KEY ) ?: array();

		foreach ( self::$fields as $name => $type ) {
			if ( ! self::is_review_type( self::$review[$name] ) )
				continue;
			?>
			<p class="<?php self::required( $name ); ?>">
				<label>
					<?php

						$required = sprintf( '<small class="description require-label">%s</small>',  __( '(required)', 'theme-check' ) );
						$maybe_required = in_array( $name, self::$required ) ? $required : '';

						switch ( $type ) {
							case 'textarea':
								$value = isset( $fields[ $name ] ) ? $fields[ $name ] : null;

								echo esc_html( self::$labels[$name] ) . ': ' . $maybe_required . '<br>';
								echo "<textarea name='$name'>" . esc_textarea( $value ) . "</textarea>";
								break;

							case 'checkbox':
								$checked = checked( isset( $fields[$name] ) && $fields[$name], true, false );
								echo "<input type='$type' name='$name' $checked> ";
								echo esc_html( self::$labels[$name] ) . ': ' . $maybe_required ;
								break;

							default:
								$value = isset( $fields[$name] ) ? sanitize_text_field( $fields[$name] ) : '';
								echo esc_html( self::$labels[$name] ) . ': ' . $maybe_required . '<br>';
								echo "<input type='$type' name='$name' value='$value'>";
						}
					?>
				</label>
			</p>
			<?php
		}
	}

	static function vip_scanner_form_results( $results ) {
		foreach ( self::$required as $name ) {
			if ( ! self::is_review_type( self::$review[$name] ) )
				continue;

			if ( ! isset( $_POST[$name] ) ) {
				$url = add_query_arg( array(
					'page' => 'vip-scanner',
					'message' => 'fill-required-fields',
					'vip-scanner-review-type' => urlencode( $_REQUEST['review'] ),
				) );

				set_transient( self::TRANSIENT_KEY, array_intersect_key( $_POST, self::$fields ) );
				wp_safe_redirect( $url );
				exit;
			}
		}

		foreach( self::$labels as $name => $label ) {
			if ( ! self::is_review_type( self::$review[$name] ) )
				continue;

			$results .= '## ' . $label . PHP_EOL;
			$results .= $_POST[$name] . PHP_EOL . PHP_EOL;
		}

		return $results;
	}

	static function vip_scanner_missing_required_fields() {
		if ( ! isset( $_GET['page'], $_GET['message'] ) || 'vip-scanner' != $_GET['page'] || 'fill-required-fields' != $_GET['message'] )
			return;
	    ?>
	    <div class="error">
	        <p><strong><?php _e( 'Warning! Fill the required fields before submitting the form.', 'theme-check' ); ?></strong></p>
	    </div>
	    <?php
	}

	private static function required( $name ) {
		$fields = get_transient( self::TRANSIENT_KEY ) ?: array();

		if ( ! isset( $_GET['message'] ) || 'fill-required-fields' != $_GET['message'] )
			return;

		if ( ! ( in_array( $name, self::$required ) && empty( $fields[$name] ) ) )
			return;

		echo "required";
	}

	private static function is_review_type( $type ) {
		global $vip_scanner;
		$review_types = VIP_Scanner::get_instance()->get_review_types();
		$cur = isset( $_REQUEST['vip-scanner-review-type'] ) ? $_REQUEST['vip-scanner-review-type'] : $review_types[$vip_scanner->default_review];
		return $cur == $type;
	}

	public static function delete_transient() {
		delete_transient( self::TRANSIENT_KEY );
	}

}
