<?php

class AdBustersTest extends WP_UnitTestCase {
	protected $_AdBustersCheck;

	public function setUp() {
		parent::setUp();
		require_once VIP_SCANNER_DIR . '/checks/AdBustersCheck.php';

		$this->_AdBustersCheck = new AdBustersCheck();
	}

	public function runCheck( $file_contents ) {
		$input = array( 'php' => array( 'test.php' => $file_contents ) );

		$result = $this->_AdBustersCheck->check( $input );
		$errors = $this->_AdBustersCheck->get_errors();

		return wp_list_pluck( $errors, 'slug' );
	}

	public function test_known_adbusters() {
		$file = '/mytheme/adcentric/ifr_b.html';
		$this->assertTrue( $this->_AdBustersCheck->is_adbuster( $file) );
	}

	public function test_non_adbuster() {
		$file = '/mytheme/this_is_not_an_adbuster/david.html';
		$this->assertFalse( $this->_AdBustersCheck->is_adbuster( $file ) );
	}

	public function test_maybe_adbuster_catch_by_name() {
		$file = '/mytheme/ads/this_is_an_adbuster.htm';
		$do_file_examination = false;
		$filesize_check = false;
		$this->assertTrue( $this->_AdBustersCheck->maybe_adbuster( $file, $filesize_check, $do_file_examination ) );
	}

	public function test_maybe_adbuster_catch_by_name_not_an_adbuster() {
		$file = '/mytheme/ads/this_is_an_iframe.htm';
		$do_file_examination = false;
		$filesize_check = false;
		$this->assertFalse( $this->_AdBustersCheck->maybe_adbuster( $file, $filesize_check, $do_file_examination ) );
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
		$this->assertFalse( $this->_AdBustersCheck->possible_adbuster_body_check( $file_content ) );
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
		$this->assertTrue( $this->_AdBustersCheck->possible_adbuster_body_check( $file_content ) );
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
		$this->assertTrue( $this->_AdBustersCheck->possible_adbuster_body_check( $file_content ) );
	}
}