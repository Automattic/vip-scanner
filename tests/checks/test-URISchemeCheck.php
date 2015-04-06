<?php

require_once( 'CheckTestBase.php' );

class URISchemeTest extends CheckTestBase {

	public function test_in_css() {
		$file_contents = <<<'EOT'
			body {
				background-image: url(http://www.example.com/bg.jpg);
			}
EOT;

		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'hardcoded-http-scheme', $error_slugs );
	}

	public function test_not_in_css() {
		$file_contents = <<<'EOT'
			body {
				background-color: #FFFFFF;
			}
EOT;

		$error_slugs = $this->runCheck( $file_contents );
		$this->assertNotContains( 'hardcoded-http-scheme', $error_slugs );
	}

	public function test_in_js() {
		$file_contents = <<<'EOT'
			var ajax_url = 'http://www.example.com/test.json';
EOT;

		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'hardcoded-http-scheme', $error_slugs );
	}

	public function test_not_in_js() {
		$file_contents = <<<'EOT'
			var ajax_url = '//www.example.com/test.json';
EOT;

		$error_slugs = $this->runCheck( $file_contents );
		$this->assertNotContains( 'hardcoded-http-scheme', $error_slugs );
	}

	public function test_in_php_wp_enqueue() {
		$file_contents = <<<'EOT'
		<?php
			wp_enqueue_script( 'script-name', 'http://www.example.com/test.js', array(), '1.0.0', true );
		?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'script-and-style-link-hardcoded-http-scheme', $error_slugs );
	}

	public function test_not_in_php_wp_enqueue() {
		$file_contents = <<<'EOT'
		<?php
			wp_enqueue_script( 'script-name', get_template_directory_uri() . '/js/example.js', array(), '1.0.0', true );
		?>
EOT;

		$error_slugs = $this->runCheck( $file_contents );
		$this->assertNotContains( 'script-and-style-link-hardcoded-http-scheme', $error_slugs );
	}

	public function test_in_html_object_tag() {
		$file_contents = <<<'EOT'
			<object width="400" height="400" data="http://www.example.com/test.swf"></object>
EOT;

		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'html-object-tag-data-attribute-hardcoded-http-scheme', $error_slugs );
	}

	public function test_not_in_html_object_tag() {
		$file_contents = <<<'EOT'
			<object width="400" height="400" data="//www.example.com/test.swf"></object>
EOT;

		$error_slugs = $this->runCheck( $file_contents );
		$this->assertNotContains( 'html-object-tag-data-attribute-hardcoded-http-scheme', $error_slugs );
	}

	public function test_in_html_menuitem_tag() {
		$file_contents = <<<'EOT'
<menu type="context" id="mymenu">
	<menuitem label="Refresh" onclick="window.location.reload();" icon="http://www.example.com/example.png"></menuitem>
</menu>
EOT;

		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'html-menuitem-tag-icon-attribute-hardcoded-http-scheme', $error_slugs );
	}

	public function test_not_in_html_menuitem_tag() {
		$file_contents = <<<'EOT'
<menu type="context" id="mymenu">
	<menuitem label="Refresh" onclick="window.location.reload();" icon="//www.example.com/example.png"></menuitem>
</menu>
EOT;

		$error_slugs = $this->runCheck( $file_contents );
		$this->assertNotContains( 'html-menuitem-tag-icon-attribute-hardcoded-http-scheme', $error_slugs );
	}

	public function test_in_html_tag() {
		$file_contents = <<<'EOT'
<html manifest="http://example.com/app1/manifest">
EOT;

		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'html-html-tag-manifest-attribute-hardcoded-http-scheme', $error_slugs );
	}

	public function test_not_in_html_tag() {
		$file_contents = <<<'EOT'
<html manifest="//example.com/app1/manifest">
EOT;

		$error_slugs = $this->runCheck( $file_contents );
		$this->assertNotContains( 'html-html-tag-manifest-attribute-hardcoded-http-scheme', $error_slugs );
	}

	public function test_in_html_video_tag() {
		$file_contents = <<<'EOT'
<video width="320" height="240" controls poster="http://www.example.com/movie.jpg">
EOT;

		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'html-video-tag-poster-attribute-hardcoded-http-scheme', $error_slugs );
	}

	public function test_not_in_html_video_tag() {
		$file_contents = <<<'EOT'
<video width="320" height="240" controls poster="//www.example.com/movie.jpg">
EOT;

		$error_slugs = $this->runCheck( $file_contents );
		$this->assertNotContains( 'html-video-tag-poster-attribute-hardcoded-http-scheme', $error_slugs );
	}

	public function test_in_html_tag_src_attribute() {
		$file_contents = <<<'EOT'
<audio src="http://www.example.com/audio.mp3">
<embed src="http://www.example.com/movie.swf">
<iframe src="http://www.example.com/page.html">
<img src="http://www.example.com/image.jpg">
<input src="http://www.example.com/image.jpg">
<script src="http://www.example.com/script.js">
<source src="http://www.example.com/movie.mp4">
<track src="http://www.example.com/subtitles.vtt">
<video src="http://www.example.com/movie.mp4">
EOT;

		$error_slugs = $this->runCheck( $file_contents );
		$this->assertContains( 'html-tag-src-attribute-hardcoded-http-scheme', $error_slugs );
	}

	public function test_not_in_html_tag_src_attribute() {
		$file_contents = <<<'EOT'
<audio src="//www.example.com/audio.mp3">
<embed src="//www.example.com/movie.swf">
<iframe src="//www.example.com/page.html">
<img src="//www.example.com/image.jpg">
<input src="//www.example.com/image.jpg">
<script src="//www.example.com/script.js">
<source src="//www.example.com/movie.mp4">
<track src="//www.example.com/subtitles.vtt">
<video src="//www.example.com/movie.mp4">
EOT;

		$error_slugs = $this->runCheck( $file_contents );
		$this->assertNotContains( 'html-tag-src-attribute-hardcoded-http-scheme', $error_slugs );
	}

}
