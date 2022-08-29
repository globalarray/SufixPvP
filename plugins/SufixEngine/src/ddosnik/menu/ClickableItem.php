<?php

/**
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

namespace ddosnik\menu;

use pocketmine\item\{Item, ItemIds};
use ddosnik\player\SufixPlayer;

abstract class ClickableItem extends Item implements ItemIds {

	abstract public function handleClick(SufixPlayer $player) : void;

	public function getItemsOnArena(string $arena) : ?array{
		return match ($arena) {
			'GAPPLE' => [
				Item::get(self::DIAMOND_HELMET),
				Item::get(self::DIAMOND_CHESTPLATE),
				Item::get(self::DIAMOND_LEGGINGS),
				Item::get(self::DIAMOND_BOOTS),
				Item::get(self::DIAMOND_SWORD),
				Item::get(self::GOLDEN_APPLE, 0, 8)],
			'FIST' => [Item::get(self::STEAK, 0, 64)],
		};
	}

	public function getCloaksList() : array{
		return [
			[1, ClickableItemFactory::DRAGON_CLOAK()],
			[2, ClickableItemFactory::GOLEM_CLOAK()],
			[3, ClickableItemFactory::PISTON_CLOAK()],
			[4, ClickableItemFactory::PICKAXE_CLOAK()],
			[5, ClickableItemFactory::CREEPER_CLOAK()]
		];
	}

	public function getParticlesList() : array{
		return [
			[0, ClickableItemFactory::HEART_PARTICLE()],
			[2, ClickableItemFactory::HAPPY_PARTICLE()],
			[4, ClickableItemFactory::RAIN_PARTICLE()],
			[6, ClickableItemFactory::FLAME_PARTICLE()]
		];
	}

	public function getMainMenuItems() : array{
		return [
			[2, ClickableItemFactory::CLOAKS()],
			[4, ClickableItemFactory::JOIN_ARENA()],
			[6, ClickableItemFactory::CUSTOMIZATION()]
		];
	}
}