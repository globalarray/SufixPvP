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
                    /*if ((microtime(true) - $game->last_move[$player->getLowerCaseName()]) > 4) {
                        $game->kill($player);
                        $player->sendMessage('§l§d» §r§fВы не §l§aдвигались§r §l§c5§r секунд. Игра §l§cпроиграна§r.');
                        continue;
                    }*/
                    /** @var Block */
                    $block = ($level = $player->getLevel())->getBlock(new Vector3($player->getFloorX(), $player->getFloorY() - 0.5, $player->getFloorZ()));
                    if ($block->getId() === 0) {
                      /** @var bool */
                      $brk = false;
                      for ($x = -0.33; $x < 0.34; $x += 0.33) {
                        for ($z = -0.33; $z < 0.34; $z += 0.33) {
                          /** @var Block */
                          $block = $level->getBlock(new Vector3($player->getX() - $x, $player->getY() - 0.5, $player->getZ() - $z));
                          if ($block->getId() === 12 || $block->getId() === 13 || $block->getId() === 46) {
                            $level->setBlockIdAt($block->asPosition()->getX(), $block->asPosition()->getY(), $block->asPosition()->getZ(), 0);
                            $brk = true;
                            break;
                          }
                        }
                      }
                    } else if ($block->getId() === 12 || $block->getId() === 13) {
                      $level->setBlockIdAt($block->asPosition()->getX(), $block->asPosition()->getY(), $block->asPosition()->getZ(), 0);
                    }
                    //if (($block = ($level = $player->getLevel())->getBlock(new Vector3(($x = $player->getFloorX()), ($y = $player->getFloorY()) - 2, $z = ($player->getFloorZ())))) instanceof TNT) {
                    //    $block->ignite();
                  //  }
                }
            }
        }
        foreach (ArenaManager::getArenas() as $name => $game) {
            $game->onUpdate();
        }
    }
}
