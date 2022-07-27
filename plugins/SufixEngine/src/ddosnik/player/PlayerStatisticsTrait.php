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

use pocketmine\utils\TextFormat as Format;
use ddosnik\Loader;

trait PlayerStatisticsTrait {

	public function addWin() : void{
		$wins = Loader::getInstance()->getPlayerData($this, 'WINS')['wins'];
		Loader::getInstance()->setPlayerData($this, 'WINS', $wins + 1);
	}

	public function getWins() : int{
		return Loader::getInstance()->getPlayerData($this, 'WINS')['wins'];
	}

	public function getLvl() : int{
		return Loader::getInstance()->getPlayerData($this, 'LEVEL')['lvl'];
	}

	public function setLvl(int $lvl) : void{
		Loader::getInstance()->setPlayerData($this, 'LEVEL', $lvl);
	}

	public function getFactor() : int{
		return Loader::getInstance()->getPlayerData($this, 'FACTOR')['factor'];
	}

    public function getFactorToString() : string{
        $factors = ['Unknown', 'Отсутсвует', 'X2', 'X3'];
        return $factors[$this->getFactor($this)];
    }

	public function getExperience() : int{
		return Loader::getInstance()->getPlayerData($this, 'EXPIRIENCE')['exp'];
	}

	public function addExperience(int $count) : void{
		$exp = Loader::getInstance()->getPlayerData($this, 'EXPIRIENCE')['exp'];
        Loader::getInstance()->setPlayerData($this, 'EXPIRIENCE', $exp + $count);
    }

    public function addKill() : void{
        $kills = Loader::getInstance()->getPlayerData($this, 'KILLS')['kills'];
        Loader::getInstance()->setPlayerData($this, 'KILLS', $kills + 1);
    }

    public function getKills() : int{
    	return Loader::getInstance()->getPlayerData($this, 'KILLS')['kills'];
    }

    public function getPingString() : string{
    	$ping = $this->getPing();
        return match (true) {
            $ping < 100 => Format::GREEN . $ping . Format::RESET,
            $ping >= 100 && $ping < 250 => Format::YELLOW . $ping . Format::RESET,
            $ping >= 250 => Format::RED . $ping . Format::RESET,
        };
    }
}