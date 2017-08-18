<?php

use WordpressfunctionalTester as Tester;

class LoginCest {

	/**
	 * It should allow logging in and have the request use the authenticated user
	 *
	 * @test
	 */
	public function should_allow_logging_in_and_have_the_request_use_the_authenticated_user(Tester $I) {
		add_action('wp_footer', function () {
			if (is_user_logged_in()) {
				echo 'User id is ' . get_current_user_id();
			}
			else {
				echo 'User is not logged in';
			}
		});

		$userId = $I->haveUserInDatabase('user', 'subscriber', ['user_pass' => 'password']);

		$I->loginAs('user', 'password');

		$I->amOnPage('/');

		$I->see("User id is {$userId}");
		$I->dontSee('User is not logged in');
	}

	/**
	 * It should not fire hooks during the login process
	 *
	 * @test
	 */
	public function should_not_fire_hooks_during_the_login_process(Tester $I) {
		$fired = false;
		add_action('login_head', function () use (&$fired) {
			$fired = true;
		});

		$I->loginAsAdmin();

		$I->assertFalse($fired);
	}
}
