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

final class RainParticleItem extends ClickableItem {

    public function __construct(int $meta = 12, int $count = 1) {
        $this->setCustomName('§r§bRain Particle' . PHP_EOL . '§7Нажми, чтобы активировать');
        parent::__construct(self::DYE, $meta, $count);
    }

    public function handleClick(SufixPlayer $player) : void{
        if ($player->getRank() === 'GUEST') {
            $player->sendMessage(Loader::Prefix . ' §fПартикл §l§bRain§r доступен игрокам с привилегией §l§aＧｕｅｓｔ§6+§r.');
            $player->sendMessage(Loader::Prefix . ' Повысить свой §l§aранг§r можно в нашем магазине §8- §l§epay.sufixpvp.su');
            return;
        }
        if (Loader::getInstance()->getPlayerData($player, 'PARTICLE')['particle'] === 'RAIN') {
            $player->sendMessage(Loader::Prefix . ' У вас уже установлен партикл §l§bRain§r.');
            return;
        }
        Loader::getInstance()->setPlayerData($player, 'PARTICLE', 'RAIN');
        $player->sendMessage(Loader::Prefix . ' Партикл §l§bRain§r успешно установлен.');
        $player->sendMessage(Loader::Prefix . ' Хочешь §l§cбольше§r партиклов? Тогда §l§aповысь§r свой ранг сайте - §l§epay.sufixpvp.su');
    }
}