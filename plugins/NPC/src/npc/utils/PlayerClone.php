<?php

declare(strict_types=1);

namespace npc\utils;

use pocketmine\utils\UUID;
use pocketmine\entity\Entity;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\Player;

final class PlayerClone{
	private UUID $uuid;
	private int $entityId;
	private Position $pos;
	private string $skinId;
	private string $skinData;
	private string $nametag;
	private float $yaw;
	private float $pitch;

	public function __construct(Position $pos, string $skinId, string $skinData, string $nametag, float $yaw = 0.0, float $pitch = 0.0){
		$this->uuid = UUID::fromRandom();
		$this->entityId = Entity::$entityCount++;
		$this->pos = $pos;
		$this->skinId = $skinId;
		$this->skinData = $skinData;
		$this->nametag = $nametag;
		$this->yaw = $yaw;
		$this->pitch = $pitch;
	}

	final public function getEntityId() : int{
		return $this->entityId;
	}

	final public function spawnTo(Player $player) : void{
		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_ADD;
		$pk->entries[] = [$this->uuid, $this->entityId, $this->nametag, $this->skinId, $this->skinData];
		$player->dataPacket($pk);

		$pk = new AddPlayerPacket();
		$pk->uuid = $this->uuid;
		$pk->username = $this->nametag;
		$pk->entityRuntimeId = $this->entityId;
		$pk->x = $this->pos->x;
		$pk->y = $this->pos->y;
		$pk->z = $this->pos->z;
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->item = Item::get(Item::AIR);
		$flags = (
			(0 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG) |
			(0 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG) |
			(1 << Entity::DATA_FLAG_IMMOBILE)
		);
		$pk->metadata = [
			Entity::DATA_FLAGS => [Entity::DATA_TYPE_BYTE, $flags],
			Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->nametag]
		];
		$player->dataPacket($pk);

		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_REMOVE;
		$pk->entries[] = [$this->uuid];
		$player->dataPacket($pk);
	}

	final public function getWorld() : Level{
		return $this->pos->level;
	}

	final public function despawnFrom(Player $player) : void{
		$pk = new RemoveEntityPacket();
		$pk->entityUniqueId = $this->entityId;
		$player->dataPacket($pk);
	}
}
