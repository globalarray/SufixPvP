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

final class BackToMenuItem extends ClickableItem {

	public function __construct(int $meta = 0, int $count = 1) {
		$this->setCustomName('§r§cВернуться§c' . PHP_EOL . '§7Нажми, чтобы вернуться');
		parent::__construct(self::ARROW, $meta, $count);
	}

	public function handleClick(SufixPlayer $player) : void{
		$player->getInventory()->clearAll();
        for ($i = 0; $i < sizeof($items = $this->getMainMenuItems()); $i++) {
            $player->getInventory()->setItem($items[$i][0], $items[$i][1]);
        }
    }
}