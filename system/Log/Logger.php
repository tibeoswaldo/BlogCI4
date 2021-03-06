<?php

/*
 * BlogCI4 - Blog write with Codeigniter v4dev
 * @author Deathart <contact@deathart.fr>
 * @copyright Copyright (c) 2018 Deathart
 * @license https://opensource.org/licenses/MIT MIT License
 */

namespace CodeIgniter\Log;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
use CodeIgniter\Log\Exceptions\LogException;
use Psr\Log\LoggerInterface;

/**
 * The CodeIgntier Logger
 *
 * The message MUST be a string or object implementing __toString().
 *
 * The message MAY contain placeholders in the form: {foo} where foo
 * will be replaced by the context data in key "foo".
 *
 * The context array can contain arbitrary data, the only assumption that
 * can be made by implementors is that if an Exception instance is given
 * to produce a stack trace, it MUST be in a key named "exception".
 *
 * @package CodeIgniter\Log
 */
class Logger implements LoggerInterface
{
	/**
	 * Caches logging calls for debugbar.
	 *
	 * @var array
	 */
	public $logCache;

	/**
	 * Path to save log files to.
	 *
	 * @var string
	 */
	protected $logPath;

	/**
	 * Used by the logThreshold Config setting to define
	 * which errors to show.
	 *
	 * @var array
	 */
	protected $logLevels = [
		'emergency'	 => 1,
		'alert'		 => 2,
		'critical'	 => 3,
		'error'		 => 4,
		'debug'		 => 5,
		'warning'	 => 6,
		'notice'	 => 7,
		'info'		 => 8,
	];

	/**
	 * Array of levels to be logged.
	 * The rest will be ignored.
	 * Set in Config/logger.php
	 *
	 * @var array
	 */
	protected $loggableLevels = [];

	/**
	 * File permissions
	 *
	 * @var int
	 */
	protected $filePermissions = 0644;

	/**
	 * Format of the timestamp for log files.
	 *
	 * @var string
	 */
	protected $dateFormat = 'Y-m-d H:i:s';

	/**
	 * Filename Extension
	 *
	 * @var string
	 */
	protected $fileExt;

	/**
	 * Caches instances of the handlers.
	 *
	 * @var array
	 */
	protected $handlers = [];

	/**
	 * Holds the configuration for each handler.
	 * The key is the handler's class name. The
	 * value is an associative array of configuration
	 * items.
	 *
	 * @var array
	 */
	protected $handlerConfig = [];

	/**
	 * Should we cache our logged items?
	 *
	 * @var bool
	 */
	protected $cacheLogs = false;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param type $config
	 * @param bool $debug
	 * @throws \RuntimeException
	 */
	public function __construct($config, bool $debug = CI_DEBUG)
	{
		$this->loggableLevels = is_array($config->threshold) ? $config->threshold : range(1, (int) $config->threshold);

		// Now convert loggable levels to strings.
		// We only use numbers to make the threshold setting convenient for users.
		if ($this->loggableLevels)
		{
			$temp = [];
			foreach ($this->loggableLevels as $level)
			{
				$temp[] = array_search((int) $level, $this->logLevels, true);
			}

			$this->loggableLevels = $temp;
			unset($temp);
		}

		$this->dateFormat = $config->dateFormat ?? $this->dateFormat;

		if ( ! is_array($config->handlers) || empty($config->handlers))
		{
			throw LogException::forNoHandlers('LoggerConfig');
		}

		// Save the handler configuration for later.
		// Instances will be created on demand.
		$this->handlerConfig = $config->handlers;

		$this->cacheLogs = (bool) $debug;
		if ($this->cacheLogs)
		{
			$this->logCache = [];
		}
	}

	//--------------------------------------------------------------------

	/**
	 * System is unusable.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function emergency($message, array $context = [])
	{
		$this->log('emergency', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Action must be taken immediately.
	 *
	 * Example: Entire website down, database unavailable, etc. This should
	 * trigger the SMS alerts and wake you up.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function alert($message, array $context = [])
	{
		$this->log('alert', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Critical conditions.
	 *
	 * Example: Application component unavailable, unexpected exception.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function critical($message, array $context = [])
	{
		$this->log('critical', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function error($message, array $context = [])
	{
		$this->log('error', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Exceptional occurrences that are not errors.
	 *
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things
	 * that are not necessarily wrong.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function warning($message, array $context = [])
	{
		$this->log('warning', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Normal but significant events.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function notice($message, array $context = [])
	{
		$this->log('notice', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Interesting events.
	 *
	 * Example: User logs in, SQL logs.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function info($message, array $context = [])
	{
		$this->log('info', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Detailed debug information.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function debug($message, array $context = [])
	{
		$this->log('debug', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed  $level
	 * @param string $message
	 * @param array  $context
	 *
	 * @return bool
	 */
	public function log($level, $message, array $context = []): bool
	{
		if (is_numeric($level))
		{
			$level = array_search((int) $level, $this->logLevels, true);
		}

		// Is the level a valid level?
		if ( ! array_key_exists($level, $this->logLevels))
		{
			throw LogException::forInvalidLogLevel($level);
		}

		// Does the app want to log this right now?
		if ( ! in_array($level, $this->loggableLevels, true))
		{
			return false;
		}

		// Parse our placeholders
		$message = $this->interpolate($message, $context);

		if ( ! is_string($message))
		{
			$message = print_r($message, true);
		}

		if ($this->cacheLogs)
		{
			$this->logCache[] = [
				'level'	 => $level,
				'msg'	 => $message
			];
		}

		foreach ($this->handlerConfig as $className => $config)
		{
			if ( ! array_key_exists($className, $this->handlers)) {
				$this->handlers[$className] = new $className($config);
			}
			
			/**
			 * @var \CodeIgniter\Log\Handlers\HandlerInterface
			 */
			$handler = $this->handlers[$className];

			if ( ! $handler->canHandle($level))
			{
				continue;
			}

			// If the handler returns false, then we
			// don't execute any other handlers.
			if ( ! $handler->setDateFormat($this->dateFormat)->handle($level, $message))
			{
				break;
			}
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Determines the current file/line that the log method was called from.
	 * by analyzing the backtrace.
	 *
	 * @return array
	 */
	public function determineFile()
	{
		// Determine the file and line by finding the first
		// backtrace that is not part of our logging system.
		$trace = debug_backtrace();
		$file = null;
		$line = null;

		foreach ($trace as $row)
		{
			if (in_array($row['function'], ['interpolate', 'determineFile', 'log', 'log_message'], true))
			{
				continue;
			}

			$file = $row['file'] ?? isset($row['object']) ? get_class($row['object']) : 'unknown';
			$line = $row['line'] ?? $row['function'] ?? 'unknown';

			break;
		}

		return [
			$file,
			$line
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Replaces any placeholders in the message with variables
	 * from the context, as well as a few special items like:
	 *
	 * {session_vars}
	 * {post_vars}
	 * {get_vars}
	 * {env}
	 * {env:foo}
	 * {file}
	 * {line}
	 *
	 * @param       $message
	 * @param array $context
	 *
	 * @return string
	 */
	protected function interpolate($message, array $context = [])
	{
		if ( ! is_string($message))
			return $message;
		// build a replacement array with braces around the context keys
		$replace = [];

		foreach ($context as $key => $val)
		{
			// Verify that the 'exception' key is actually an exception
			// or error, both of which implement the 'Throwable' interface.
			if ($key == 'exception' && $val instanceof \Throwable)
			{
				$val = $val->getMessage() . ' ' . $this->cleanFileNames($val->getFile()) . ':' . $val->getLine();
			}

			// todo - sanitize input before writing to file?
			$replace['{' . $key . '}'] = $val;
		}

		// Add special placeholders
		$replace['{post_vars}'] = '$_POST: ' . print_r($_POST, true);
		$replace['{get_vars}'] = '$_GET: ' . print_r($_GET, true);
		$replace['{env}'] = ENVIRONMENT;

		// Allow us to log the file/line that we are logging from
		if (strpos($message, '{file}') !== false)
		{
			list($file, $line) = $this->determineFile();

			$replace['{file}'] = $file;
			$replace['{line}'] = $line;
		}

		// Match up environment variables in {env:foo} tags.
		if (strpos($message, 'env:') !== false)
		{
			preg_match('/env:[^}]+/', $message, $matches);

			if ($matches)
			{
				foreach ($matches as $str)
				{
					$key = str_replace('env:', '', $str);
					$replace["{{$str}}"] = $_ENV[$key] ?? 'n/a';
				}
			}
		}

		if (isset($_SESSION))
		{
			$replace['{session_vars}'] = '$_SESSION: ' . print_r($_SESSION, true);
		}

		// interpolate replacement values into the message and return
		return strtr($message, $replace);
	}

	//--------------------------------------------------------------------

	/**
	 * Cleans the paths of filenames by replacing APPPATH, BASEPATH, FCPATH
	 * with the actual var. i.e.
	 *
	 *  /var/www/site/application/Controllers/Home.php
	 *      becomes:
	 *  APPPATH/Controllers/Home.php
	 *
	 * @param $file
	 *
	 * @return mixed
	 */
	protected function cleanFileNames($file)
	{
		$file = str_replace(APPPATH, 'APPPATH/', $file);
		$file = str_replace(BASEPATH, 'BASEPATH/', $file);
		$file = str_replace(FCPATH, 'FCPATH/', $file);

		return $file;
	}

	//--------------------------------------------------------------------
}
