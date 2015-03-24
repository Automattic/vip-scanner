<?php

require_once( 'CheckTestBase.php' );

class VIPRestrictedVariablesTest extends CheckTestBase {

	public function testSingleQuoteStringLiteral() {
		$file_contents = <<<'EOT'
				<?php

				$test = '$$this is a test';

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertNotRegExp(
			'((?<![\\\'\"])\$\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*?(?![\\\'\"])|(?<![\\\'\"])\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*?\-\>\$(?![\\\'\"])|(?<![\\\'\"])\$\{(?:.*)[\}](?![\\\'\"]))',
			$file_contents
		);
	}

	public function testDoubleQuoteStringLiteral() {
		$file_contents = <<<'EOT'
				<?php

				$test = "$$this is a test";

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertNotRegExp(
			'((?<![\\\'\"])\$\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*?(?![\\\'\"])|(?<![\\\'\"])\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*?\-\>\$(?![\\\'\"])|(?<![\\\'\"])\$\{(?:.*)[\}](?![\\\'\"]))',
			$file_contents
		);
	}

	public function testComplexSyntaxVariableVariable() {
		$file_contents = <<<'EOT'
				<?php

				$bar = 'foo';
				$foo = ${$bar};

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertRegExp(
			'((?<![\\\'\"])\$\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*?(?![\\\'\"])|(?<![\\\'\"])\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*?\-\>\$(?![\\\'\"])|(?<![\\\'\"])\$\{(?:.*)[\}](?![\\\'\"]))',
			$file_contents
		);
	}

	public function testVariableVariable() {
		$file_contents = <<<'EOT'
				<?php

				$foo = 'foo';
				$bar = 'biz';
				$$foo = $bar;

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertRegExp(
			'((?<![\\\'\"])\$\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*?(?![\\\'\"])|(?<![\\\'\"])\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*?\-\>\$(?![\\\'\"])|(?<![\\\'\"])\$\{(?:.*)[\}](?![\\\'\"]))',
			$file_contents
		);
	}

	public function testDoubleQuoteStringLiteralInsideComplexSyntaxVariableVariable() {
		$file_contents = <<<'EOT'
				<?php

				$one = 'biz';
				${"foo$one"} = 'bar';

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertRegExp(
			'((?<![\\\'\"])\$\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*?(?![\\\'\"])|(?<![\\\'\"])\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*?\-\>\$(?![\\\'\"])|(?<![\\\'\"])\$\{(?:.*)[\}](?![\\\'\"]))',
			$file_contents
		);
	}

	public function testConstantInsideComplexSyntaxVariableVariable() {
		$file_contents = <<<'EOT'
				<?php

				define("ONE", "biz");
				${'foo' . ONE} = 'bar';

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertRegExp(
			'((?<![\\\'\"])\$\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*?(?![\\\'\"])|(?<![\\\'\"])\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*?\-\>\$(?![\\\'\"])|(?<![\\\'\"])\$\{(?:.*)[\}](?![\\\'\"]))',
			$file_contents
		);
	}

	public function testFunctionInsideComplexSyntaxVariableVariable() {
		$file_contents = <<<'EOT'
				<?php

				function biz() {
					return 'foo';
				}
				${'foo' . one()} = 'bar';

				?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );

		$this->assertRegExp(
			'((?<![\\\'\"])\$\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*?(?![\\\'\"])|(?<![\\\'\"])\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*?\-\>\$(?![\\\'\"])|(?<![\\\'\"])\$\{(?:.*)[\}](?![\\\'\"]))',
			$file_contents
		);
	}

	public function testVariableClassProperties() {
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

		$this->assertRegExp(
			'((?<![\\\'\"])\$\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*?(?![\\\'\"])|(?<![\\\'\"])\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*?\-\>\$(?![\\\'\"])|(?<![\\\'\"])\$\{(?:.*)[\}](?![\\\'\"]))',
			$file_contents
		);
	}

}
