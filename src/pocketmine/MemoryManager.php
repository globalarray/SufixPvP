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

namespace pocketmine;

use pocketmine\event\server\LowMemoryEvent;
use pocketmine\timings\Timings;
use pocketmine\scheduler\GarbageCollectionTask;
use pocketmine\utils\Utils;
use pocketmine\utils\Process;
use function arsort;
use function count;
use function fclose;
use function file_exists;
use function file_put_contents;
use function fopen;
use function fwrite;
use function gc_collect_cycles;
use function gc_disable;
use function gc_enable;
use function gc_mem_caches;
use function get_class;
use function get_declared_classes;
use function get_defined_functions;
use function ini_get;
use function ini_set;
use function intdiv;
use function is_array;
use function is_object;
use function is_resource;
use function is_string;
use function json_encode;
use function mb_strtoupper;
use function min;
use function mkdir;
use function preg_match;
use function print_r;
use function round;
use function spl_object_hash;
use function sprintf;
use function strlen;
use function substr;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const SORT_NUMERIC;

class MemoryManager{

	private Server $server;

	private int $memoryLimit;
	private int $globalMemoryLimit;
	private int $checkRate;
	private int $checkTicker = 0;
	private bool $lowMemory = false;

	private bool $continuousTrigger = true;
	private int $continuousTriggerRate;
	private int $continuousTriggerCount = 0;
	private int $continuousTriggerTicker = 0;

	private int $garbageCollectionPeriod;
	private int $garbageCollectionTicker = 0;
	private bool $garbageCollectionTrigger;
	private bool $garbageCollectionAsync;

	private int $chunkRadiusOverride;
	private bool $chunkCollect;
	private bool $chunkTrigger;

	private bool $chunkCache;
	private bool $cacheTrigger;

	public function __construct(Server $server) {
		$this->server = $server;

		$this->init($server->getConfigGroup());
	}

	private function init(ServerConfigGroup $config) : void{
		$this->memoryLimit = $config->getPropertyInt("memory.main-limit", 0) * 1024 * 1024;

		$defaultMemory = 1024;

		if(preg_match("/([0-9]+)([KMGkmg])/", $config->getConfigString("memory-limit", ""), $matches) > 0){
			$m = (int) $matches[1];
			if($m <= 0){
				$defaultMemory = 0;
			}else{
				switch(strtoupper($matches[2])){
					case "K":
						$defaultMemory = $m / 1024;
						break;
					case "M":
						$defaultMemory = $m;
						break;
					case "G":
						$defaultMemory = $m * 1024;
						break;
					default:
						$defaultMemory = $m;
						break;
				}
			}
		}

		$hardLimit = $config->getPropertyInt("memory.main-hard-limit", $defaultMemory);

		if($hardLimit <= 0){
			ini_set("memory_limit", '-1');
		}else{
			ini_set("memory_limit", $hardLimit . "M");
		}

		$this->globalMemoryLimit = $config->getPropertyInt("memory.global-limit", 0) * 1024 * 1024;
		$this->checkRate = $config->getPropertyInt("memory.check-rate", 20);
		$this->continuousTrigger = $config->getPropertyBool("memory.continuous-trigger", true);
		$this->continuousTriggerRate = $config->getPropertyInt("memory.continuous-trigger-rate", 30);

		$this->garbageCollectionPeriod = $config->getPropertyInt("memory.garbage-collection.period", 36000);
		$this->garbageCollectionTrigger = $config->getPropertyBool("memory.garbage-collection.low-memory-trigger", true);
		$this->garbageCollectionAsync = $config->getPropertyBool("memory.garbage-collection.collect-async-worker", true);

		$this->chunkRadiusOverride = $config->getPropertyInt("memory.max-chunks.chunk-radius", 4);
		$this->chunkCollect = $config->getPropertyBool("memory.max-chunks.trigger-chunk-collect", true);
		$this->chunkTrigger = $config->getPropertyBool("memory.max-chunks.low-memory-trigger", true);

		$this->chunkCache = $config->getPropertyBool("memory.world-caches.disable-chunk-cache", true);
		$this->cacheTrigger = $config->getPropertyBool("memory.world-caches.low-memory-trigger", true);

		gc_enable();
	}

	public function isLowMemory() : bool{
		return $this->lowMemory;
	}

	public function canUseChunkCache() : bool{
		return !($this->lowMemory and $this->chunkTrigger);
	}

	/**
	 * Returns the allowed chunk radius based on the current memory usage.
	 *
	 * @param int $distance
	 *
	 * @return int
	 */
	public function getViewDistance(int $distance) : int{
		return $this->lowMemory ? min($this->chunkRadiusOverride, $distance) : $distance;
	}

	public function trigger($memory, $limit, $global = false, $triggerCount = 0){
		$this->server->getLogger()->debug(sprintf("[Memory Manager] %sLow memory triggered, limit %gMB, using %gMB",
			$global ? "Global " : "", round(($limit / 1024) / 1024, 2), round(($memory / 1024) / 1024, 2)));
		if($this->cacheTrigger){
			foreach($this->server->getLevels() as $level){
				$level->clearCache(true);
			}
		}

		if($this->chunkTrigger and $this->chunkCollect){
			foreach($this->server->getLevels() as $level){
				$level->doChunkGarbageCollection();
			}
		}

		$ev = new LowMemoryEvent($memory, $limit, $global, $triggerCount);
		$this->server->getPluginManager()->callEvent($ev);

		$cycles = 0;
		if($this->garbageCollectionTrigger){
			$cycles = $this->triggerGarbageCollector();
		}

		$this->server->getLogger()->debug(sprintf("[Memory Manager] Freed %gMB, $cycles cycles", round(($ev->getMemoryFreed() / 1024) / 1024, 2)));
	}

	public function check(){
		Timings::$memoryManager->startTiming();

		if(($this->memoryLimit > 0 or $this->globalMemoryLimit > 0) and ++$this->checkTicker >= $this->checkRate){
			$this->checkTicker = 0;
			$memory = Process::getAdvancedMemoryUsage();
			$trigger = false;
			if($this->memoryLimit > 0 and $memory[0] > $this->memoryLimit){
				$trigger = 0;
			}elseif($this->globalMemoryLimit > 0 and $memory[1] > $this->globalMemoryLimit){
				$trigger = 1;
			}

			if($trigger !== false){
				if($this->lowMemory and $this->continuousTrigger){
					if(++$this->continuousTriggerTicker >= $this->continuousTriggerRate){
						$this->continuousTriggerTicker = 0;
						$this->trigger($memory[$trigger], $this->memoryLimit, $trigger > 0, ++$this->continuousTriggerCount);
					}
				}else{
					$this->lowMemory = true;
					$this->continuousTriggerCount = 0;
					$this->trigger($memory[$trigger], $this->memoryLimit, $trigger > 0);
				}
			}else{
				$this->lowMemory = false;
			}
		}

		if($this->garbageCollectionPeriod > 0 and ++$this->garbageCollectionTicker >= $this->garbageCollectionPeriod){
			$this->garbageCollectionTicker = 0;
			$this->triggerGarbageCollector();
		}

		Timings::$memoryManager->stopTiming();
	}

	public function triggerGarbageCollector(){
		Timings::$garbageCollector->startTiming();

		if($this->garbageCollectionAsync){
			$size = $this->server->getScheduler()->getAsyncTaskPoolSize();
			for($i = 0; $i < $size; ++$i){
				$this->server->getScheduler()->scheduleAsyncTaskToWorker(new GarbageCollectionTask(), $i);
			}
		}

		$cycles = gc_collect_cycles();
		gc_mem_caches();

		Timings::$garbageCollector->stopTiming();

		return $cycles;
	}

	public function dumpServerMemory($outputFolder, $maxNesting, $maxStringSize){
		$hardLimit = Utils::assumeNotFalse(ini_get('memory_limit'), "memory_limit INI directive should always exist");
		ini_set('memory_limit', '-1');
		gc_disable();

		if(!file_exists($outputFolder)){
			mkdir($outputFolder, 0777, true);
		}

		$logger = new \PrefixedLogger($this->server->getLogger(), "Memory Dump");
		$logger->notice("After the memory dump is done, the server might crash");

		$obData = fopen($outputFolder . "/objects.js", "wb+");

		$staticProperties = [];

		$functionStaticVars = [];
		$functionStaticVarsCount = 0;

		$data = [];

		$objects = [];

		$refCounts = [];

		$instanceCounts = [];

		$staticCount = 0;
		foreach($this->server->getLoader()->getClasses() as $className){
			$reflection = new \ReflectionClass($className);
			$staticProperties[$className] = [];
			foreach($reflection->getProperties() as $property){
				if(!$property->isStatic() or $property->getDeclaringClass()->getName() !== $className){
					continue;
				}

				if(!$property->isPublic()){
					$property->setAccessible(true);
				}

				$staticCount++;
				if ($reflection->isTrait()) continue;
				$staticProperties[$className][$property->getName()] = self::continueDump($property->getValue(), $objects, $refCounts, 0, $maxNesting, $maxStringSize);
			}

			if(count($staticProperties[$className]) === 0){
				unset($staticProperties[$className]);
			}
		}

		$logger->info("Wrote $staticCount static properties");

		$this->continueDump($this->server, $data, $objects, $refCounts, 0, $maxNesting, $maxStringSize);

		$globalVariables = [];
		$globalCount = 0;

		$ignoredGlobals = [
			'GLOBALS' => true,
			'_SERVER' => true,
			'_REQUEST' => true,
			'_POST' => true,
			'_GET' => true,
			'_FILES' => true,
			'_ENV' => true,
			'_COOKIE' => true,
			'_SESSION' => true
		];

		foreach(Utils::stringifyKeys($GLOBALS) as $varName => $value){
			if(isset($ignoredGlobals[$varName])){
				continue;
			}

			$globalCount++;
			$globalVariables[$varName] = self::continueDump($value, $objects, $refCounts, 0, $maxNesting, $maxStringSize);
		}

		$logger->info("Wrote $globalCount global variables");

		foreach(get_defined_functions()["user"] as $function){
			$reflect = new \ReflectionFunction($function);

			$vars = [];
			foreach($reflect->getStaticVariables() as $varName => $variable){
				$vars[$varName] = self::continueDump($variable, $objects, $refCounts, 0, $maxNesting, $maxStringSize);
			}
			if(count($vars) > 0){
				$functionStaticVars[$function] = $vars;
				$functionStaticVarsCount += count($vars);
			}
		}

		$logger->info("Wrote $functionStaticVarsCount function static variables");

		do{
			$continue = false;
			foreach($objects as $hash => $object){
				if(!is_object($object)){
					continue;
				}
				$continue = true;

				$className = get_class($object);
				if(!isset($instanceCounts[$className])){
					$instanceCounts[$className] = 1;
				}else{
					$instanceCounts[$className]++;
				}

				$objects[$hash] = true;

				$reflection = new \ReflectionObject($object);

				$info = [
					"information" => "$hash@$className",
					"properties" => []
				];

				if($reflection->getParentClass()){
					$info["parent"] = $reflection->getParentClass()->getName();
				}

				if(count($reflection->getInterfaceNames()) > 0){
					$info["implements"] = implode(", ", $reflection->getInterfaceNames());
				}

				foreach($reflection->getProperties() as $property){
					if($property->isStatic()){
						continue;
					}

					if(!$property->isPublic()){
						$property->setAccessible(true);
					}
					$this->continueDump($property->getValue($object), $info["properties"][$property->getName()], $objects, $refCounts, 0, $maxNesting, $maxStringSize);
				}

				fwrite($obData, "$hash@$className: " . json_encode($info, JSON_UNESCAPED_SLASHES) . "\n");
			}

			$logger->info('Wrote ' . count($objects) . ' objects');
		}while($continue);

		fclose($obData);

		file_put_contents($outputFolder . '/staticProperties.js', json_encode($staticProperties, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
		file_put_contents($outputFolder . '/serverEntry.js', json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
		file_put_contents($outputFolder . '/functionStaticVars.js', json_encode($functionStaticVars, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
		file_put_contents($outputFolder . '/referenceCounts.js', json_encode($refCounts, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
		file_put_contents($outputFolder . '/globalVariables.js', json_encode($globalVariables, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

		arsort($instanceCounts, SORT_NUMERIC);
		file_put_contents($outputFolder . "/instanceCounts.js", json_encode($instanceCounts, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

		$logger->info("Finished!");

		ini_set('memory_limit', $hardLimit);
		gc_enable();
	}

	private function continueDump($from, &$objects, &$refCounts, $recursion, $maxNesting, $maxStringSize){
		if($maxNesting <= 0){
			$data = "(error) NESTING LIMIT REACHED";
			return;
		}

		--$maxNesting;

		if(is_object($from)){
			if(!isset($objects[$hash = spl_object_hash($from)])){
				$objects[$hash] = $from;
				$refCounts[$hash] = 0;
			}

			++$refCounts[$hash];

			$data = "(object) $hash@" . get_class($from);
		}elseif(is_array($from)){
			if($recursion >= 5){
				$data = "(error) ARRAY RECURSION LIMIT REACHED";
				return;
			}
			$data = [];
			$numeric = 0;
			foreach($from as $key => $value){
				$data[$numeric] = [
					"k" => self::continueDump($key, $objects, $refCounts, $recursion + 1, $maxNesting, $maxStringSize),
					"v" => self::continueDump($value, $objects, $refCounts, $recursion + 1, $maxNesting, $maxStringSize),
				];
				$numeric++;
			}
		}elseif(is_string($from)){
			$data = "(string) len(". strlen($from) .") " . substr(Utils::printable($from), 0, $maxStringSize);
		}elseif(is_resource($from)){
			$data = "(resource) " . print_r($from, true);
		}else{
			$data = $from;
		}
	}
}
