<?php

namespace ddosnik\task;

use pocketmine\scheduler\PluginTask;
use pocketmine\lang\Translate;
use ddosnik\Loader;

final class Broadcaster extends PluginTask {
    private $message = 0;

    public function __construct(Loader $loader) {
        parent::__construct($loader);
    }

    public function onRun(int $currentTick) :void{
        foreach($this->getOwner()->getServer()->getOnlinePlayers() as $player) {
            Translate::tr($player->getLocale(), Loader::MESSAGES[$this->message]);
        }
        if($this->message > (sizeof(Loader::MESSAGES) - 1)) {
            $this->message = 0;
        }
    }
}