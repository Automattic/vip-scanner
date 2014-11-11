<?php

require_once( 'CheckTestBase.php' );

class AdBustersTest extends CheckTestBase {

	public function test_known_adbusters() {
		$file = '/mytheme/adcentric/ifr_b.html';
		$this->assertTrue( $this->check->is_adbuster( $file) );
	}

	public function test_non_adbuster() {
		$file = '/mytheme/this_is_not_an_adbuster/david.html';
		$this->assertFalse( $this->check->is_adbuster( $file ) );
	}

	public function test_maybe_adbuster_catch_by_name() {
		$file = '/mytheme/ads/this_is_an_adbuster.htm';
		$do_file_examination = false;
		$filesize_check = false;
		$this->assertTrue( $this->check->maybe_adbuster( $file, $filesize_check, $do_file_examination ) );
	}

	public function test_maybe_adbuster_catch_by_name_not_an_adbuster() {
		$file = '/mytheme/ads/this_is_an_iframe.htm';
		$do_file_examination = false;
		$filesize_check = false;
		$this->assertFalse( $this->check->maybe_adbuster( $file, $filesize_check, $do_file_examination ) );
	}

	public function test_possible_adbuster_file_examination_non_adbuster() {
		$file_content =  <<<EOT
<html>
<head>
<style>* { color: black; }</style>
</head>
<body>
<p>Ahoj</p>
</body>
</html>
EOT;
		$this->assertFalse( $this->check->possible_adbuster_body_check( $file_content ) );
	}

	public function test_possible_adbuster_file_examination_real_adbuster() {
		$file_content =  <<<EOT
<html>
<head>
</head>
<body>
<script src='http://rmd.atdmt.com/tl/newIframeScript.js'> </script>


</body>
</html>
EOT;
		$this->assertTrue( $this->check->possible_adbuster_body_check( $file_content ) );
	}

	public function test_possible_adbuster_file_examination_real_adbuster_script_in_head() {
		$file_content =  <<<EOT
<html>
<head>
<script src='http://rmd.atdmt.com/tl/newIframeScript.js'> </script>
</head>
<body>

</body>
</html>
EOT;
		$this->assertTrue( $this->check->possible_adbuster_body_check( $file_content ) );
	}
}