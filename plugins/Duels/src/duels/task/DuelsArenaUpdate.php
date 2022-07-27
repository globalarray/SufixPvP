<?php

declare(strict_types=1);

namespace duels\task;

use pocketmine\scheduler\PluginTask;

use duels\Duels;
use duels\manager\ArenaManager;

class DuelsArenaUpdate extends PluginTask {
    public function __construct(Duels $plugin) {
        parent::__construct($plugin);
    }
    
    public function onRun(int $currentTick) : void{
        foreach ($this->getOwner()->getServer()->getOnlinePlayers() as $player) {
        //    $player->sendTip(''.$player->getPing().'');
        }
        foreach (ArenaManager::getArenas() as $name => $game) {
            $game->onUpdate();
        }
    }
}