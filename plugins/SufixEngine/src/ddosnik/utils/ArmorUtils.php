<?php

declare(strict_types=1);

namespace ddosnik\utils;

use ddosnik\player\SufixPlayer;
use pocketmine\item\{
	Item,
	Armor
};

final class ArmorUtils {
	private const HELMETS = [298, 302, 306, 310, 314];
	private const CHESTPLATES = [299, 303, 307, 311, 315];
	private const LEGGINGS = [300, 304, 308, 312, 316];
	private const BOOTS = [301, 305, 309, 313, 317];

	public static function isHelmet(Item $item) : bool{
		return in_array($item->getId(), self::HELMETS, true);
	}

	public static function isChestplate(Item $item) : bool{
		return in_array($item->getId(), self::CHESTPLATES, true);
	}

	public static function isLeggings(Item $item) : bool{
		return in_array($item->getId(), self::LEGGINGS, true);
	}

	public static function isBoots(Item $item) : bool{
		return in_array($item->getId(), self::BOOTS, true);
	}

	public static function equip(SufixPlayer $player, Armor $armor) : void{
		if (self::isHelmet($armor)) {
			$player->getInventory()->setHelmet($armor);
			return;
		}

		if (self::isChestplate($armor)) {
			$player->getInventory()->setChestplate($armor);
			return;
		}

		if (self::isLeggings($armor)) {
			$player->getInventory()->setLeggings($armor);
			return;
		}

		if (self::isBoots($armor)) {
			$player->getInventory()->setBoots($armor);
			return;
		}
	}
}
