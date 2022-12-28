<?php

declare(strict_types=1);

namespace ddosnik\particles;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\{SetEntityDataPacket, AddEntityPacket, RemoveEntityPacket};
use pocketmine\entity\Entity;
use pocketmine\{Server, Player};

final class TextParticle
{
	private int $id;
	private string $title;
	private string $text;
	private Vector3 $position;
	
	private int $entityRuntimeId;
	
	public function __construct(int $id, string $title, string $text, Vector3 $pos)
	{
		$this->id = $id;
		$this->title = $title;
		$this->text = $text;
		$this->position = $pos;
		
		$this->entityRuntimeId = mt_rand(-1, PHP_INT_MAX);
	}
	
	public function getId() : string{
		return $this->id;
	}

	public function updateText(string $text, ?array $players = null) : void
	{
		$pk = new SetEntityDataPacket();
		$pk->entityRuntimeId = $this->entityRuntimeId;
		$pk->metadata = [Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $text]];
		if($players === null){
			foreach(Server::getInstance()->getOnlinePlayers() as $player){
				$player->dataPacket($pk);
			}
		}else{
			foreach($players as $player){
				$player->dataPacket($pk);
			}
		}
	}
	
	public function setTitle(string $title, ?array $players = null) : void
	{
		$this->title = $title;
		$this->updateText($this->title . ($this->text !== '' ? PHP_EOL . $this->text : ''), $players);
	}
	
	public function setText(string $text) : void
	{
		$this->text = $text;
		$this->updateText($this->title . ($this->text !== '' ? PHP_EOL . $this->text : ''));
	}
	
	public function spawnTo(Player $player) : void
	{
		$pk = new AddEntityPacket();
		$pk->entityRuntimeId = $this->entityRuntimeId;
		$pk->type = 33;
		$pk->x = $this->position->x;
		$pk->y = $this->position->y + 0.5;
		$pk->z = $this->position->z;
		$flags = (
				(1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG) |
				(1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG) |
				(1 << Entity::DATA_FLAG_IMMOBILE)
		);
		$pk->metadata = [
			Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
			Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->title . ($this->text !== '' ? PHP_EOL . $this->text : '')],
			Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
		];
		$player->dataPacket($pk);
	}
	
	public function despawnFrom(Player $player) : void
	{
		$pk = new RemoveEntityPacket();
		$pk->entityUniqueId = $this->entityRuntimeId;
		$player->dataPacket($pk);
	}
}