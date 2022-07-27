<?php

namespace ddosnik\task;

use ddosnik\Loader;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;

final class LeaveTask extends PluginTask {

    public function __construct(Loader $loader) {
        parent::__construct($loader);
    }

    public function onRun(int $tickDiff) : void{
        foreach($this->getOwner()->players as $player=>$time) {
            if((time() - $time) > $this->getOwner()->interval) {
                if($this->getOwner()->getServer()->getPlayer($player) instanceof Player) {
                    unset($this->getOwner()->players[$player]);
                }
            }
        }
    }
}
