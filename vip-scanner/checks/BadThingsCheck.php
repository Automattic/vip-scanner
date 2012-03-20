<?php
class BadThingsCheck extends BaseCheck {
	function check( $files ) {
		$result = true;

		$checks = array(
			'/[\s|]eval\s*\([^\$|\'](.){25}/i' => 'eval() is not allowed.',
			'/[\s](popen|proc_open|exec|shell_exec|system|passthru)\(/is' => 'PHP system calls should be disabled by server admins anyway!',
			'/base64_decode/ims' => '`base64_decode()` is not allowed',
			'/base64_encode/ims' => '`base64_encode()` is not allowed',
			'/uudecode/ims' => 'uudecode() is not allowed',
			'/str_rot13/ims' => 'str_rot13() is not allowed',
			'/cx=[0-9]{21}:[a-z0-9]{10}/ims' => 'Google search code detected',
			'/add_(admin|submenu|theme)_page\s?\x28.*,\s?[0-9]\s?,/i' => 'Please see [Roles and Capabilities](http://codex.wordpress.org/Roles_and_Capabilities)',
			'/pub-[0-9]{16}/i' => 'Google advertising code detected'
		);

		$php_files = $this->filter_files( $files, 'php' );

		foreach ( $php_files as $file_path => $file_content ) {
			foreach ( $checks as $key => $check ) {
			$this->increment_check_count();
				if ( preg_match( $key, $file_content, $matches ) ) {
					$filename = $this->get_filename( $file_path );
					$error = rtrim( $matches[0], '(' );
					$lines = $this->grep_content( $error, $file_content );
					$this->add_error(
						$key,
						sprintf( 'Found `%1$s` in the file. %2$s.', $error, $check ),
						'warning',
						$filename,
						$lines
					);
					$result = false;
				}
			}
		}


		$checks = array(
			'/cx=[0-9]{21}:[a-z0-9]{10}/ms' => 'Google search code detected',
			'/pub-[0-9]{16}/' => 'Google advertising code detected'
		);

		$other_files = array_diff( $this->get_all_files( $files ), $php_files );

		foreach ( $other_files as $file_path => $file_content ) {
			foreach ( $checks as $key => $check ) {
				$this->increment_check_count();
				if ( preg_match( $key, $file_content, $matches ) ) {
					$filename = tc_filename( $file_path );
					$error = rtrim( $matches[0], '(' );
					$lines = $this->grep_content( $error, $file_content );
					$this->add_error(
						$key,
						sprintf( 'Found `%1$s` in the file. %2$s.', $error, $check ),
						'warning',
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