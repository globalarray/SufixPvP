<?php

declare(strict_types=1);

namespace duels\task;

use pocketmine\scheduler\PluginTask;

use duels\Duels;
use duels\manager\ArenaManager;
use pocketmine\item\ItemIds;

class DuelsArenaUpdate extends PluginTask {
    public function __construct(Duels $plugin) {
        parent::__construct($plugin);
    }
    
    public function onRun(int $currentTick) : void{
        foreach ($this->getOwner()->getServer()->getOnlinePlayers() as $player) {
            if (($game = ArenaManager::getGameByPlayer($player)) !== NULL) {
                if ($game->getGamemode() === 'tntrun') {
                    if ($level = ($player->getLevel())->getBlockIdAt(($x = $player->getX()), ($y = $player->getY()) - 2, $z = ($player->getZ())) === ItemIds::TNT) {
                        $level->setBlockIdAt($x, $y - 2, $z, ItemIds::AIR);
                        $level->setBlockIdAt($x, $y - 1, $z, ItemIds::AIR);
                    }
                }
            }
        }
        foreach (ArenaManager::getArenas() as $name => $game) {
            $game->onUpdate();
        }
    }
}