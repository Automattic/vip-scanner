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

		$this->assertContains( 'using-request-variable', $error_slugs );
	}

	public function testAssignedNormalVariables() {
		$file_contents = <<<'EOT'
				<?php

				$bbq = "is so tasty";

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertNotContains( 'using-request-variable', $error_slugs );
	}

	public function testReadingREQUESTVariables() {
		$file_contents = <<<'EOT'
				<?php

				if( $_REQUEST["lol"] == "hey" )
					echo "how are you?";

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'using-request-variable', $error_slugs );
	}


	public function testReadingNormalVariables() {
		$file_contents = <<<'EOT'
				'<?php

				if( $imnormal == "hey" )
					echo "how are you?";

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertNotContains( 'using-request-variable', $error_slugs );
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
				'output-of-restricted-variables',
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
				'output-of-restricted-variables',
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

		$this->assertContains( 'direct-query_vars-access', $error_slugs );
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

		$this->assertContains( 'direct-query_vars-modification', $error_slugs );
	}

	public function testVarVarsSingleQuoteStringLiteral() {
		$file_contents = <<<'EOT'
				<?php

				$test = '$$this is a test';

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertNotContains( 'variable-variables', $error_slugs );
	}

	public function testVarVarsDoubleQuoteStringLiteral() {
		$file_contents = <<<'EOT'
				<?php

				$test = "$$this is a test";

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertNotContains( 'variable-variables', $error_slugs );
	}

	public function testVarVarsStandardVariable() {
		$file_contents = <<<'EOT'
				<?php

				$bar = 'foo';

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertNotContains( 'variable-variables', $error_slugs );
	}

	public function testVarVarsComplexSyntax() {
		$file_contents = <<<'EOT'
				<?php

				$bar = 'foo';
				$foo = ${$bar};

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'variable-variables', $error_slugs );
	}

	public function testVarVarsStandard() {
		$file_contents = <<<'EOT'
				<?php

				$foo = 'foo';
				$bar = 'biz';
				$$foo = $bar;

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'variable-variables', $error_slugs );
	}

	public function testVarVarsDoubleQuoteStringLiteralInsideComplexSyntax() {
		$file_contents = <<<'EOT'
				<?php

				$one = 'biz';
				${"foo$one"} = 'bar';

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'variable-variables', $error_slugs );
	}

	public function testVarVarsConstantInsideComplexSyntax() {
		$file_contents = <<<'EOT'
				<?php

				define("ONE", "biz");
				${'foo' . ONE} = 'bar';

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'variable-variables', $error_slugs );
	}

	public function testVarVarsFunctionInsideComplexSyntax() {
		$file_contents = <<<'EOT'
				<?php

				function biz() {
					return 'foo';
				}
				${'foo' . one()} = 'bar';

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'variable-variables', $error_slugs );
	}

	public function testVarVarsVariableClassProperties() {
		$file_contents = <<<'EOT'
				<?php

				class Foo {
					public $foo = 'bar';
				}

				$bar = 'foo';
				$foo->$bar = 'foobar';

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertContains( 'variable-variables', $error_slugs );
	}

	public function testVarVarsStandardClassProperties() {
		$file_contents = <<<'EOT'
				<?php

				class Foo {
					public $bar = 'bar';
				}

				$foo->barr = 'foobar';

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertNotContains( 'variable-variables', $error_slugs );
	}
}
