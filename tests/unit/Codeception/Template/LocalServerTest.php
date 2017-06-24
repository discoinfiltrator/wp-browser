<?php

namespace Codeception\Template;


use Prophecy\Argument;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class LocalServerTest extends \Codeception\Test\Unit {

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @param $answers
     *
     * @return \Codeception\Template\LocalServer
     */
    protected function makeInstance($answers): \Codeception\Template\LocalServer {
        $input  = new StringInput('');
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

    protected function _before() {
    }

    protected function _after() {
    }

    public function test_input() {
        $answers = ['foo'];
        $sut     = $this->makeInstance($answers);

        $sut->setup();

        $this->assertStringEqualsFile($sut->getOutputFilePath(), 'foo');
    }
}