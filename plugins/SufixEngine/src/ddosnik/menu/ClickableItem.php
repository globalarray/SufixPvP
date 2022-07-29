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
				Item::get(self::GOLDEN_APPLE)],
			'FIST' => [Item::get(self::STEAK, 0, 64)],
		};
	}

	public function getCloaksList() : array{
		return [
			[1, ClickableItemFactory::get('dragon_cloak')],
			[2, ClickableItemFactory::get('golem_cloak')],
			[3, ClickableItemFactory::get('piston_cloak')],
			[4, ClickableItemFactory::get('pickaxe_cloak')],
			[5, ClickableItemFactory::get('creeper_cloak')]
		];
	}

	public function getMainMenuItems() : array{
		return [
			[2, ClickableItemFactory::get('item_cloaks')],
			[4, ClickableItemFactory::get('join_arena')],
			[6, ClickableItemFactory::get('item_customization')]
		];
	}
}