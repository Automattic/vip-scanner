<?php

class CDNTest extends WP_UnitTestCase {
	protected $_CDNCheck;

	public function setUp() {
		parent::setUp();
		require_once VIP_SCANNER_DIR . '/checks/CDNCheck.php';

		$this->_CDNCheck = new CDNCheck();
	}

	public function testForCDN() {
		$cdn_list = array(
			'bootstrap-maxcdn',
			'bootstrap-netdna',
			'bootswatch-maxcdn',
			'bootswatch-netdna',
			'font-awesome-maxcdn',
			'font-awesome-netdna',
			'html5shiv-google',
			'html5shiv-maxcdn',
			'jquery',
			'respond-js',
		);

		$file_content = <<<'EOT'
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
<link href="//netdna.bootstrapcdn.com/bootswatch/3.1.1/slate/bootstrap.min.css" rel="stylesheet">
<link href="//maxcdn.bootstrapcdn.com/bootswatch/3.1.1/slate/bootstrap.min.css" rel="stylesheet">
<?php wp_enqueue_style( 'font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css' ); ?>
<?php wp_enqueue_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css' ); ?>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
<?php wp_enqueue_script("jquery","http://code.jquery.com/jquery-2.1.1.min.js"); ?>
<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
EOT;

		$input = array(
			'php' => array(
				'test.php' => $file_content
			)
		);

		$result = $this->_CDNCheck->check( $input );

		$errors = $this->_CDNCheck->get_errors();
		$error_slugs = wp_list_pluck( $errors, 'slug' );

		foreach( $cdn_list as $cdn ) {
			$this->assertContains( 'cdn-' . $cdn, $error_slugs );
		}
	}
}