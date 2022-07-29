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

final class PistonCloakItem extends ClickableItem {

	public function __construct(int $meta = 0, int $count = 1) {
		$this->setCustomName('§r§2Piston Cloak' . PHP_EOL . '§7Нажми, чтобы установить');
		parent::__construct(self::SLIME, $meta, $count);
	}

    public function handleClick(SufixPlayer $player) : void{
        $player->sendMessage(Loader::Prefix . 'Вы успешно установили себе плащ §l§2Piston Cloak§r');
        $player->setCloak('Minecon_MineconSteveCape2013');
    }
}