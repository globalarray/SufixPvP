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

final class SetEveningTimeItem extends ClickableItem {

    public function __construct(int $meta = 0, int $count = 1) {
        $this->setCustomName('§r§9Вечер§c' . PHP_EOL . '§fВремя: §l19:00§r' . PHP_EOL . '§7Нажми, чтобы изменить время');
        parent::__construct(self::CLOCK, $meta, $count);
    }

    public function handleClick(SufixPlayer $player) : void{
        $player->sendTime(13000);
        $player->sendMessage(Loader::Prefix . '§eУстановленное время: §9Вечер §7(Время: §l§f19:00§r§7)');
    }
}