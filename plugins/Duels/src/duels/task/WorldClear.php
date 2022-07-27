<?php

declare(strict_types=1);

namespace duels\task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\level\Level;
use pocketmine\block\Block;
use pocketmine\Server;

use function serialize;
use function unserialize;

final class WorldClear extends AsyncTask{
	private $blocks;
	private $levelName;

	public function __construct(string $blocks, string $levelName){
		$this->blocks = $blocks;
		$this->levelName = $levelName;
	}

	final public function onRun() : void{
		$blocks = unserialize($this->blocks);
		$result = [0 => $this->levelName, 1 => []];
		$clear = [];
		foreach($blocks as $pos => $value){
			$pos = explode(';', $pos);
			$clear[] = ['x' => $pos[0], 'y' => $pos[1], 'z' => $pos[2]]; 
		}
		$result[1] = $clear;
		$this->setResult($result);
	}

	final public function onCompletion(Server $server) : void{
		$result = $this->getResult();
		$level = $server->getLevelByName($result[0]);
		foreach($result[1] as $pos){
			$level->setBlockIdAt((int)$pos['x'], (int)$pos['y'], (int)$pos['z'], 0);
		}
		//if($this->unload){
			//$server->unloadLevel($level);
		//}
	}
}
