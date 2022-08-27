<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\utils;

use pocketmine\Thread;
use pocketmine\Worker;
use LogLevel;
use DateTimeZone;

class MainLogger extends \AttachableThreadedLogger{

    private string $format = TextFormat::AQUA . "[%s] " . TextFormat::RESET . "%s[%s/%s]: %s" . TextFormat::RESET;
	protected string $logFile;
	protected \Threaded $logStream;
	protected bool $shutdown;
	protected bool $logDebug;
	protected DateTimeZone $timezone;
	public static ?MainLogger $logger = null;

	/**
	 * @param string $logFile
	 * @param bool $logDebug
	 *
	 * @throws \RuntimeException
	 */
	public function __construct(string $logFile, DateTimeZone $timezone, bool $logDebug = false){
		if(static::$logger instanceof MainLogger){
			throw new \RuntimeException("MainLogger has been already created");
		}
		touch($logFile);
		$this->logFile = $logFile;
		$this->logDebug = $logDebug;
		$this->timezone = $timezone->getName();
		$this->logStream = new \Threaded;
		$this->start();
	}

	/**
	 * @return MainLogger
	 */
	public static function getLogger() : MainLogger{
		return static::$logger;
	}

	/**
	 * Assigns the MainLogger instance to the {@link MainLogger#logger} static property.
	 *
	 * WARNING: Because static properties are thread-local, this MUST be called from the body of every Thread if you
	 * want the logger to be accessible via {@link MainLogger#getLogger}.
	 */
	public function registerStatic(){
		if(static::$logger === null){
			static::$logger = $this;
		}
	}

	public function emergency($message){
		$this->send($message, \LogLevel::EMERGENCY, "EMERGENCY", TextFormat::RED);
	}

	public function alert($message){
		$this->send($message, \LogLevel::ALERT, "ALERT", TextFormat::RED);
	}

	public function critical($message){
		$this->send($message, \LogLevel::CRITICAL, "CRITICAL", TextFormat::RED);
	}

	public function error($message){
		$this->send($message, \LogLevel::ERROR, "ERROR", TextFormat::DARK_RED);
	}

	public function warning($message){
		$this->send($message, \LogLevel::WARNING, "WARNING", TextFormat::YELLOW);
	}

	public function notice($message){
		$this->send($message, \LogLevel::NOTICE, "NOTICE", TextFormat::AQUA);
	}

	public function info($message){
		$this->send($message, \LogLevel::INFO, "INFO", TextFormat::WHITE);
	}

	public function debug($message){
		if($this->logDebug === false){
			return;
		}
		$this->send($message, \LogLevel::DEBUG, "DEBUG", TextFormat::GRAY);
	}

	/**
	 * @param bool $logDebug
	 */
	public function setLogDebug(bool $logDebug){
		$this->logDebug = $logDebug;
	}

	public function logException(\Throwable $e, $trace = null){
		$this->critical(implode("\n", Utils::printableExceptionInfo($e, $trace)));
	}

	public function log($level, $message){
		switch($level){
			case LogLevel::EMERGENCY:
				$this->emergency($message);
				break;
			case LogLevel::ALERT:
				$this->alert($message);
				break;
			case LogLevel::CRITICAL:
				$this->critical($message);
				break;
			case LogLevel::ERROR:
				$this->error($message);
				break;
			case LogLevel::WARNING:
				$this->warning($message);
				break;
			case LogLevel::NOTICE:
				$this->notice($message);
				break;
			case LogLevel::INFO:
				$this->info($message);
				break;
			case LogLevel::DEBUG:
				$this->debug($message);
				break;
		}
	}

	public function shutdown(){
		$this->shutdown = true;
		$this->notify();
	}

	protected function send($message, $level, $prefix, $color){
		$time = new \DateTime('now', new \DateTimeZone($this->timezone));

		$thread = \Thread::getCurrentThread();
		if($thread === null){
			$threadName = "Server thread";
		}elseif($thread instanceof Thread or $thread instanceof Worker){
			$threadName = $thread->getThreadName() . " thread";
		}else{
			$threadName = (new \ReflectionClass($thread))->getShortName() . " thread";
		}

		//$message = TextFormat::toANSI(TextFormat::AQUA . "[" . date("H:i:s") . "] " . TextFormat::RESET . $color . "[" . $threadName . "/" . $prefix . "]:" . " " . $message . TextFormat::RESET);
		$message = TextFormat::toANSI(sprintf($this->format, $time->format("H:i:s.v"), $color, $threadName, $prefix, TextFormat::clean($message, false)));
		$cleanMessage = TextFormat::clean($message);

		if(!Terminal::hasFormattingCodes()){
			echo $cleanMessage . PHP_EOL;
		}else{
			echo $message . PHP_EOL;
		}

		if($this->attachment instanceof \ThreadedLoggerAttachment){
			$this->attachment->call($level, $message);
		}

		$this->logStream[] = $time->format("Y-m-d") . " " . $cleanMessage . PHP_EOL;
	}

	/**
	 * @param resource $logResource
	 */
	private function writeLogStream($logResource){
		while($this->logStream->count() > 0){
			$chunk = $this->logStream->shift();
			fwrite($logResource, $chunk);
		}
	}

	public function run(){
		$this->shutdown = false;
		$logResource = fopen($this->logFile, "ab");
		if(!is_resource($logResource)){
			throw new \RuntimeException("Couldn't open log file");
		}

		while($this->shutdown === false){
			$this->writeLogStream($logResource);
			$this->synchronized(function(){
				$this->wait(25000);
			});
		}

		$this->writeLogStream($logResource);

		fclose($logResource);
	}
}
