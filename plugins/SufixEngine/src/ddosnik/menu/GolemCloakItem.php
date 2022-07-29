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

final class GolemCloakItem extends ClickableItem {

	public function __construct(int $meta = 0, int $count = 1) {
		$this->setCustomName('§r§3Golem Cloak' . PHP_EOL . '§7Нажми, чтобы установить');
		parent::__construct(self::IRON_INGOT, $meta, $count);
	}

    public function handleClick(SufixPlayer $player) : void{
        $player->sendMessage(Loader::Prefix . 'Вы успешно установили себе плащ §l§3Golem Cloak§r');
        $player->setCloak('Minecon_MineconSteveCape2015');
    }
}