<?php
require_once( 'CheckTestBase.php' );
class XSSVectorsTest extends CheckTestBase {

	public function test_xss_in_link_tag_href() {
		$file_contents = <<<'EOT'
			<LINK REL="styleheet" HREF='javascript:alert("XSS");'>
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'xss-in-link-tag-href', $error_slugs );
	}

	public function test_obfuscated_xss_in_link_tag_href() {
		$file_contents = <<<'EOT'
			<LINK REL="styleheet" HREF='ja\v\as\cr\ipt:alert("XSS");'>
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'xss-in-link-tag-href', $error_slugs );
	}

	public function test_xss_not_in_link_tag_href() {
		$file_contents = <<<'EOT'
			<LINK REL="styleheet" HREF='stylesheet.css'>
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertNotContains( 'xss-in-link-tag-href', $error_slugs );
	}

	public function test_xss_in_tag_style_attr() {
		$file_contents = <<<'EOT'
			<DIV STYLE="background-image: url(&#1;javascript:alert('XSS'))">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'xss-in-style-attribute', $error_slugs );
	}

	public function test_xss_not_in_tag_style_attr() {
		$file_contents = <<<'EOT'
			<DIV STYLE="background-image: url(bg.gif)">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertNotContains( 'xss-in-style-attribute', $error_slugs );
	}

	public function test_moz_binding_in_tag_style_attr() {
		$file_contents = <<<'EOT'
			<DIV STYLE="-moz-binding:url(http://ha.ckers.org/xssmoz.xml#xss)">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'moz-binding-xss-in-style-attribute', $error_slugs );
	}

	public function test_moz_binding_not_in_tag_style_attr() {
		$file_contents = <<<'EOT'
			<DIV STYLE="color: #fff;">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertNotContains( 'moz-binding-xss-in-style-attribute', $error_slugs );
	}

	public function test_moz_binding_in_tag_style() {
		$file_contents = <<<'EOT'
			<style>body{-moz-binding:url(http://ha.ckers.org/xssmoz.xml#xss)}</style>
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'moz-binding-xss-in-style-tag', $error_slugs );
	}

	public function test_moz_binding_not_in_tag_style() {
		$file_contents = <<<'EOT'
			<style>body{ color: #000; }</style>
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertNotContains( 'moz-binding-xss-in-style-tag', $error_slugs );
	}

	public function test_xss_in_style_tag() {
		$file_contents = <<<'EOT'
			<STYLE>@im\port'\ja\vasc\ript:alert("XSS")';</STYLE>
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'xss-javascript-in-style-tag', $error_slugs );
	}

	public function test_xss_not_in_style_tag() {
		$file_contents = <<<'EOT'
			<STYLE>body{ color: #000; }</STYLE>
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertNotContains( 'xss-javascript-in-style-tag', $error_slugs );
	}

	public function test_css_expression_xss_in_style_tag() {
		$file_contents = <<<'EOT'
			<STYLE>body{ width: expression(alert('XSS')); }</STYLE>
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'css-expression-xss-in-style-tag', $error_slugs );
	}

	public function test_css_expression_xss_not_in_style_tag() {
		$file_contents = <<<'EOT'
			<STYLE>body{ color: #000; }</STYLE>
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertNotContains( 'css-expression-xss-in-style-tag', $error_slugs );
	}

	public function test_css_expression_xss_in_style_attribute() {
		$file_contents = <<<'EOT'
			<DIV STYLE="width: expression(alert('XSS'));">>
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'css-expression-xss-in-style-attribute', $error_slugs );
	}

	public function test_obfuscated_css_expression_xss_in_style_attribute() {
		$file_contents = <<<'EOT'
			<IMG STYLE="xss:expr/*XSS*/ession(alert('XSS'))">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'css-expression-xss-in-style-attribute', $error_slugs );
	}

	public function test_css_expression_xss_not_in_style_attribute() {
		$file_contents = <<<'EOT'
			<DIV STYLE="width: 100%;">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertNotContains( 'css-expression-xss-in-style-attribute', $error_slugs );
	}

}