<?php

namespace Codeception\Template;

use tad\WPBrowser\Template\BaseTemplate;

class LocalServer extends BaseTemplate {

	/**
	 * Override this class to create customized setup.
	 *
	 * @return mixed
	 */
	public function setup() {
		$line = $this->ask('Line?');

		file_put_contents($this->getOutputFilePath(), $line);
	}

	/**
	 * @return string
	 */
	public function getOutputFilePath() {
		return codecept_output_dir('output.txt');
	}


}