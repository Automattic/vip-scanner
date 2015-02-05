<?php

require_once( 'CheckTestBase.php' );

class VIPRestrictedPatternsTest extends CheckTestBase {

	public function testAssigningREQUESTVariable() {
		$file_contents = <<<'EOT'
				<?php

				$normal = "Hey";

				$_REQUEST["lol"] = "dont do this";

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( '/(\$_REQUEST)+/msiU', $error_slugs );
	}

	public function testAssignedNormalVariables() {
		$file_contents = <<<'EOT'
				<?php

				$bbq = "is so tasty";

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertNotContains( '/(\$_REQUEST)+/msiU', $error_slugs );
	}

	public function testReadingREQUESTVariables() {
		$file_contents = <<<'EOT'
				<?php

				if( $_REQUEST["lol"] == "hey" )
					echo "how are you?";

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( '/(\$_REQUEST)+/msiU', $error_slugs );
	}


	public function testReadingNormalVariables() {
		$file_contents = <<<'EOT'
				'<?php

				if( $imnormal == "hey" )
					echo "how are you?";

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertNotContains( '/(\$_REQUEST)+/msiU', $error_slugs );
	}

	public function testOutputRestrictedVariables() {
		$patterns = array(
			'echo $_GET["foo"];',
			'echo $_GET["var"];',
			'echo( $_GET["foo"] );',
			'echo $_GET;',
			'print $_GET;',
			'echo $_POST;',
			'echo $GLOBALS;',
			'echo $_SERVER;',
			'echo $_REQUEST;',
			'echo "Hello, " . $_GET["name"]',
			'sprintf( "Hello, %s", $_GET["name"] );'
		);

		foreach ( $patterns as $pattern ) {
			$file_contents = <<<EOT
			<?php

			{$pattern}

			?>
EOT;

			$this->assertContains(
				'/(echo|\<\?\=)+(?!\s+\(?\s*(?:isset|typeof)\(\s*)[^;]+(\$GLOBALS|\$_SERVER|\$_GET|\$_POST|\$_REQUEST)+/msiU',
				$this->runCheck( $file_contents )
			);
		}
	}

	// Patterns that shouldn't set off red flags
	public function testOutputRestrictedVariablesFalsePositives() {
		$patterns = array(
			'echo $foo; isset( $_GET["bar"] );',
			'echo isset( $_GET ) ? "foo" : "bar";',
			'echo typeof( $_GET ) == "array" ? "yes" : "no";',
			'echo ( isset( $_GET["checked"] ) ? "is" : "not";'
		);

		foreach ( $patterns as $pattern ) {
			$file_contents = <<<EOT
			<?php

			{$pattern}

			?>
EOT;

			$this->assertNotContains(
				'/(echo|\<\?\=)+(?!\s+\(?\s*(?:isset|typeof)\(\s*)[^;]+(\$GLOBALS|\$_SERVER|\$_GET|\$_POST|\$_REQUEST)+/msiU',
				$this->runCheck( $file_contents )
			);
		}
	}

	public function testQueryVarsDirectAccessGet() {
		$file_contents = <<<'EOT'
			<?php

			add_action( "init", function(){
				global $wp_query;
				$paged = $wp_query->query_vars["paged"];
			} );
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( '/\$wp_query->query_vars\[.*?\][^=]*?\;/msi', $error_slugs );
	}

	public function testQueryVarsDirectAccessSet() {
		$file_contents = <<<'EOT'
			<?php

			add_action( "init", function(){
				global $wp_query;
				$wp_query->query_vars["paged"] = 3;
			} );
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( '/\$wp_query->query_vars\[.*?\]\s*?\=.*?\;/msi', $error_slugs );
	}
}
