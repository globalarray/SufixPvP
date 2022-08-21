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

use pocketmine\item\Item;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\DataPacket;

class InventoryContentPacket extends DataPacket{
	const NETWORK_ID = ProtocolInfo::INVENTORY_CONTENT_PACKET;

	/** @var int */
	public $windowId;
	/** @var Item[] */
	public $items = [];

	public function decodePayload(){
		$this->windowId = $this->getUnsignedVarInt();
		$count = $this->getUnsignedVarInt();
		for($i = 0; $i < $count; ++$i){
			$this->items[] = $this->getSlot();
		}
	}

	public function encodePayload(){
		$this->putUnsignedVarInt($this->windowId);
		$this->putUnsignedVarInt(count($this->items));
		foreach($this->items as $item){
			$this->putSlot($item);
		}
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleInventoryContent($this);
	}

	public function isMultiversionNative() : bool{
		return true;
	}
}