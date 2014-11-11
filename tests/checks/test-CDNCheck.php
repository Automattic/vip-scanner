<?php

require_once( 'CheckTestBase.php' );

class CDNTest extends CheckTestBase {

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

		$file_contents = <<<'EOT'
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

		$error_slugs = $this->runCheck( $file_contents );

		foreach( $cdn_list as $cdn ) {
			$this->assertContains( 'cdn-' . $cdn, $error_slugs );
		}
	}
}