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

use ddosnik\Loader;
use ddosnik\player\SufixPlayer;
use const PHP_EOL;

final class ChangeTimeItem extends ClickableItem {

	public function __construct(int $meta = 4, int $count = 1) {
		$this->setCustomName('§r§9Время' . PHP_EOL . '§7Нажмите, чтобы изменить свое время.');
		parent::__construct(self::CLOCK, $meta, $count);
	}

    public function handleClick(SufixPlayer $player) : void{
        $player->getInventory()->clearAll();
        $player->setItem(2, ClickableItemFactory::get('item_time_morning'));
        $player->setItem(4, ClickableItemFactory::get('item_time_day'));
        $player->setItem(6, ClickableItemFactory::get('item_time_evening'));
        $player->setItem(8, ClickableItemFactory::get('item_back_menu'));
    }
}