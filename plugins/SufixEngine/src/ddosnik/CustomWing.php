<?php

declare(strict_types=1);

namespace ddosnik;

use pocketmine\math\Vector3;
use pocketmine\level\Position;

final class CustomWing {

	private float $scale = 0.3;
	private array $shape = [];
	private array $coords = [];

	public function __construct(array $shape) {
		$this->shape = $shape;
		$long1 = sizeof($shape);
		for ($y = 0; $y < $long1; $y++) {
			$long2 = count($this->shape[$y]);
			for ($x = 0; $x < $long2; $x++) {
				$flag = $shape[$y][$x];
				if ($flag === 0) continue;
				$kx = $x - (int) ($long2 / 2);
				$ky = ($y - (int) ($long1 / 2)) * (-1);
				$this->coords[] = [new Vector3($kx, $this->scale * $ky + 1.7), $flag];
			}
		}
	}

	public function draw(Position $pos, float $angle) : void{
		$level = $pos->getLevel();
		$sin = sin(deg2rad($angle));
		$cos = cos(deg2rad($angle));
		for ($i = 0; $i < sizeof($this->coords); $i++) {
			$r = $this->scale * $this->coords[$i][0]->x;
			$px = $r * $cos;
			$pz = $r * $sin;
			$level->addParticle(Loader::getInstance()->parseWings($pos->add($px, $this->coords[$i][0]->y, $pz), $this->coords[$i][1]));
		}
	}
}
