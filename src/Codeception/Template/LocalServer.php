<?php

namespace Codeception\Template;


use Codeception\InitTemplate;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class LocalServer extends InitTemplate {

    /**
     * @var \Symfony\Component\Console\Helper\QuestionHelper
     */
    protected $questionHelper;

    /**
     * Override this class to create customized setup.
     *
     * @return mixed
     */
    public function setup() {
        $line = $this->ask('Line?');

        file_put_contents($this->getOutputFilePath(), $line);
    }

    public function __construct(InputInterface $input, OutputInterface $output, QuestionHelper $question_helper = NULL) {
        parent::__construct($input, $output);
        $this->questionHelper = $question_helper ? $question_helper : new QuestionHelper();
    }

    protected function ask($question, $answer = NULL) {
        $question = "? $question";
        $dialog   = $this->questionHelper;
        if (is_array($answer)) {
            $question .= " <info>(" . $answer[0] . ")</info> ";
            return $dialog->ask($this->input, $this->output, new ChoiceQuestion($question, $answer, 0));
        }
        if (is_bool($answer)) {
            $question .= " (y/n) ";
            return $dialog->ask($this->input, $this->output, new ConfirmationQuestion($question, $answer));
        }
        if ($answer) {
            $question .= " <info>($answer)</info>";
        }
        return $dialog->ask($this->input, $this->output, new Question("$question ", $answer));
    }

    /**
     * @return string
     */
    public function getOutputFilePath(): string {
        return codecept_output_dir('output.txt');
    }

    /**
     * @return \Symfony\Component\Console\Helper\QuestionHelper
     */
    public function getQuestionHelper(): \Symfony\Component\Console\Helper\QuestionHelper {
        return $this->questionHelper;
    }

    /**
     * @param \Symfony\Component\Console\Helper\QuestionHelper $questionHelper
     */
    public function setQuestionHelper(\Symfony\Component\Console\Helper\QuestionHelper $questionHelper) {
        $this->questionHelper = $questionHelper;
    }
}