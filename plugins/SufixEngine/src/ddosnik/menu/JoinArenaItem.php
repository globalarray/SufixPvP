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

use ddosnik\player\SufixPlayer;

final class JoinArenaItem extends ClickableItem {

	public function __construct(int $meta = 0, int $count = 1) {
		$this->setCustomName('§r§eВойти на арену'. PHP_EOL .'§7Нажмите, чтобы открыть.');
		parent::__construct(self::COMPASS, $meta, $count);
	}

	public function handleClick(SufixPlayer $player) : void{
		$player->getInventory()->clearAll();
        $player->getInventory()->setItem(2, ClickableItemFactory::get('ffa_gapple'));
        $player->getInventory()->setItem(4, ClickableItemFactory::get('ffa_fist'));
        $player->getInventory()->setItem(6, ClickableItemFactory::get('ffa_resistance'));
        $player->getInventory()->setItem(7, ClickableItemFactory::get('item_quit'));
    }
}