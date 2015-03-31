Filing an Issue
---------------

If you've found something in a WordPress theme or plugin that should be reported by VIP-scanner, please file an issue with some sample code.

Adding a New Check
------------------

Adding a new VIP-scanner check can be considered a testboook example of [Test-driven development](https://en.wikipedia.org/wiki/Test-driven_development) (TDD). Mind the difference between a test (as in TDD) and a check, which is a sort of code sniff specific to VIP-scanner, used for scanning a theme or plugin for bad code. We'll a start by a test that will ensure the functionality of our check.

Here's a recipe:

1. Think of a suitable name for your check. In this example, which is a simplified version of a check that actually ships with vip-scanner, we're going to add a check that scans files for ill-formed escaping, i.e. `esc_attr` being wrapped around `print` or `printf` statements instead of the other way round. We'll name our check class `EscapingCheck`.
2. Create a file to hold some erroneous code in vip-scanner/tests/data. The file should be named like the check, only with `Check` replaced by `Test`; also, use `inc` instead of `php` for the file extension -- so in our example, EscapingTest.inc.
3. Put the following code into that file:

```php
<?php

die(); //Don't actually run the following code.
```

4. Add the erroneous code the check should scan for after that:

```php
<?php

die(); //Don't actually run the following code.

esc_attr( printf( 'unescaped string' ) );
esc_attr( print 'unescaped string' );
```

5. Create a test file to cover your check in vip-scanner/tests/checks/. Name it like your check, prepended with 'test-': test-EscapingCheck.php. At the top of the file, put `require_once( 'CodeCheckTestBase.php' )` -- this file contains the `CodeCheckTestBase` class which is basically a wrapper around PHPUnit which adds some nifty extra features. Create a class that `extends CodeCheckTestBase`, and name it like your check, but ending in `Test` instead of `Check` (this is actually important for CodeCheckTestBase to work). For each different type of error your check is going to scan for, add a method to that class. As with PHPUnit, those methods need to be prefixed with `test` and use methods like `$this->assertEqual()` to make assertions for which PHPUnit checks.

6. Each test method contains of three parts:

	1. An array of expected errors
	2. The actual check, which returns an array of actual errors found in your 'inc' file.
	3. An assertion comparing those two arrays.

7. At this point, you should put some thought into finding a slug and description for the error you're diagnosing, and what error level to assign to it -- the latter can be blocker, warning, and note. Use that data in the `$expected_errors` array like so:

```php
<?php

require_once( 'CodeCheckTestBase.php' );

class EscapingTest extends CodeCheckTestBase {

	public function testEscaping() {
		$expected_errors = array(
			array( 'slug' => 'functions-file',
				'level' => BaseScanner::LEVEL_BLOCKER,
				'description' => sprintf(
					__( 'The function %1$s is being passed as the first parameter of %2$s. This is problematic because %1$s echoes a string which will not be escaped by %2$s.', 'vip-scanner' ),
					'<code>printf()</code>',
					'<code>esc_attr()</code>'
				),
				'file' => 'EscapingTest.inc',
				'lines' => 5,
			),
			array( 'slug' => 'functions-file',
					'level' => BaseScanner::LEVEL_BLOCKER,
					'description' => sprintf(
							__( '%1$s is being passed as the first parameter of %2$s.', 'vip-scanner' ),
							'<code>print</code>',
							'<code>esc_attr()</code>'
					),
					'file' => 'EscapingTest.inc',
					'lines' => 6,
			),
		);
		$actual_errors = $this->checkFile( 'EscapingTest.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}
}
```

The 'file' and 'lines' values state where the check is supposed to find the erroneous code -- compare that to the EscapingTest.inc sample code above.

7. From the top-level vip-scanner directory, run `phpunit`. Your test should fail because no class named `EscapingCheck` exists yet and thus can be found by `CodeCheckTestBase` -- not because of some syntax errors in your test file!

8. Create your check file in vip-scanner/vip-scanner/checks/. In our case, that's `EscapingCheck.php`. Start with an explanatory commentary and the following stub:

```php
<?php
/**
 * Check for ill-formed escaping, i.e. `esc_attr` being wrapped around
 * `print` or `printf` statements instead of the other way round.
 */

class EscapingCheck extends CodeCheck {
	function __construct() {
		parent::__construct( array(
			// ...
		) );
	}
}
```

TO BE CONTINUED.
