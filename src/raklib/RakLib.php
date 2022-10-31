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

declare(strict_types=1);

namespace raklib;

use function defined;
use function extension_loaded;
use function phpversion;
use function substr_count;
use function version_compare;
use const PHP_EOL;
use const PHP_VERSION;

//Dependencies check
$errors = 0;
if(version_compare("8.1", PHP_VERSION) > 0){
	echo "[CRITICAL] Use PHP >= 8.1" . PHP_EOL;
	++$errors;
}

if(!extension_loaded("sockets")){
	echo "[CRITICAL] Unable to find the Socket extension." . PHP_EOL;
	++$errors;
}

if(!extension_loaded("pthreads")){
	echo "[CRITICAL] Unable to find the pthreads extension." . PHP_EOL;
	++$errors;
}else{
	$pthreads_version = phpversion("pthreads");
	if(substr_count($pthreads_version, ".") < 2){
		$pthreads_version = "0.$pthreads_version";
	}

	if(version_compare($pthreads_version, "4.1.3") < 0){
		echo "[CRITICAL] pthreads >= 4.1.3 is required, while you have $pthreads_version.";
		++$errors;
	}
}

if($errors > 0){
	exit(1); //Exit with error
}
unset($errors);

abstract class RakLib{
	public const VERSION = "0.8.0-SE";
	public const PROTOCOL = 6;
	public const MAGIC = "\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78";

	public const PRIORITY_NORMAL = 0;
	public const PRIORITY_IMMEDIATE = 1;

	public const FLAG_NEED_ACK = 0b00001000;

	/**
	 * Regular RakNet uses 10 by default. MCPE uses 20. Configure this value as appropriate.
	 * @var int
	 */
	public static $SYSTEM_ADDRESS_COUNT = 20;

	public static function bootstrap(\ClassLoader $loader){
		$loader->addPath(dirname(__FILE__) . DIRECTORY_SEPARATOR . "..");
	}
}