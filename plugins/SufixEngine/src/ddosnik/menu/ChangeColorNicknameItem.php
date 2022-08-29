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

use ddosnik\player\SufixPlayer;
use const PHP_EOL;

final class ChangeColorNicknameItem extends ClickableItem {

	public function __construct(int $meta = 15, int $count = 1) {
		$this->setCustomName('§r§6Изменить цвет никнейма' . PHP_EOL . '§7Нажмите, чтобы открыть цвета.');
		parent::__construct(self::DYE, $meta, $count);
	}

    public function handleClick(SufixPlayer $player) : void{
        ($inventory = $player->getInventory())->clearAll();
        $inventory->setItem(0, ClickableItemFactory::YELLOW_COLOR());
        $inventory->setItem(2, ClickableItemFactory::BLUE_COLOR());
        $inventory->setItem(4, ClickableItemFactory::RED_COLOR());
        $inventory->setItem(6, ClickableItemFactory::GREEN_COLOR());
        $inventory->setItem(8, ClickableItemFactory::BACK_MENU());
    }
}