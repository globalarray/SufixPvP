<?php

/*
 * RakLib network library
 *
 *
 * This project is not affiliated with Jenkins Software LLC nor RakNet.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 */

namespace raklib\server;

use raklib\utils\InternetAddress;
use raklib\generic\Socket;
use Threaded;
use ThreadedLogger;
use ClassLoader;
use function array_reverse;
use function error_get_last;
use function error_reporting;
use function function_exists;
use function gc_enable;
use function get_class;
use function getcwd;
use function gettype;
use function ini_set;
use function is_object;
use function method_exists;
use function mt_rand;
use function realpath;
use function register_shutdown_function;
use function str_replace;
use function strval;
use function substr;
use function xdebug_get_function_stack;
use const DIRECTORY_SEPARATOR;
use const PHP_INT_MAX;
use const PTHREADS_INHERIT_NONE;

class RakLibServer extends \Thread{
	/** @var \ThreadedLogger */
	protected ThreadedLogger $logger;
	/** @var \ClassLoader */
	protected ClassLoader $loader;

	/** @var bool */
	protected $ready = false;

	/** @var InternetAddress */
	public InternetAddress $bindAddress;

	/** @var \ByteBuffer */
	//public ByteBuffer $binary;

	public $loadPaths;

	protected $shutdown;

	/** @var \Threaded */
	protected Threaded $externalQueue;
	/** @var \Threaded */
	protected Threaded $internalQueue;

	protected $mainPath;

	/** @var int */
	protected int $serverId = 0;

	/** @var \Throwable|null */
	public ?\Throwable $crashInfo = null;

	/**
	 * @param \ThreadedLogger $logger
	 * @param \ClassLoader    $loader
	 * @param int             $port
	 * @param string          $interface
	 *
	 * @throws \Exception
	 */
	public function __construct(ThreadedLogger $logger, ClassLoader $loader, InternetAddress $bindAddress) {
		$this->bindAddress = $bindAddress;
		$this->logger = $logger;
		$this->loader = $loader;
		$loadPaths = [];
		$this->addDependency($loadPaths, new \ReflectionClass($logger));
		$this->addDependency($loadPaths, new \ReflectionClass($loader));
		$this->loadPaths = array_reverse($loadPaths);
		$this->shutdown = false;

		$this->externalQueue = new Threaded;
		$this->internalQueue = new Threaded;

		if(\Phar::running(true) !== ""){
			$this->mainPath = \Phar::running(true);
		}else{
			$this->mainPath = \getcwd() . DIRECTORY_SEPARATOR;
		}
	}

	public function startAndWait(int $options = PTHREADS_INHERIT_NONE) : void{
		$this->start($options);
		$this->synchronized(function(){
			while(!$this->ready and $this->crashInfo === null){
				$this->wait();
			}
			if($this->crashInfo !== null){
				throw $this->crashInfo;
			}
		});
	}

	protected function addDependency(array &$loadPaths, \ReflectionClass $dep){
		if($dep->getFileName() !== false){
			$loadPaths[$dep->getName()] = $dep->getFileName();
		}

		if($dep->getParentClass() instanceof \ReflectionClass){
			$this->addDependency($loadPaths, $dep->getParentClass());
		}

		foreach($dep->getInterfaces() as $interface){
			$this->addDependency($loadPaths, $interface);
		}
	}

	public function isShutdown() : bool{
		return $this->shutdown === true;
	}

	public function shutdown() : void{
		$this->shutdown = true;
	}

	/**
	 * Returns RakNet server ID
	 *
	 * @return int
	 */
	public function getServerId() : int{
		return $this->serverId;
	}

	/**
	 * @return \ThreadedLogger
	 */
	public function getLogger() : \ThreadedLogger{
		return $this->logger;
	}

	/**
	 * @return \Threaded
	 */
	public function getExternalQueue() : \Threaded{
		return $this->externalQueue;
	}

	/**
	 * @return \Threaded
	 */
	public function getInternalQueue() : \Threaded{
		return $this->internalQueue;
	}

	public function pushMainToThreadPacket(string $str) : void{
		$this->internalQueue[] = $str;
	}

	public function readMainToThreadPacket() : mixed{
		return $this->internalQueue->shift();
	}

	public function pushThreadToMainPacket(string $str) : void{
		$this->externalQueue[] = $str;
	}

	public function readThreadToMainPacket() : mixed{
		return $this->externalQueue->shift();
	}

	public function shutdownHandler(){
		if($this->shutdown !== true){
			$error = error_get_last();
			if($error !== null){ //fatal error
				$this->setCrashInfo(new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
			}
		}
	}

	public function getCrashInfo() : ?\Throwable{
		return $this->crashInfo;
	}

	private function setCrashInfo(\Throwable $e){
		$this->synchronized(function($e){
			$this->crashInfo = $e;
			$this->notify();
		}, $e);
	}

	public function errorHandler($errno, $errstr, $errfile, $errline){
		if((error_reporting() & $errno) === 0){
			return false;
		}

		$errorConversion = [
			E_ERROR => "E_ERROR",
			E_WARNING => "E_WARNING",
			E_PARSE => "E_PARSE",
			E_NOTICE => "E_NOTICE",
			E_CORE_ERROR => "E_CORE_ERROR",
			E_CORE_WARNING => "E_CORE_WARNING",
			E_COMPILE_ERROR => "E_COMPILE_ERROR",
			E_COMPILE_WARNING => "E_COMPILE_WARNING",
			E_USER_ERROR => "E_USER_ERROR",
			E_USER_WARNING => "E_USER_WARNING",
			E_USER_NOTICE => "E_USER_NOTICE",
			E_STRICT => "E_STRICT",
			E_RECOVERABLE_ERROR => "E_RECOVERABLE_ERROR",
			E_DEPRECATED => "E_DEPRECATED",
			E_USER_DEPRECATED => "E_USER_DEPRECATED"
		];

		$errno = $errorConversion[$errno] ?? $errno;

		$errstr = preg_replace('/\s+/', ' ', trim($errstr));
		$errfile = $this->cleanPath($errfile);

		$this->getLogger()->debug("An $errno error happened: \"$errstr\" in \"$errfile\" at line $errline");

		foreach($this->getTrace(2) as $i => $line){
			$this->getLogger()->debug($line);
		}

		return true;
	}


	public function getTrace($start = 1, $trace = null){
		if($trace === null){
			if(function_exists("xdebug_get_function_stack")){
				$trace = array_reverse(xdebug_get_function_stack());
			}else{
				$e = new \Exception();
				$trace = $e->getTrace();
			}
		}

		$messages = [];
		$j = 0;
		for($i = (int) $start; isset($trace[$i]); ++$i, ++$j){
			$params = "";
			if(isset($trace[$i]["args"]) or isset($trace[$i]["params"])){
				if(isset($trace[$i]["args"])){
					$args = $trace[$i]["args"];
				}else{
					$args = $trace[$i]["params"];
				}
				foreach($args as $name => $value){
					$params .= (is_object($value) ? get_class($value) . " " . (method_exists($value, "__toString") ? $value->__toString() : "object") : gettype($value) . " " . @strval($value)) . ", ";
				}
			}
			$messages[] = "#$j " . (isset($trace[$i]["file"]) ? $this->cleanPath($trace[$i]["file"]) : "") . "(" . (isset($trace[$i]["line"]) ? $trace[$i]["line"] : "") . "): " . (isset($trace[$i]["class"]) ? $trace[$i]["class"] . (($trace[$i]["type"] === "dynamic" or $trace[$i]["type"] === "->") ? "->" : "::") : "") . $trace[$i]["function"] . "(" . substr($params, 0, -2) . ")";
		}

		return $messages;
	}

	public function cleanPath($path) : string{
		return rtrim(str_replace(["\\", ".php", "phar://", rtrim(str_replace(["\\", "phar://"], ["/", ""], $this->mainPath), "/")], ["/", "", "", ""], $path), "/");
	}

	public function run(){
		try{
			//Load removed dependencies, can't use require_once()
			foreach($this->loadPaths as $name => $path){
				if(!class_exists($name, false) and !interface_exists($name, false)){
					require($path);
				}
			}
			$this->loader->register(true);

			gc_enable();
			error_reporting(-1);
			ini_set("display_errors", 1);
			ini_set("display_startup_errors", 1);

			set_error_handler([$this, "errorHandler"], E_ALL);
			register_shutdown_function([$this, "shutdownHandler"]);


			$socket = new Socket($this->bindAddress);
			$manager = new SessionManager($this, $socket);
			$this->serverId = $manager->getID();
			$this->synchronized(function(){
				$this->ready = true;
				$this->notify();
			});
			$manager->run();
		}catch(\Throwable $e){
			$this->logger->logException($e);
		}
	}

}
