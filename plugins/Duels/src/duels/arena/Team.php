<?php

declare(strict_types=1);

namespace duels\arena;

class Team{
	private $name;
	private $players = [];
	
	public function __construct(string $name){
		$this->name = $name;
	}
	
	public function addPlayer(Player $player) : void{
		$this->players[$player->getName()] = $player;
	}
	
	public function removePlayer(Player $player) : void{
		unset($this->players[$player->getName()]);
	}
	
	public function getPlayers() : array{
		return $this->players;
	}
	
	public function reset() : void{
		$this->players = [];
	}
}