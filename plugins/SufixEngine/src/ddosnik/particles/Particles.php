<?php

namespace ddosnik\particles;

use pocketmine\level\particle\Particle;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;

final class Particles extends Particle {

    private int $type;

    public function __construct(int $particleType, Vector3 $pos) {
    	$this->type = $particleType;
    	parent::__construct($pos->getX(), $pos->getY(), $pos->getZ());
    }

    public function encode() {
    	$pk = new LevelEventPacket();
    	$pk->evid = LevelEventPacket::EVENT_ADD_PARTICLE_MASK | $this->type;
    	$pk->x = $this->getX();
    	$pk->y = $this->getY();
    	$pk->z = $this->getZ();
    	$pk->data = 0;
    	return $pk;
    }
}
