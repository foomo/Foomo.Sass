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
class Sass
{
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @var string
	 */
	public $filename;
	/**
	 * @var boolean
	 */
	public $watch = false;
	/**
	 * @var string
	 */
	public $name = '';
	/**
	 * @var boolean
	 */
	public $compress = false;

	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $filename
	 */
	public function __construct($filename)
	{
		$this->filename = $filename;
		if (!file_exists($this->filename)) {
			trigger_error ('Source does not exist: ' . $this->filename, E_USER_ERROR);
		};
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------


	/**
	 * @param $name
	 * @return $this
	 */
	public function name($name)
	{
		$this->name = $name;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getOutputPath()
	{
		return \Foomo\Sass\Module::getHtdocsVarBuildPath() . '/' . $this->getOutputBasename();
	}

	/**
	 * @return string
	 */
	public function getOutputFilename()
	{
		return \Foomo\Sass\Module::getHtdocsVarDir() . DIRECTORY_SEPARATOR . $this->getOutputBasename();
	}

	/**
	 * @return string
	 */
	public function getOutputBasename()
	{
		$basename = \md5($this->filename);
		if ($this->compress) $basename .= '.min';
		if(empty($this->name)) {
			return  $basename . '.css';
		} else {
			return  $this->name . '-' . $basename . '.css';
		}
	}

	/**
	 * @param boolean $watch
	 * @return \Foomo\Sass
	 */
	public function watch($watch=true)
	{
		$this->watch = $watch;
		return $this;
	}

	/**
	 * @param boolean $compress
	 * @return \Foomo\Sass
	 */
	public function compress($compress=true)
	{
		$this->compress = $compress;
		return $this;
	}
	private function needsCompilation()
	{
		$source = $this->filename;
		$output = $this->getOutputFilename();
		$compile = (!\file_exists($output));
		if (!$compile && $this->watch) {
			$dependencies = \Foomo\Sass\Utils::getDependencies($source);
            $outputMTime = filemtime($output);
            foreach($dependencies as $dep) {
                if(file_exists($dep) && filemtime($dep) > $outputMTime) {
                    $compile = true;
                    break;
                }
            }
		}
		return $compile;
	}
	/**
	 * @return \Foomo\Sass
	 */
	public function compile()
	{
		$output = $this->getOutputFilename();

		if (
			$this->needsCompilation() &&
			Lock::lock($lockName = 'SASS-' . basename($output)) &&
			$this->needsCompilation()
		) {

			$arguments = array(
				$this->filename,
				$this->getOutputFilename(),
				'--style'

			);
			if($this->compress) {
				$arguments[] = 'compressed';
			} else {
				$arguments[] = 'nested';
				// source maps
				$arguments[] = '--sourcemap';
			}
			$call = \Foomo\CliCall::create(
				Sass\Module::getBaseDir('bin') . DIRECTORY_SEPARATOR . 'sassc',
				$arguments
			)
				->execute()
			;
			if($call->exitStatus === 0 && !$this->compress) {
				Sass\SourceServer::fixSourcemap($this->getOutputFilename(), Sass\Module::NAME);
			}

			$success = $call->exitStatus == 0;
			if(!$success) {
				MVC::abort();
				header('Content-Type: text/plain;charset=utf-8;');
				echo $call->report;
				exit;
			}
			Lock::release($lockName);
		}

		return $this;
	}

	//---------------------------------------------------------------------------------------------
	// Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $filename Path to the sass file
	 * @return \Foomo\Sass
	 */
	public static function create($filename)
	{
		return new self($filename);
	}

}