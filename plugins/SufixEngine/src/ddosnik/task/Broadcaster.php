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
        ($server = $this->getOwner()->getServer())->broadcastMessage(Translate::tr($server->getLanguage()->getLang(), Loader::MESSAGES[$this->message]));
        $this->message++;
        if($this->message > (sizeof(Loader::MESSAGES) - 1)) {
            $this->message = 0;
        }
    }
}