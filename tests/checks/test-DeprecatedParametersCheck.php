<?php

require_once( 'CodeCheckTestBase.php' );

class DeprecatedParametersTest extends CodeCheckTestBase {

	public function testDeprecatedParameters() {
		$description_template = 'The deprecated function parameter %1$s was found. Use %2$s instead.';
		$expected_errors = array(
			array( 'slug' => 'deprecated', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, '<code>get_bloginfo( &#039;home&#039; )</code>', '<code>home_url()</code>' ), 'file' => 'DeprecatedParametersTest.inc', 'lines' => 5 ),
			array( 'slug' => 'deprecated', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, '<code>get_bloginfo( &#039;url&#039; )</code>', '<code>home_url()</code>' ), 'file' => 'DeprecatedParametersTest.inc', 'lines' => 6 ),
			array( 'slug' => 'deprecated', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, '<code>get_bloginfo( &#039;wpurl&#039; )</code>', '<code>site_url()</code>' ), 'file' => 'DeprecatedParametersTest.inc', 'lines' => 7 ),
			array( 'slug' => 'deprecated', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, '<code>get_bloginfo( &#039;stylesheet_directory&#039; )</code>', '<code>get_stylesheet_directory_uri()</code>' ), 'file' => 'DeprecatedParametersTest.inc', 'lines' => 8 ),
			array( 'slug' => 'deprecated', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, '<code>get_bloginfo( &#039;template_directory&#039; )</code>', '<code>get_template_directory_uri()</code>' ), 'file' => 'DeprecatedParametersTest.inc', 'lines' => 9 ),
			array( 'slug' => 'deprecated', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, '<code>get_bloginfo( &#039;template_url&#039; )</code>', '<code>get_template_directory_uri()</code>' ), 'file' => 'DeprecatedParametersTest.inc', 'lines' => 10 ),
			array( 'slug' => 'deprecated', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, '<code>get_bloginfo( &#039;text_direction&#039; )</code>', '<code>is_rtl()</code>' ), 'file' => 'DeprecatedParametersTest.inc', 'lines' => 11 ),
			array( 'slug' => 'deprecated', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, '<code>get_bloginfo( &#039;feed_url&#039; )</code>', "<code>get_feed_link( &#039;feed&#039; ), where feed is rss, rss2 or atom</code>" ), 'file' => 'DeprecatedParametersTest.inc', 'lines' => 12 ),
			array( 'slug' => 'deprecated', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, '<code>bloginfo( &#039;home&#039; )</code>', '<code>echo esc_url( home_url() )</code>' ), 'file' => 'DeprecatedParametersTest.inc', 'lines' => 14 ),
			array( 'slug' => 'deprecated', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, '<code>bloginfo( &#039;url&#039; )</code>', '<code>echo esc_url( home_url() )</code>' ), 'file' => 'DeprecatedParametersTest.inc', 'lines' => 15 ),
			array( 'slug' => 'deprecated', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, '<code>bloginfo( &#039;wpurl&#039; )</code>', '<code>echo esc_url( site_url() )</code>' ), 'file' => 'DeprecatedParametersTest.inc', 'lines' => 16 ),
			array( 'slug' => 'deprecated', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, '<code>bloginfo( &#039;stylesheet_directory&#039; )</code>', '<code>echo esc_url( get_stylesheet_directory_uri() )</code>' ), 'file' => 'DeprecatedParametersTest.inc', 'lines' => 17 ),
			array( 'slug' => 'deprecated', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, '<code>bloginfo( &#039;template_directory&#039; )</code>', '<code>echo esc_url( get_template_directory_uri() )</code>' ), 'file' => 'DeprecatedParametersTest.inc', 'lines' => 18 ),
			array( 'slug' => 'deprecated', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, '<code>bloginfo( &#039;template_url&#039; )</code>', '<code>echo esc_url( get_template_directory_uri() )</code>' ), 'file' => 'DeprecatedParametersTest.inc', 'lines' => 19 ),
			array( 'slug' => 'deprecated', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, '<code>bloginfo( &#039;text_direction&#039; )</code>', '<code>is_rtl()</code>' ), 'file' => 'DeprecatedParametersTest.inc', 'lines' => 20 ),
			array( 'slug' => 'deprecated', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, '<code>bloginfo( &#039;feed_url&#039; )</code>', "<code>echo esc_url( get_feed_link( &#039;feed&#039; ) ), where feed is rss, rss2 or atom</code>" ), 'file' => 'DeprecatedParametersTest.inc', 'lines' => 21 ),
			array( 'slug' => 'deprecated', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, '<code>get_option( &#039;home&#039; )</code>', '<code>home_url()</code>' ), 'file' => 'DeprecatedParametersTest.inc', 'lines' => 23 ),
			array( 'slug' => 'deprecated', 'level' => BaseScanner::LEVEL_BLOCKER, 'description' => sprintf( $description_template, '<code>get_option( &#039;site_url&#039; )</code>', '<code>site_url()</code>' ), 'file' => 'DeprecatedParametersTest.inc', 'lines' => 24 ),
		);
		$actual_errors = $this->checkFile( 'DeprecatedParametersTest.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}
}
