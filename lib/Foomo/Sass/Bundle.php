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

namespace Foomo\Sass;

use Foomo\Bundle\Compiler\Result;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author Jan Halfar <jan@bestbytes.com>
 */
class Bundle extends \Foomo\Bundle\AbstractBundle
{
	/**
	 * sass source file name
	 * @var string
	 */
	private $sass;
    public function getHash()
    {
        return parent::getHash() . sha1($this->sass);
    }
	/**
	 * @param Result $result
	 * @return Bundle
	 */
	public function compile(Result $result)
	{
		$sassCompiler = \Foomo\Sass::create($this->sass)
			->watch($this->debug)
			->name($this->name)
			->compress(!$this->debug)
			->compile()
		;
		$result->resources[] = Result\Resource::create(
			Result\Resource::MIME_TYPE_CSS,
			$sassCompiler->getOutputFilename(),
			$sassCompiler->getOutputPath()
		);
		return $this;
	}

	/**
	 * @param string $name
	 * @param string $sass scss file name
	 *
	 * @return Bundle
	 */
	public static function create($name)
	{
		$ret = parent::create($name);
		$ret->sass = func_get_arg(1);
		return $ret;
	}
	public static function mergeFiles(array $files, $debug)
	{
		$css = '';
		foreach($files as $file) {
			$css .= file_get_contents($file) . PHP_EOL;
		}
		return $css;
	}
	public static function canMerge($mimeType)
	{
		return $mimeType == Result\Resource::MIME_TYPE_CSS;
	}
}
