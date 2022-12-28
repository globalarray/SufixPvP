<?php

namespace ddosnik\task;

use pocketmine\scheduler\Task;
use pocketmine\item\Item;
use pocketmine\Player;
use ddosnik\CustomWing;

class WingsTask extends Task {

	/** @var Player */
	private Player $player;
	/** @var array */
	private array $shape = [];

	public function __construct(Player $player, array $shape) {
		$this->player = $player;
		$this->shape = $shape;
	}

	public function onRun(int $tickDiff) : void{
		$wings = new CustomWing($this->shape);
		$wings->draw($this->player, $this->player->yaw);
	}
}
