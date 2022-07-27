<?php

/*
 *
 *  _____   _____   __   _   _   _____  __    __  _____
 * /  ___| | ____| |  \ | | | | /  ___/ \ \  / / /  ___/
 * | |     | |__   |   \| | | | | |___   \ \/ /  | |___
 * | |  _  |  __|  | |\   | | | \___  \   \  /   \___  \
 * | |_| | | |___  | | \  | | |  ___| |   / /     ___| |
 * \_____/ |_____| |_|  \_| |_| /_____/  /_/     /_____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;

abstract class FlyingAnimal extends Creature implements Ageable {

	protected $gravity = 0;
	protected $drag = 0.02;

	/** @var Vector3 */
	public $flyDirection = null;
	public $flySpeed = 0.5;
	public $highestY = 128;

	private $switchDirectionTicker = 0;
	public $switchDirectionTicks = 300;

	/**
	 * @param $tickDiff
	 *
	 * @return bool
	 */
	public function entityBaseTick($tickDiff = 1, $EnchantL = 0){
		if($this->closed !== false){
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff, $EnchantL);
		
		if($this->willMove(100)){
			if(++$this->switchDirectionTicker === $this->switchDirectionTicks){
				$this->switchDirectionTicker = 0;
				if(mt_rand(0, 100) < 50){
					$this->flyDirection = null;
				}
			}

			if(!$this->isFlaggedForDespawn()){
				if($this->y > $this->highestY and $this->flyDirection !== null){
					$this->flyDirection->y = -0.5;
				}

				$inAir = !$this->isInsideOfSolid() and !$this->isInsideOfWater();
				if(!$inAir){
					$this->flyDirection = null;
				}
				if($this->flyDirection instanceof Vector3){
					//var_dump($this->flyDirection);
					$this->setMotion($this->flyDirection->multiply($this->flySpeed));
				}else{
					$this->flyDirection = $this->generateRandomDirection();
					$this->flySpeed = mt_rand(50, 100) / 500;
					$this->setMotion($this->flyDirection);
				}
				
				$f = sqrt(($this->motionX ** 2) + ($this->motionZ ** 2));
				$this->yaw = (-atan2($this->motionX, $this->motionZ) * 180 / M_PI);
				$this->pitch = (-atan2($f, $this->motionY) * 180 / M_PI);

				if($this->onGround and $this->flyDirection instanceof Vector3){
					$this->flyDirection->y *= -1;
				}
			}
		}

		return $hasUpdate;
	}

	/**
	 * @return Vector3
	 */
	private function generateRandomDirection(){
		return new Vector3(mt_rand(-1000, 1000) / 1000, mt_rand(-500, 500) / 1000, mt_rand(-1000, 1000) / 1000);
	}

	public function initEntity(){
		parent::initEntity();
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_BABY, false);
	}

	/**
	 * @return bool
	 */
	public function isBaby(){
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_BABY);
	}

	/**
	 * @param float             $damage
	 * @param EntityDamageEvent $source
	 *
	 * @return bool|void
	 */
	public function attack($damage, EntityDamageEvent $source){
		if($source->isCancelled()){
			return;
		}
		if($source->getCause() == EntityDamageEvent::CAUSE_FALL){
			$source->setCancelled();
			return;
		}
		parent::attack($damage, $source);
	}

}
