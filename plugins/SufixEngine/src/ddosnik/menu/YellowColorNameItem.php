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

use ddosnik\Loader;
use ddosnik\player\SufixPlayer;
use const PHP_EOL;

final class YellowColorNameItem extends ClickableItem {

	public function __construct(int $meta = 11, int $count = 1) {
		$this->setCustomName('§r§eЖелтый цвет' . PHP_EOL . '§7Нажмите, чтобы установить.');
		parent::__construct(self::DYE, $meta, $count);
	}

    public function handleClick(SufixPlayer $player) : void{
        $engine = Loader::getInstance();
        if (!$player->isHaveCustomColor('YELLOW')) {
            if ($engine->getMoney($player) < 2500) {
                $player->sendMessage(Loader::Prefix . "У вас не куплен §r§eжелтый цвет §fникнейма. У вас §cнедостаточно§f монет для его покупки. Стоимость цвета: §e2500 монет.");
                return;
            }
            if ($engine->getMoney($player) >= 2500) {
                $player->sendMessage(Loader::Prefix . "§r§eЖелтый цвет §fникнейма успешно приобретен за §e2500 монет§f.");
                $engine->remMoney($player, 2500);
                $engine->setPlayerData($player, 'YELLOWTAG', true);
                $engine->setPlayerData($player, 'CURRENTCOLOR', '§e');
                $player->sendMessage(Loader::Prefix . "§r§eЖелтый цвет §fникнейма успешно установлен, перезайдите для активации.");
            }
        } else {
            $engine->setPlayerData($player, 'CURRENTCOLOR', '§e');
            $player->sendMessage(Loader::Prefix . "§r§eЖелтый цвет §fникнейма успешно установлен, перезайдите для активации.");
        }
    }
}