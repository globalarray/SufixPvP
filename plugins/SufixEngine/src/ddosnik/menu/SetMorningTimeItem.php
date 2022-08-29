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

final class SetMorningTimeItem extends ClickableItem {

    public function __construct(int $meta = 0, int $count = 1) {
        $this->setCustomName('§r§bУтро§c' . PHP_EOL . '§fВремя: §l9:00§r' . PHP_EOL . '§7Нажми, чтобы изменить время');
        parent::__construct(self::CLOCK, $meta, $count);
    }

    public function handleClick(SufixPlayer $player) : void{
        $player->sendTime(0);
        $player->sendMessage(Loader::Prefix . '§eУстановленное время: §bУтро §7(Время: §l§f9:00§r§7)');
    }
}