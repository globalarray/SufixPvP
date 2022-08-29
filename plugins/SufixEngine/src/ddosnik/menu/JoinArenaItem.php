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
use const PHP_EOL;

final class JoinArenaItem extends ClickableItem {

	public function __construct(int $meta = 0, int $count = 1) {
		$this->setCustomName('§r§eВойти на арену'. PHP_EOL .'§7Нажмите, чтобы открыть.');
		parent::__construct(self::COMPASS, $meta, $count);
	}

	public function handleClick(SufixPlayer $player) : void{
		$player->getInventory()->clearAll();
        $player->getInventory()->setItem(2, ClickableItemFactory::FFA_GAPPLE());
        $player->getInventory()->setItem(4, ClickableItemFactory::FFA_FIST());
        $player->getInventory()->setItem(6, ClickableItemFactory::FFA_RESISTANCE());
        $player->getInventory()->setItem(7, ClickableItemFactory::BACK_MENU());
    }
}