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
