<?php

namespace tad\WPBrowser\Template;

use Codeception\InitTemplate;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

abstract class BaseTemplate extends InitTemplate {

	/**
	 * Asks a question to the user.
	 *
	 * @param string $question
	 * @param  string $answer
	 *
	 * @return string
	 */
	protected function ask($question, $answer = NULL) {
		$question = "? $question";
		$dialog = $this->questionHelper;
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
}