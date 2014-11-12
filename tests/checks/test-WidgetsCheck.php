<?php

require_once( 'CheckTestBase.php' );

class WidgetsTest extends CheckTestBase {

	public function testRegisterWidgetExists() {
		$file = <<<'EOT'
<?php
function prefix_register_widget_areas() {
	register_sidebar( array(
		'id' => 'sidebar-1',
	) );

	register_widget( 'Widget_Class' );
}
add_action( 'widgets_init', 'prefix_register_widget_areas' );

dynamic_sidebar( 'sidebar-1' );
EOT;

		$error_slugs = $this->runCheck( $file );

		$this->assertContains( 'widgets', $error_slugs );
	}

	public function testRegisterWidgetInFunctionName() {
		$file = <<<'EOT'
<?php
function prefix_register_widget_areas() {
	register_sidebar( array(
		'id' => 'sidebar-1',
	) );
}
add_action( 'widgets_init', 'prefix_register_widget_areas' );

dynamic_sidebar( 'sidebar-1' );

// Additional variations with 'register_widget' in the name.
register_widget_suffix();
prefix_register_widget();
EOT;

		$error_slugs = $this->runCheck( $file );

		$this->assertNotContains( 'widgets', $error_slugs );
	}
}
