<?php

declare(strict_types=1);

namespace duels\task;

use pocketmine\scheduler\PluginTask;

use duels\Duels;
use duels\manager\ArenaManager;
use pocketmine\math\Vector3;
use pocketmine\item\ItemIds;
use pocketmine\block\TNT;

class DuelsArenaUpdate extends PluginTask {
    public function __construct(Duels $plugin) {
        parent::__construct($plugin);
    }
    
    public function onRun(int $currentTick) : void{
        foreach ($this->getOwner()->getServer()->getOnlinePlayers() as $player) {
            if (($game = ArenaManager::getGameByPlayer($player)) !== NULL) {
                $player->setNameTag($player->getRankColor() . $player->getName() . ' §7(§r§l' . $player->getHealth() . '§c❤§r§7)');
                if ($game->getGamemode() === 'tntrun' && $game->isStarted()) {
                    if ((microtime(true) - $game->last_move[$player->getLowerCaseName()]) > 4) {
                        $game->kill($player);
                        $player->sendMessage('§l§d» §r§fВы не §l§aдвигались§r §l§c5§r секунд. Игра §l§cпроиграна§r.');
                        continue;
                    }
                    if (($block = ($level = $player->getLevel())->getBlock(new Vector3(($x = $player->getFloorX()), ($y = $player->getFloorY()) - 2, $z = ($player->getFloorZ())))) instanceof TNT) {
                        $block->ignite();
                    }
                }
            }
        }
        foreach (ArenaManager::getArenas() as $name => $game) {
            $game->onUpdate();
        }
    }
}