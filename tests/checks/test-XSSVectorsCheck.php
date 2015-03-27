<?php
require_once( 'CheckTestBase.php' );
class XSSVectorsTest extends CheckTestBase {

	/*
	 * XSS javascript in link href tag
	 */
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

	/*
	 * XSS javascript in any src attribute
	 */
	public function test_xss_in_any_tag_src() {
		$file_contents = <<<'EOT'
			<BGSOUND SRC="javascript:alert('XSS');">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'xss-javascript-in-any-tag-src', $error_slugs );
	}

	public function test_xss_not_in_any_tag_src() {
		$file_contents = <<<'EOT'
			<BGSOUND SRC="canyon.mid">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertNotContains( 'xss-javascript-in-any-tag-src', $error_slugs );
	}

	/*
	 * XSS javascript in style attribute and style tag
	 */
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

	/*
	 * XSS -moz-binding in style attributes and style tag
	 */
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

	/*
	 * XSS CSS expression property in style attribute and style tag
	 */
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

	/*
	 * XSS CSS behavior property in style attribute and style tag
	 */
	public function test_css_behavior_xss_in_style_tag() {
		$file_contents = <<<'EOT'
			<STYLE>body{ behavior: url(xss.htc); }</STYLE>
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'css-behavior-xss-in-style-tag', $error_slugs );
	}

	public function test_css_behavior_xss_not_in_style_tag() {
		$file_contents = <<<'EOT'
			<STYLE>body{ color: #000; }</STYLE>
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertNotContains( 'css-behavior-xss-in-style-attribute', $error_slugs );
	}

	public function test_css_behavior_xss_in_style_attribute() {
		$file_contents = <<<'EOT'
			<DIV STYLE="behavior: url(xss.htc);">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'css-behavior-xss-in-style-attribute', $error_slugs );
	}

	public function test_css_behavior_xss_not_in_style_attribute() {
		$file_contents = <<<'EOT'
			<DIV STYLE="width: 100%;">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertNotContains( 'css-behavior-xss-in-style-attribute', $error_slugs );
	}

	/*
	 * XSS Script tag in malformed img tag
	 */
	public function test_script_tag_in_malformed_img_tag() {
		$file_contents = <<<'EOT'
			<IMG """><SCRIPT>alert("XSS")</SCRIPT>">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'malformed-img-tag-xss-script', $error_slugs );
	}

	public function test_script_tag_not_in_malformed_img_tag() {
		$file_contents = <<<'EOT'
			<IMG SRC="picture.jpg">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertNotContains( 'malformed-img-tag-xss-script', $error_slugs );
	}

	/*
	 * Malformed whitespace and embedded characters
	 */
	public function test_space_before_javascript_in_src_attr() {
		$file_contents = <<<'EOT'
			<IMG SRC="   javascript:alert('XSS');">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'xss-javascript-in-any-tag-src', $error_slugs );
	}

	public function test_space_and_metachar_before_javascript_in_src_attr() {
		$file_contents = <<<'EOT'
			<IMG SRC=" &#14;  javascript:alert('XSS');">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'xss-javascript-in-any-tag-src', $error_slugs );
	}

	public function test_tab_javascript_in_src_attr() {
		$file_contents = <<<'EOT'
			<IMG SRC="jav	ascript:alert('XSS');">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'xss-javascript-in-any-tag-src', $error_slugs );
	}

	public function test_encoded_tab_javascript_in_src_attr() {
		$file_contents = <<<'EOT'
			<IMG SRC="jav&#x09;ascript:alert('XSS');">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'xss-javascript-in-any-tag-src', $error_slugs );
	}

	public function test_encoded_newline_javascript_in_src_attr() {
		$file_contents = <<<'EOT'
			<IMG SRC="jav&#x0A;ascript:alert('XSS');">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'xss-javascript-in-any-tag-src', $error_slugs );
	}

	public function test_encoded_carriage_return_javascript_in_src_attr() {
		$file_contents = <<<'EOT'
			<IMG SRC="jav&#x0D;ascript:alert('XSS');">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'xss-javascript-in-any-tag-src', $error_slugs );
	}

	public function test_null_character_javascript_in_src_attr() {
		$file_contents = "<IMG SRC=\"jav\0ascript:alert('XSS');\">";
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'xss-javascript-in-any-tag-src', $error_slugs );
	}

	/*
	 * XSS javascript declaration in tag background attribute
	 */
	public function test_javascript_in_body_tag_background_attribute() {
		$file_contents = <<<'EOT'
			<BODY BACKGROUND="javascript:alert('XSS')">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'xss-in-background-attribute', $error_slugs );
	}

	public function test_javascript_not_in_body_tag_background_attribute() {
		$file_contents = <<<'EOT'
			<BODY BACKGROUND="#000">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertNotContains( 'xss-in-background-attribute', $error_slugs );
	}

	/*
	 * XSS javascript in img tag rarely used attributes
	 */
	public function test_xss_in_img_dynsrc_attr() {
		$file_contents = <<<'EOT'
			<IMG DYNSRC="javascript:alert('XSS')">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'xss-in-img-dynsrc-attr', $error_slugs );
	}

	public function test_xss_not_in_img_dynsrc_attr() {
		$file_contents = <<<'EOT'
			<IMG DYNSRC="movie.mpg" SRC="movie.gif">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertNotContains( 'xss-in-img-dynsrc-attr', $error_slugs );
	}

	public function test_xss_in_img_lowsrc_attr() {
		$file_contents = <<<'EOT'
			<IMG LOWSRC="javascript:alert('XSS')">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'xss-in-img-lowsrc-attr', $error_slugs );
	}

	public function test_xss_not_in_img_lowsrc_attr() {
		$file_contents = <<<'EOT'
			<IMG LOWSRC="picsmall.jpg" SRC="picregular.jpg">
EOT;
		$error_slugs = $this->runCheck( $file_contents );
		$this->assertNotContains( 'xss-in-img-lowsrc-attr', $error_slugs );
	}

}