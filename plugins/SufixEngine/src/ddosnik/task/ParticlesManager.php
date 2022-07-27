<?php

namespace ddosnik\task;

use ddosnik\Loader;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\level\particle\Particle;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\level\particle\{
	HappyVillagerParticle, 
	HeartParticle, 
	BlackHeartParticle,
	RainSplashParticle,
	FlameParticle
};

use function lcg_value;
use function cos;
use function sin;

use const M_PI;

final class ParticlesManager extends PluginTask {

	public function __construct(Loader $loader) {
		parent::__construct($loader);
	}

	private function sendParticlePacket(Player $player, int $id, int $count, $data = 0) : void{
		for($i = 0; $i < $count; ++$i) {
			$distance = -0.5 + lcg_value();
			$yaw = $player->yaw * M_PI / 180 + (-0.5 + lcg_value()) * 90;
			$x = $distance * cos($yaw);
			$z = $distance * sin($yaw);
			$y = lcg_value() * 0.4 + 0.5;
			$pos = $player->add($x, $y, $z);
			$pk = new LevelEventPacket();
			$pk->evid = LevelEventPacket::EVENT_ADD_PARTICLE_MASK | $id;
			$pk->x = $pos->x;
			$pk->y = $pos->y;
			$pk->z = $pos->z;
			$pk->data = $data;
			foreach($player->getLevel()->getPlayers() as $player){
				$player->dataPacket($pk);
			}
		}
	}

	private function sendWithClass(Player $player, string $class, int $count) : void{
		for($i = 0; $i < $count; ++$i){
			$distance = -0.5 + lcg_value();
			$yaw = $player->yaw * M_PI / 180 + (-0.5 + lcg_value()) * 90;
			$x = $distance * cos($yaw);
			$z = $distance * sin($yaw);
			$y = lcg_value() * 0.4 + 0.5;
			$player->getLevel()->addParticle(new $class($player->add($x, $y, $z)));
		}
	}

	private function drawCenter(Player $player, int $id, float $radius, float $step) : void{
		for ($i = 0; $i < 361; $i++) {
			$x = $player->getX() + ($radius * cos($i));
			$z = $player->getZ() + ($radius * sin($i));
			$pk = new LevelEventPacket();
			$pk->evid = LevelEventPacket::EVENT_ADD_PARTICLE_MASK | $id;
			$pk->x = $x;
			$pk->y = $player->getX() + 2.2;
			$pk->z = $z;
			$pk->data = 0;
			var_dump($pk);
			foreach ($player->getLevel()->getPlayers() as $player) {
				$player->dataPacket($pk);
			}
		}
	}

	public function onRun(int $tickDiff) : void{
		 $particles = [
		 	'HEART' => [HeartParticle::class, 2],
		 	'HAPPY' => [HappyVillagerParticle::class, 2],
		 	'RAIN' => [RainSplashParticle::class, 3],
		 	'FLAME' => [FlameParticle::class, 3]
		 ];
		foreach ($this->getOwner()->getServer()->getOnlinePlayers() as $player) {
			if ($player instanceof SufixPlayer) {
				$particle = $player->getParticle();
				if (!$particle || $player->isSpectator()) {
					continue;
				}
				if ($particle === 'MELON') {
					//TODO
					return;
				}

				if ($particle === 'CUSTOM') {
					//TODO
					return;
				}

				$this->sendWithClass($player, $particles[$particle][0], $particles[$particle][0]);
			}
		}
	}
}
