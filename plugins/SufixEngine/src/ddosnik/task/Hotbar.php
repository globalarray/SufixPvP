<?php

namespace ddosnik\task;

use ddosnik\Loader;
use pocketmine\scheduler\PluginTask;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use const PHP_EOL;

final class Hotbar extends PluginTask {

    public function __construct(Loader $loader) {
        parent::__construct($loader);
    }

    public function onRun(int $tickDiff) : void{
        foreach($this->getOwner()->getServer()->getOnlinePlayers() as $player) {
            if ($player->inFFA()) {
                $this->getOwner()->sendHealthAttribute($player, 1);
                $bossTitle = '  §l§dSUFIX §fPVP §8| §r§7' . $player->getPingToString() .' §7ms ' . PHP_EOL . '§r§fEXP: §8[§l§c'. $player->getExperience() .'§8/§c'. $player->getExpNextLevel() .'§8]§r' . PHP_EOL . '§r§fPLAYERS ON ARENA: §l§c' . count($player->getLevel()->getPlayers()) . PHP_EOL . '§r§7     pay.sufixpvp.su';
                $this->getOwner()->sendBoss($player, $bossTitle);
                $this->getOwner()->setBossTitle($player, $bossTitle);
            }
        }
    }
}