<?php

use WordpressfunctionalTester as Tester;

class ActionsCest {

	public function _before(Tester $I) {
		$I->useTheme('twentyseventeen');
	}

	/**
	 * It should allow hooking on an action while navigating to the homepage
	 *
	 * @test
	 */
	public function should_allow_hooking_on_an_action_while_navigating_to_the_homepage(Tester $I) {
		$wp_headFired   = false;
		$wp_footerFired = false;

		add_action('wp_head', function () use (&$wp_headFired) {
			$wp_headFired = true;
			echo '<meta foo="bar">';
		});

		add_action('wp_footer', function () use (&$wp_footerFired) {
			$wp_footerFired = true;
			echo '<p>Hello from the footer</p>';
		});

		$I->amOnPage('/');

		$I->assertTrue($wp_headFired);
		$I->seeInSource('<meta foo="bar">');
		$I->seeInSource('<p>Hello from the footer</p>');
	}

	/**
	 * It should allow visiting the homepage multiple times
	 *
	 * Testing that the `require_once` of locate template is eluded...
	 *
	 * @test
	 */
	public function should_allow_visiting_the_homepage_multiple_times(Tester $I) {
		$wp_headFiredTimes   = 0;
		$wp_footerFiredTimes = 0;

		add_action('wp_head', function () use (&$wp_headFiredTimes) {
			++$wp_headFiredTimes;
		});

		add_action('wp_footer', function () use (&$wp_footerFiredTimes) {
			++$wp_footerFiredTimes;
		});

		$I->amOnPage('/');
		$I->amOnPage('/');
		$I->amOnPage('/');

		$I->assertEquals(3, $wp_headFiredTimes);
		$I->assertEquals(3, $wp_footerFiredTimes);
	}

	/**
	 * It should allow visiting a single post page multiple times
	 *
	 * @test
	 */
	public function should_allow_visiting_a_single_post_page_multiple_times(Tester $I) {
		$wp_headFiredTimes   = 0;
		$wp_footerFiredTimes = 0;

		add_action('wp_head', function () use (&$wp_headFiredTimes) {
			++$wp_headFiredTimes;
		});

		add_action('wp_footer', function () use (&$wp_footerFiredTimes) {
			++$wp_footerFiredTimes;
		});

		$id = $I->havePostInDatabase();
		$I->amOnPage("/index.php?p={$id}");
		$I->amOnPage("/index.php?p={$id}");
		$I->amOnPage("/index.php?p={$id}");

		$I->assertEquals(3, $wp_headFiredTimes);
		$I->assertEquals(3, $wp_footerFiredTimes);
	}

	/**
	 * It should allow visiting a page multiple times
	 *
	 * @test
	 */
	public function should_allow_visiting_a_page_multiple_times(Tester $I) {
		$wp_headFiredTimes   = 0;
		$wp_footerFiredTimes = 0;

		add_action('wp_head', function () use (&$wp_headFiredTimes) {
			++$wp_headFiredTimes;
		});

		add_action('wp_footer', function () use (&$wp_footerFiredTimes) {
			++$wp_footerFiredTimes;
		});

		$I->havePageInDatabase(['post_name' => 'test']);
		$I->amOnPage('/test');
		$I->amOnPage('/test');
		$I->amOnPage('/test');

		$I->assertEquals(3, $wp_headFiredTimes);
		$I->assertEquals(3, $wp_footerFiredTimes);
	}
}
