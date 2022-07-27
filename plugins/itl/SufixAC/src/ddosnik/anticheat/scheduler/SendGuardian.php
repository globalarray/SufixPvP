<?php

declare(strict_types=1);

namespace ddosnik\anticheat\scheduler;

use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class SendGuardian extends Task{
    private $player;

    public function __construct(Player $player){
        $this->player = $player;
    }

    public function onRun(int $currentTick) : void{
        if($this->player->isOnline()){
            $pk = new LevelEventPacket();
            $pk->evid = 2006;
            $pk->data = 0;
            $pk->x = $this->player->x;
            $pk->y = $this->player->y;
            $pk->z = $this->player->z;
            $this->player->dataPacket($pk);
        }
    }
}