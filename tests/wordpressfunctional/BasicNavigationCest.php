<?php

use WordpressfunctionalTester as Tester;

class BasicNavigationCest {

	public function _before(Tester $I) {
		$I->useTheme('twentyseventeen');
	}

	/**
	 * It should allow navigating to the site homepage
	 *
	 * @test
	 */
	public function should_allow_navigating_to_the_site_homepage(Tester $I) {
		$I->amOnPage('/');

		$I->seeElement('body.home');
	}

	/**
	 * It should allow navigating to a single post page
	 *
	 * @test
	 */
	public function should_allow_navigating_to_a_single_post_page(Tester $I) {
		$id = $I->havePostInDatabase();

		$I->amOnPage("/index.php?p={$id}");

		$I->seeElement('body.single');
	}

	/**
	 * It should allow navigating to a page
	 *
	 * @test
	 */
	public function should_allow_navigating_to_a_page(Tester $I) {
		$I->havePageInDatabase(['post_name' => 'test']);

		$I->amOnPage("/test");

		$I->seeElement('body.page');
	}
}
