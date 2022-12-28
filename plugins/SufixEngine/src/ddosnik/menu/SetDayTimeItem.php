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

final class SetDayTimeItem extends ClickableItem {

    public function __construct(int $meta = 0, int $count = 1) {
        $this->setCustomName('§r§eДень§c' . PHP_EOL . '§fВремя: §l12:00§r' . PHP_EOL . '§7Нажми, чтобы изменить время');
        parent::__construct(self::CLOCK, $meta, $count);
    }

    public function handleClick(SufixPlayer $player) : void{
        $player->setCustomTime(1000);
        $player->updateTime();
        $player->sendPopup(Loader::Prefix . '§eУстановленное время: §eДень §7(Время: §l§f12:00§r§7)');
    }
}