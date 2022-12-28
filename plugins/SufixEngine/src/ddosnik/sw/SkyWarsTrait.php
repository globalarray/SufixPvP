<?php

declare(strict_types=1);

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

namespace ddosnik\sw;

use pocketmine\item\{
	Item,
	ItemIds,
	Bow,
	Armor
};
use pocketmine\item\enchantment\Enchantment;

trait SkyWarsTrait {

	public function getSkyWarsItems() : array{
		$items = [
			'armor' => [ // броня
				ItemIds::LEATHER_CAP,
				ItemIds::LEATHER_TUNIC,
				ItemIds::LEATHER_PANTS,
				ItemIds::LEATHER_BOOTS,
				ItemIds::GOLD_HELMET,
				ItemIds::GOLD_CHESTPLATE,
				ItemIds::GOLD_LEGGINGS,
				ItemIds::GOLD_BOOTS,
				ItemIds::CHAIN_HELMET,
				ItemIds::CHAIN_CHESTPLATE,
				ItemIds::CHAIN_LEGGINGS,
				ItemIds::CHAIN_BOOTS,
				ItemIds::IRON_HELMET,
				ItemIds::IRON_CHESTPLATE,
				ItemIds::IRON_LEGGINGS,
				ItemIds::IRON_BOOTS,
				ItemIds::DIAMOND_HELMET,
				ItemIds::DIAMOND_CHESTPLATE,
				ItemIds::DIAMOND_LEGGINGS,
				ItemIds::DIAMOND_BOOTS,
			],
			'weapons' => [ // оружие
				ItemIds::WOODEN_SWORD,
				ItemIds::WOODEN_AXE,
				ItemIds::WOODEN_PICKAXE,
				ItemIds::GOLD_SWORD,
				ItemIds::GOLD_AXE,
				ItemIds::GOLD_PICKAXE,
				ItemIds::STONE_SWORD,
				ItemIds::STONE_AXE,
				ItemIds::STONE_PICKAXE,
				ItemIds::IRON_SWORD,
				ItemIds::IRON_AXE,
				ItemIds::IRON_PICKAXE,
				ItemIds::DIAMOND_SWORD,
				ItemIds::DIAMOND_AXE,
				ItemIds::DIAMOND_PICKAXE
			],
			'food' => [ // еда
				ItemIds::RAW_PORKCHOP,
				ItemIds::RAW_CHICKEN,
				ItemIds::MELON_SLICE,
				ItemIds::COOKIE,
				ItemIds::RAW_BEEF,
				ItemIds::CARROT,
				ItemIds::APPLE,
				ItemIds::GOLDEN_APPLE,
				ItemIds::BEETROOT_SOUP,
				ItemIds::BREAD,
				ItemIds::BAKED_POTATO,
				ItemIds::MUSHROOM_STEW,
				ItemIds::COOKED_CHICKEN,
				ItemIds::COOKED_PORKCHOP,
				ItemIds::STEAK,
				ItemIds::PUMPKIN_PIE
			],
			'air_weapons' => [ // оружие по воздуху
				ItemIds::BOW,
				ItemIds::ARROW,
				ItemIds::SNOWBALL,
				ItemIds::EGG
			],
			'blocks' => [ // блоки
				ItemIds::STONE,
				ItemIds::WOODEN_PLANKS,
				ItemIds::COBBLESTONE,
				ItemIds::DIRT
			]
		];

		$finally_items = [];

		foreach ($items as $type => $item) {
			$rand_item = $item[array_rand($item)];
			$final_item = match (true) {
				$type === 'armor' => Item::get($rand_item),
				$type === 'weapons' => Item::get($rand_item),
				$type === 'food' => Item::get($rand_item),
				$type === 'air_weapons' => Item::get($rand_item, 0, mt_rand(1, Item::get($rand_item)->getMaxStackSize())),
				$type === 'blocks' => Item::get($rand_item, 0, mt_rand(8, 56)),
				default => Item::get(0),
			};

			if ($final_item instanceof Armor && mt_rand(1, 4) % 2) {
				$final_item->addEnchantment(Enchantment::getEnchantment(self::ARMOR_ENCHANTMENTS[array_rand(self::ARMOR_ENCHANTMENTS)]));
			}

			if ($final_item instanceof Bow && mt_rand(1, 3) % 3) {
				$final_item->addEnchantment(Enchantment::getEnchantment(self::BOW_ENCHANTMENTS[array_rand(self::BOW_ENCHANTMENTS)]));
			}

			if (in_array($final_item->getId(), $items['weapons']) && mt_rand(1, 2) % 1) {
				$final_item->addEnchantment(Enchantment::getEnchantment(self::WEAPONS_ENCHANTMENTS[array_rand(self::WEAPONS_ENCHANTMENTS)]));
			}
			array_push($finally_items, $final_item);
		}

		for ($i = 0; $i < 5; $i++) {
			$randomType = $items[array_rand($items)];
			$randomItemId = $randomType[array_rand($randomType)];
			$finallyRandomItem = Item::get($randomItemId, 0, mt_rand(1, Item::get($randomItemId)->getMaxStackSize()));
			array_push($finally_items, $finallyRandomItem);
		}
		return $finally_items;
	}
}