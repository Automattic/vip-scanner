<?php

require_once( 'CheckTestBase.php' );

class VIPRestrictedClassesTest extends CheckTestBase {

	public function testWPClasses() {
		$class_name = 'WP_User_Query';

		$file_contents = '<?php $dummy = new ' . $class_name . '( $args )';

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( $class_name, $error_slugs );
	}
}