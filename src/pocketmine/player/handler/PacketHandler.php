<?php

namespace pocketmine\player\handler;

use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\Player;

abstract class PacketHandler
{

	protected Player $player;

	public function __construct(Player $player)
	{
		$this->player = $player;
	}

	/**
	 * @param DataPacket $packet
	 * @return bool true, если нужно прокинуть обработку дальше в Player::handleDataPacket
	 */
	public abstract function handle(DataPacket $packet): bool;

}