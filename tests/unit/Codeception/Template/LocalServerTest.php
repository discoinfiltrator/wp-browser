<?php

namespace Codeception\Template;


use Prophecy\Argument;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

class LocalServerTest extends \Codeception\Test\Unit {

	/**
	 * @var \UnitTester
	 */
	protected $tester;

	public function test_input() {
		$answers = ['foo'];
		$sut = $this->makeInstance($answers);

		$sut->setup();

		$this->assertStringEqualsFile($sut->getOutputFilePath(), 'foo');
	}

	/**
	 * @param $answers
	 *
	 * @return \Codeception\Template\LocalServer
	 */
	protected function makeInstance($answers) {
		$input = new StringInput('');
		$output = new BufferedOutput();
		/** @var QuestionHelper $questionHelper */
		$questionHelper = $this->prophesize(QuestionHelper::class);
		$questionHelper->ask(Argument::any(), Argument::any(), Argument::any())
			->will(function () use ($answers) {
				static $count;

				return $answers[(int) $count++];
			});

		$instance = new LocalServer($input, $output, $questionHelper->reveal());

		return $instance;
	}
}