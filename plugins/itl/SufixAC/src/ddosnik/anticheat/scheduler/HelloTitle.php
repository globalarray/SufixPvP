<?php

namespace ddosnik\anticheat\scheduler;

use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\level\sound\MinecraftSound;

final class HelloTitle extends Task {
    public function __construct(Player $player) {
        $this->player = $player;
    }

    public function onRun(int $tickDiff) :void{
        if($this->player->isOnline()) {
            $this->player->addTitle('§l§dSufix§fPvP', 'Добро пожаловать');
            $this->player->getLevel()->addSound(new MinecraftSound($this->player, 'mob.cat.meow'), [$this->player]);
        }
    }
}