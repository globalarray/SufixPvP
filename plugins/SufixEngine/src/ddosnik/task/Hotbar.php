<?php

namespace ddosnik\task;

use ddosnik\Loader;
use pocketmine\scheduler\PluginTask;

final class Hotbar extends PluginTask {

    public function __construct(Loader $loader) {
        parent::__construct($loader);
    }

    public function onRun(int $tickDiff) : void{
        foreach($this->getOwner()->getServer()->getOnlinePlayers() as $player) {
            if(isset($this->getOwner()->ffaworlds[$player->getLevel()->getFolderName()])){
                $this->getOwner()->sendHealthAttribute($player, 1);
                $this->getOwner()->sendBoss($player, "  §l§dSUFIX §fPVP §8| §r§7{$this->getOwner()->getPing($player)} §7ms \n§r§fEXP: §8[§l§c".$this->getOwner()->getExp($player)."§8/§c".$this->getOwner()->getExpNextLevel($player)."§8]§r\n§r§7       pay.sufixpvp.su");
                $this->getOwner()->setBossTitle($player, "  §l§dSUFIX §fPVP §8| §r§7{$this->getOwner()->getPing($player)} §7ms \n§r§fEXP: §8[§l§c".$this->getOwner()->getExp($player)."§8/§c".$this->getOwner()->getExpNextLevel($player)."§8]§r\n§r§7       pay.sufixpvp.su");
            } else if($player->getLevel()->getFolderName() === "lobby") {
                $pk = new \pocketmine\network\mcpe\protocol\RemoveEntityPacket();
                $pk->entityUniqueId = 999888777;
                $player->dataPacket($pk);
            }
        }
    }
}