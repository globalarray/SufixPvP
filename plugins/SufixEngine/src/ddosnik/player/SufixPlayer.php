<?php

/*
 *
 * ╔═══╗───╔═╗───╔═══╗
 * ║╔═╗║───║╔╝───║╔══╝
 * ║╚══╦╗╔╦╝╚╦╦╗╔╣╚══╦═╗╔══╦╦═╗╔══╗
 * ╚══╗║║║╠╗╔╬╬╬╬╣╔══╣╔╗╣╔╗╠╣╔╗╣║═╣
 * ║╚═╝║╚╝║║║║╠╬╬╣╚══╣║║║╚╝║║║║║║═╣
 * ╚═══╩══╝╚╝╚╩╝╚╩═══╩╝╚╩═╗╠╩╝╚╩══╝
 * ─────────────────────╔═╝║
 * ─────────────────────╚══╝
 *
 * @author David Ratnikov
 * @link https://vk.com/showyouass
 *
 */

declare(strict_types=1);

namespace ddosnik\player;

use pocketmine\Player;
use pocketmine\network\mcpe\protocol\{
    SetTimePacket,
    RemoveEntityPacket
};
use ddosnik\Loader;

class SufixPlayer extends Player implements PlayerConstants {
	use PlayerRankTrait;
	use PlayerStatisticsTrait;
	use PlayerCustomizationTrait;

    public function getFFAMode(): string{
        return Loader::FFA_WORLDS[$this->getLevel()->getFolderName()];
    }

    public function inFFA() : bool{
        return isset(Loader::FFA_WORLDS[$this->getLevel()->getFolderName()]);
    }

    public function sendTime(int $time): void{
        $pk = new SetTimePacket();
        $pk->time = $time;
        $this->dataPacket($pk);
    }

    public function removeBossBar() : void{
        $pk = new RemoveEntityPacket();
        $pk->entityUniqueId = 999888777;
        $this->dataPacket($pk);
    }
}