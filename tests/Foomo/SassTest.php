<?php

/*
 * This file is part of the foomo Opensource Framework.
 *
 * The foomo Opensource Framework is free software: you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public License as
 * published  by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * The foomo Opensource Framework is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with
 * the foomo Opensource Framework. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Foomo;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author Jan Halfar <jan@bestbytes.com>
 */
class SassTest extends \PHPUnit_Framework_TestCase
{
	const TEST_FILE_PREFIX = 'foomo-sass-test-';
	public function cleanup()
	{
		foreach(new \DirectoryIterator(Sass\Module::getHtdocsVarDir()) as $fileInfo) {
			if($fileInfo->isFile() && substr($fileInfo->getFilename(), 0, strlen(self::TEST_FILE_PREFIX)) == self::TEST_FILE_PREFIX) {
				unlink($fileInfo->getPathname());
			}
		}
	}
	public function setUp()
	{
		$this->cleanup();
	}
	public function tearDown()
	{
		$this->cleanup();
	}
	/**
	 * @param bool $debug
	 *
	 * @return Sass
	 */
	private static function compile($debug, $name)
	{
		return Sass::create(self::getSassFile('test.scss'))
			->name(self::TEST_FILE_PREFIX . $name)
			->compress($debug === false)
			->watch($debug === true)
			->compile()
		;

	}
	private static function getSassFile($basename)
	{
		return Sass\Module::getBaseDir('sass') . DIRECTORY_SEPARATOR . $basename;
	}
	private function iterateWithDebugValues(callable $func)
	{
		foreach(array(true, false) as $debugValue) {
			$func($debugValue);
		}
	}
	public function testCompile()
	{
		$this->iterateWithDebugValues(function($debugValue) {
			$this->assertFileExists(self::compile($debugValue, 'compile')->getOutputFilename());
		});
	}
	public function testCompileImport()
	{
		$this->iterateWithDebugValues(function($debugValue) {
			$this->assertContains(
				'class-defined-in-import',
				file_get_contents(
					self::compile($debugValue, 'compile-import')->getOutputFilename()
				)
			);
		});
	}
	public function testCompileSourceMapAndSourceServer()
	{
		$sass = self::compile(true, 'source-maps');
		$css = file_get_contents($sass->getOutputFilename());
		$sourceMap = json_decode(file_get_contents($sass->getOutputFilename() . '.map'));
		$this->assertContains('/*# sourceMappingURL=' . basename($sass->getOutputFilename()) . '.map', $css);
		foreach($sourceMap->sources as $sourceServerPath) {
			$this->assertEquals(
				file_get_contents($localFile = self::getSassFile(basename($sourceServerPath))),
				file_get_contents($sourceURL = Utils::getServerUrl() . $sourceServerPath)
			);
		}
	}
}