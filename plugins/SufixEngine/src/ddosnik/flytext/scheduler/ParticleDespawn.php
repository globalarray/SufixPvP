<?php

declare(strict_types=1);

namespace ddosnik\flytext\scheduler;


use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use function serialize;
use function unserialize;

class ParticleDespawn extends AsyncTask{
    private $particles;
    private $player;

    public function __construct(array $particles, string $player){
        $this->particles = serialize($particles);
        $this->player = serialize($player);
    }

    public function onRun() : void{
        $this->setResult([$this->player, $this->particles]);
    }

    public function onCompletion(Server $server) : void{
        $player = $server->getPlayer(unserialize($this->getResult()[0]));
        $particles = unserialize($this->getResult()[1]);
        foreach($particles as $particle){
            if($particle !== null and $player !== null) {
                $particle->despawnFrom($player);
            }
        }
    }
}