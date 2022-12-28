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
use pocketmine\Server;
use ddosnik\player\SufixPlayer;
use const PHP_EOL;

final class CustomizationItem extends ClickableItem {

	public function __construct(int $meta = 9, int $count = 1) {
		$this->setCustomName('§r§dКастомизация' . PHP_EOL . '§7Нажмите, чтобы изменить свою кастомизацию.');
		parent::__construct(self::DYE, $meta, $count);
	}

    public function handleClick(SufixPlayer $player) : void{
        ($inventory = $player->getInventory())->clearAll();
        $player->sendPopup(TextFormat::GRAY . 'Ты открыл(а) меню кастомизации!');
        $inventory->setItem(1, ClickableItemFactory::CHANGE_COLOR());
        $inventory->setItem(4, ClickableItemFactory::PARTICLES());
        $inventory->setItem(7, ClickableItemFactory::CHANGE_TIME());
        $inventory->setItem(8, ClickableItemFactory::BACK_MENU());
    }
}