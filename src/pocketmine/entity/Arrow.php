<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_| 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 * 
 *
*/

namespace pocketmine\entity;

use pocketmine\event\inventory\InventoryPickupArrowEvent;
use pocketmine\item\Bow;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item as ItemItem;
use pocketmine\item\ItemIds;
use pocketmine\item\Potion;
use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\level\particle\MobSpellParticle;
use pocketmine\math\RayTraceResult;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\TakeItemEntityPacket;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\Player;

class Arrow extends Projectile {
	const NETWORK_ID = 80;

	public const PICKUP_NONE = 0;
	public const PICKUP_ANY = 1;
	public const PICKUP_CREATIVE = 2;

	private const TAG_PICKUP = "pickup"; //TAG_Byte

	public $width = 0.25;
	public $length = 0.25;
	public $height = 0.25;

	protected $gravity = 0.05;
	protected $drag = 0.01;

	protected $damage = 2.0;

	/** @var int */
	protected $pickupMode = self::PICKUP_ANY;

	protected $potionId;

	protected $bow;

	/**
	 * Arrow constructor.
	 *
	 * @param Level       $level
	 * @param CompoundTag $nbt
	 * @param Entity|null $shootingEntity
	 * @param bool        $critical
	 */
	public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null, bool $critical = false, Bow $bow = null){
		if(!isset($nbt->Potion)){
			$nbt->Potion = new ShortTag("Potion", 0);
		}
		parent::__construct($level, $nbt, $shootingEntity);
		$this->potionId = $this->namedtag["Potion"];
		$this->setCritical($critical);
		$this->bow = $bow;
	}

	protected function initEntity() : void{
		parent::initEntity();

		$this->pickupMode = $this->namedtag->{self::TAG_PICKUP} ?? self::PICKUP_ANY;
	}

	public function saveNBT() : void{
		parent::saveNBT();

		$this->namedtag->{self::TAG_PICKUP} = new ByteTag(self::TAG_PICKUP, $this->pickupMode);
	}

	public function getBow() : ?Bow{
		return $this->bow;
	}

	public function setBow(?Bow $bow){
		$this->bow = $bow;
	}

	/**
	 * @return bool
	 */
	public function isCritical() : bool{
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_CRITICAL);
	}

	/**
	 * @return int
	 */
	public function getPickupMode() : int{
		return $this->pickupMode;
	}

	/**
	 * @param int $pickupMode
	 */
	public function setPickupMode(int $pickupMode) : void{
		$this->pickupMode = $pickupMode;
	}

	/**
	 * @return void
	 */
	public function setCritical(bool $value = true) : void{
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_CRITICAL, $value);
	}

	/**
	 * @return int
	 */
	public function getResultDamage() : int{
		$base = parent::getResultDamage();
		if($this->isCritical()){
			return ($base + mt_rand(0, (int) ($base / 2) + 1));
		}else{
			return $base;
		}
	}

	/**
	 * @return int
	 */
	public function getPotionId() : int{
		return $this->potionId;
	}

	protected function onHit(ProjectileHitEvent $event) : void{
		$this->setCritical(false);
		$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_BOW_HIT);
	}

	protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void{
		parent::onHitBlock($blockHit, $hitResult);
		$this->broadcastEntityEvent(EntityEventPacket::ARROW_SHAKE, 7); //7 ticks
	}

	public function onCollideWithPlayer(Player $player) {
		if($this->blockHit === null){
			return;
		}

		$item = ItemItem::get(ItemIds::ARROW, $this->getPotionId());

		$playerInventory = $player->getInventory();

		$add = false;
		if(!$player->server->allowInventoryCheats and !$player->isCreative()){
			if(
				!$player->getFloatingInventory()->canAddItem($item) or
				!$player->getInventory()->canAddItem($item)
			){
				//The item is added to the floating inventory to allow client to handle the pickup
				//We have to also check if it can be added to the real inventory before sending packets.
				return;
			}
			$add = true;
		}

		$ev = new InventoryPickupArrowEvent($playerInventory, $this);
		if($this->pickupMode === self::PICKUP_NONE or ($this->pickupMode === self::PICKUP_CREATIVE and !$player->isCreative())){
			$ev->setCancelled();
		}

		if($this->getBow() !== null and $this->getBow()->hasEnchantment(Enchantment::TYPE_BOW_INFINITY)) {
			$ev->setCancelled();
		}

		$this->server->getPluginManager()->callEvent($ev);

		if($ev->isCancelled()){
			return;
		}

		$pk = new TakeItemEntityPacket();
		$pk->eid = $player->getId();
		$pk->target = $this->getId();
		$this->server->broadcastPacket($this->getViewers(), $pk);

		if($add){
			$player->getFloatingInventory()->addItem(clone $item);
		}

		$this->flagForDespawn();
	}

	/**
	 * @param $tickDiff
	 *
	 * @return bool
	 */
	public function entityBaseTick($tickDiff = 1){
		if($this->closed){
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if($this->potionId != 0){
			if(!$this->onGround or ($this->onGround and ($tickDiff % 4) == 0)){
				$color = Potion::getColor($this->potionId - 1);
				$this->level->addParticle(new MobSpellParticle($this->add(
					$this->width / 2 + mt_rand(-100, 100) / 500,
					$this->height / 2 + mt_rand(-100, 100) / 500,
					$this->width / 2 + mt_rand(-100, 100) / 500), $color[0], $color[1], $color[2]));
			}
			$hasUpdate = true;
		}

		if($this->blockHit !== null){
			if($this->age > 1200){
				$this->flagForDespawn();
				$hasUpdate = true;
			}
		}else{
			$this->age = 0;
		}

		return $hasUpdate;
	}

	/**
	 * @param Player $player
	 */
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->type = Arrow::NETWORK_ID;
		$pk->eid = $this->getId();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motionX;
		$pk->speedY = $this->motionY;
		$pk->speedZ = $this->motionZ;
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
}