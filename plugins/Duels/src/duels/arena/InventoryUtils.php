<?php

declare(strict_types=1);

namespace duels\arena;

use pocketmine\inventory\PlayerInventory;
use pocketmine\item\Item;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\{Potion, SplashPotion};

class InventoryUtils
{
    final public static function addItemsByGamemode(PlayerInventory $inventory, string $gamemode): void
    {
        $inventory->clearAll();
        switch ($gamemode) {
            case 'bow':
                $inventory->setHelmet(Item::get(Item::LEATHER_HELMET));
                $inventory->setChestplate(Item::get(Item::LEATHER_CHESTPLATE));
                $inventory->setLeggings(Item::get(Item::LEATHER_LEGGINGS));
                $inventory->setBoots(Item::get(Item::LEATHER_BOOTS));
                $inventory->setItem(0, Item::get(Item::BOW)->addEnchantment(Enchantment::getEnchantment(Enchantment::INFINITY)->setLevel(1)));
                $inventory->setItem(12, Item::get(Item::ARROW));
                break;
            case 'nodebuff':
                for ($i = 0; $i < 40; $i++) {
                    if ($i != 0 || $i != 1 || $i != 7 || $i != 8) {
                        $inventory->setItem($i, new SplashPotion(22));
                    }
                }
                $inventory->setHelmet(Item::get(Item::DIAMOND_HELMET, 0, 1)->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1)));
                $inventory->setChestplate(Item::get(Item::DIAMOND_CHESTPLATE, 0, 1)->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1)));
                $inventory->setLeggings(Item::get(Item::DIAMOND_LEGGINGS, 0, 1)->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1)));
                $inventory->setBoots(Item::get(Item::DIAMOND_BOOTS, 0, 1)->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1)));
                $inventory->setItem(0, Item::get(Item::DIAMOND_SWORD, 0, 1)->addEnchantment(Enchantment::getEnchantment(13)->setLevel(1)));
                $inventory->setItem(1, Item::get(Item::ENDER_PEARL, 0, 16));
                $inventory->setItem(8, Item::get(Item::STEAK, 0, 64));
                break;
            case 'combo':
                $inventory->setHelmet(Item::get(Item::DIAMOND_HELMET, 0, 1)->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1)));
                $inventory->setChestplate(Item::get(Item::DIAMOND_CHESTPLATE, 0, 1)->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1)));
                $inventory->setLeggings(Item::get(Item::DIAMOND_LEGGINGS, 0, 1)->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1)));
                $inventory->setBoots(Item::get(Item::DIAMOND_BOOTS, 0, 1)->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1)));
                $inventory->setItem(0, Item::get(Item::DIAMOND_SWORD, 0, 1));
                $inventory->setItem(1, new Potion(31));
                $inventory->setItem(2, new Potion(31));
                $inventory->setItem(3, Item::get(Item::ENCHANTED_GOLDEN_APPLE));
                break;
            case 'fist':
                $inventory->setItem(0, Item::get(Item::STEAK, 0, 64));
                break;
            case 'mlgrush':
                $inventory->setItem(0, Item::get(Item::STICK)->addEnchantment(Enchantment::getEnchantment(Enchantment::KNOCKBACK)->setLevel(1)));
                $inventory->setItem(1, Item::get(Item::DIAMOND_PICKAXE));
                $inventory->setItem(2, Item::get(Item::SANDSTONE, 0, 64));
                break;
            case 'builduhc':
                $inventory->setHelmet(Item::get(Item::DIAMOND_HELMET));
                $inventory->setChestplate(Item::get(Item::DIAMOND_CHESTPLATE));
                $inventory->setLeggings(Item::get(Item::DIAMOND_LEGGINGS));
                $inventory->setBoots(Item::get(Item::DIAMOND_BOOTS));
                $inventory->setItem(0, Item::get(Item::DIAMOND_SWORD));
                break;
        }
    }
}
