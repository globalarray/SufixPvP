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

final class DragonCloakItem extends ClickableItem {

	public function __construct(int $meta = 5, int $count = 1) {
		$this->setCustomName('§r§5Dragon Cloak' . PHP_EOL . '§7Нажми, чтобы установить');
		parent::__construct(self::MOB_HEAD, $meta, $count);
	}

    public function handleClick(SufixPlayer $player) : void{
        $player->sendMessage(Loader::Prefix . 'Вы успешно установили себе плащ §l§5Dragon Cloak§r');
        $player->setCloak('Minecon_MineconSteveCape2016');
    }
}