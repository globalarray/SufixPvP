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

final class HappyParticleItem extends ClickableItem {

	public function __construct(int $meta = 0, int $count = 1) {
		$this->setCustomName('§r§aHappy Particle' . PHP_EOL . '§7Нажми, чтобы активировать');
		parent::__construct(self::EMERALD, $meta, $count);
	}

    public function handleClick(SufixPlayer $player) : void{
        if ($player->getRank() === 'GUEST') {
            $player->sendMessage(Loader::Prefix . ' §fПартикл §l§aHappy§r доступен игрокам с привилегией §l§aＧｕｅｓｔ§6+§r.');
            $player->sendMessage(Loader::Prefix . ' Повысить свой §l§aранг§r можно в нашем магазине §8- §l§epay.sufixpvp.su');
            return;
        }
        if (Loader::getInstance()->getPlayerData($player, 'PARTICLE')['particle'] === 'HAPPY') {
            $player->sendMessage(Loader::Prefix . ' У вас уже установлен партикл §l§aHappy§r.');
            return;
        }
        Loader::getInstance()->setPlayerData($player, 'PARTICLE', 'HAPPY');
        $player->sendMessage(Loader::Prefix . ' Партикл §l§aHappy§r успешно установлен.');
        $player->sendMessage(Loader::Prefix . ' Хочешь §l§cбольше§r партиклов? Тогда §l§aповысь§r свой ранг сайте - §l§epay.sufixpvp.su');
    }
}