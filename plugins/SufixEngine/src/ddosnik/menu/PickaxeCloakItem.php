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

final class PickaxeCloakItem extends ClickableItem {

	public function __construct(int $meta = 0, int $count = 1) {
		$this->setCustomName('§r§9Pickaxe Cloak' . PHP_EOL . '§7Нажми, чтобы установить');
		parent::__construct(self::GOLDEN_PICKAXE, $meta, $count);
	}

    public function handleClick(SufixPlayer $player) : void{
        $player->sendMessage(Loader::Prefix . 'Вы успешно установили себе плащ §l§9Pickaxe Cloak§r');
        $player->setCloak('Minecon_MineconSteveCape2012');
    }
}