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
    use PlayerEconomyTrait;
	use PlayerStatisticsTrait;
	use PlayerCustomizationTrait;

    public int $clicksPerSecond = 0;
    public float $lastClicksUpdate = 0.0;

    private int $customTime = 1000;

    public function getFFAMode(): string{
        return Loader::FFA_WORLDS[$this->getLevel()->getFolderName()];
    }

    public function inFFA() : bool{
        return isset(Loader::FFA_WORLDS[$this->getLevel()->getFolderName()]);
    }

    public function getClicksPerSecond() : int{
        return $this->clicksPerSecond;
    }

    public function setCustomTime(int $time) : void{
        $this->customTime = $time;
    }

    public function getCustomTime() : int{
        return $this->customTime;
    }

    public function updateTime() : void{
        $this->sendTime($this->customTime);
    }

    public function addClickToQueue() : void{
        if ((microtime(true) - $this->lastClicksUpdate) > 1.2) {
            $this->clicksPerSecond = 0;
            $this->lastClicksUpdate = microtime(true);
        }
        $this->clicksPerSecond++;
    }

    public function getOsAsString() : string{
      return match (true) {
  			($this->getDeviceOS() !== 1 && $this->getDeviceOS() !== 2) => '§r§8Windows 10',
  			($this->getDeviceModel() === 'Linux') => '§r§8Bedrock Launcher',
        ($this->getDeviceModel() !== 'Linux' && $this->getDeviceOS() === 1) => '§r§8Android',
        ($this->getDeviceModel() !== 'Linux' && $this->getDeviceOS() === 2) => '§r§8iOS',
        default => '§r§8' . $this->getDeviceModel()
  		};
    }

    public function sendTime(int $time): void{
        $pk = new SetTimePacket();
        $pk->time = $time;
        $this->dataPacket($pk);
    }

    public function removeBossBar() : void{
        $pk = new RemoveEntityPacket();
        $pk->entityUniqueId = $this->getClientId();
        $this->dataPacket($pk);
    }
}
