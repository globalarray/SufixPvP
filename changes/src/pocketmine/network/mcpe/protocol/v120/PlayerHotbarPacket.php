<?php

/**
 *
 *  ____       _                          _
 * |  _ \ _ __(_)___ _ __ ___   __ _ _ __(_)_ __   ___
 * | |_) | '__| / __| '_ ` _ \ / _` | '__| | '_ \ / _ \
 * |  __/| |  | \__ \ | | | | | (_| | |  | | | | |  __/
 * |_|   |_|  |_|___/_| |_| |_|\__,_|_|  |_|_| |_|\___|
 *
 * Prismarine is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Prismarine Team
 * @link   https://github.com/PrismarineMC/Prismarine
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\v120;

#include <rules/DataPacket.h>

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\types\ContainerIds;
use pocketmine\utils\Binary;

class PlayerHotbarPacket extends DataPacket{
	const NETWORK_ID = ProtocolInfo::PLAYER_HOTBAR_PACKET;

	/** @var int */
	public $selectedHotbarSlot;
	/** @var int */
	public $windowId = ContainerIds::INVENTORY;
	/** @var int[] */
	public $slots = [];
	/** @var bool */
	public $selectHotbarSlot = true;

	public function decodePayload(){
		$this->selectedHotbarSlot = $this->getUnsignedVarInt();
		$this->windowId = $this->getByte();
		$count = $this->getUnsignedVarInt();
		for($i = 0; $i < $count; ++$i){
			$this->slots[$i] = Binary::signInt($this->getUnsignedVarInt());
		}
		$this->selectHotbarSlot = $this->getBool();
	}

	public function encodePayload(){
		$this->putUnsignedVarInt($this->selectedHotbarSlot);
		$this->putByte($this->windowId);
		$this->putUnsignedVarInt(count($this->slots));
		foreach($this->slots as $slot){
			$this->putUnsignedVarInt($slot);
		}
		$this->putBool($this->selectHotbarSlot);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handlePlayerHotbar($this);
	}

	public function isMultiversionNative() : bool{
		return true;
	}
}