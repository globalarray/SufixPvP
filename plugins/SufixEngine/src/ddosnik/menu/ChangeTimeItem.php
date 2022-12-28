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

use pocketmine\utils\TextFormat;
use ddosnik\player\SufixPlayer;
use const PHP_EOL;

final class ChangeTimeItem extends ClickableItem {

	public function __construct(int $meta = 4, int $count = 1) {
		$this->setCustomName('§r§9Время' . PHP_EOL . '§7Нажмите, чтобы изменить свое время.');
		parent::__construct(self::CLOCK, $meta, $count);
	}

    public function handleClick(SufixPlayer $player) : void{
        ($inventory = $player->getInventory())->clearAll();
        $player->sendPopup(TextFormat::GRAY . 'Ты открыл(а) меню изменения времени!');
        $inventory->setItem(2, ClickableItemFactory::TIME_MORNING());
        $inventory->setItem(4, ClickableItemFactory::TIME_DAY());
        $inventory->setItem(6, ClickableItemFactory::TIME_EVENING());
        $inventory->setItem(8, ClickableItemFactory::BACK_MENU());
    }
}