<?php
namespace tad\WPBrowser\Interfaces;


use Codeception\Exception\ModuleException;

interface PatchworkUser {

	/**
	 * Writes the Patchwork configuration and checksum files if needed.
	 *
	 * @return bool Whether a new configuration file was written or not.
	 *
	 * @throws \Codeception\Exception\ModuleException If the Patchwork configuration or the checksum files could not be written.
	 */
	public function _writePatchworkConfig();

	/**
	 * Returns the absolute path to the Patchwork configuration file checksum file.
	 *
	 * @return string
	 */
	public function _getPatchworkConfigChecksumFile();

	/**
	 * Returns the absolute path to the Patchwork configuration file used by the module.
	 *
	 * @return string
	 */
	public function _getPatchworkConfigFile();

	/**
	 * Returns the absolute path to the cache folder used by the Patchwork library.
	 *
	 * @return string
	 */
	public function _getPatchworkCachePath();

	/**
	 * Returns the contents of the Patchwork configuration file created by the class
	 *
	 * @return string
	 */
	public function _getPatchworkConfigurationContents();
}