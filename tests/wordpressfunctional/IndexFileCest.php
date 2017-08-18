<?php

use WordpressfunctionalTester as Tester;

class IndexFileCest {

	public function _before(Tester $I) {
		$I->useTheme('twentyseventeen');
	}

	/**
	 * It should correctly include the file when navigating to the index file multiple times
	 *
	 * @test
	 */
	public function should_correctly_include_the_file_when_navigating_to_the_index_file_multiple_times(Tester $I) {
		$wp_headFiredTimes   = 0;
		$wp_footerFiredTimes = 0;

		add_action('wp_head', function () use (&$wp_headFiredTimes) {
			++$wp_headFiredTimes;
		});

		add_action('wp_footer', function () use (&$wp_footerFiredTimes) {
			++$wp_footerFiredTimes;
		});

		$I->amOnPage('/index.php');
		$I->amOnPage('/index.php');
		$I->amOnPage('/index.php');

		$I->assertEquals(3, $wp_headFiredTimes);
		$I->assertEquals(3, $wp_footerFiredTimes);
	}
}
