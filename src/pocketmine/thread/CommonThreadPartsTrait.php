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

namespace pocketmine\thread;

use pocketmine\errorhandler\ErrorToExceptionHandler;
use pocketmine\Server;
use \ClassLoader;
use function error_reporting;

trait CommonThreadPartsTrait{
	/** @var \Threaded|\ClassLoader[]|null  */
	private ?\Threaded $classLoader;
	/** @var string|null */
	protected $composerAutoloaderPath;

	/** @var bool */
	protected $isKilled = false;

	/**
	 * @return \ClassLoader
	 */
	public function getClassLoader() : ?ClassLoader{
		return $this->classLoader;
	}

	/**
	 * @param \ClassLoader $loader
	*/
	public function setClassLoader(\ClassLoader $loader = null){
		if($loader === null){
			$loader = Server::getInstance()->getLoader();
		}
		$this->classLoader = $loader;
	}

	/**
	 * Registers the class loaders for this thread.
	 *
	 * WARNING: This method MUST be called from any descendent threads' run() method to make autoloading usable.
	 * If you do not do this, you will not be able to use new classes that were not loaded when the thread was started
	 * (unless you are using a custom autoloader).
	 */

	public function registerClassLoader() : void{
		if(!interface_exists("ClassLoader", false)){
			require(\pocketmine\PATH . "src/spl/ClassLoader.php");
			require(\pocketmine\PATH . "src/spl/BaseClassLoader.php");
		}
		if($this->classLoader !== null){
			$this->classLoader->register(true);
		}
	}

	final public function run() : void{
		error_reporting(-1);
		$this->registerClassLoader();
		//set this after the autoloader is registered
		ErrorToExceptionHandler::set();
		$this->onRun();
	}

	/**
	 * Runs code on the thread.
	 */
	abstract protected function onRun() : void;

	public function getThreadName() : string{
		return (new \ReflectionClass($this))->getShortName();
	}
}
