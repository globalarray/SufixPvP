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

final class HeartParticleItem extends ClickableItem {

	public function __construct(int $meta = 1, int $count = 1) {
		$this->setCustomName('§r§cHeart Particle' . PHP_EOL . '§7Нажми, чтобы активировать');
		parent::__construct(self::DYE, $meta, $count);
	}

    public function handleClick(SufixPlayer $player) : void{
        $engine = Loader::getInstance();
        if (!$engine->getPlayerData($player, 'HEARTPARTICLE')['heart']) {
            if (($money = $engine->getMoney($player)) < 3000) {
                $player->sendMessage(Loader::Prefix . ' Недостаточно §l§a' . (3000 - $money) . '§r  для покупки партикла §l§cHeart§r');
                $player->sendMessage(Loader::Prefix . ' Приобрести §l§bвалюту§r можно на нашем сайте - §l§epay.sufixpvp.su');
                return;
            } else {
                $player->sendMessage(Loader::Prefix . ' Партикл §l§cHeart§r успешно куплен за §l§b3000 §r');
                $player->sendMessage(Loader::Prefix . ' Партикл §l§cHeart§r успешно установлен.');
                $engine->remMoney($player, 3000);
                $engine->setPlayerData($player, 'PARTICLE', 'HEART');
                $engine->setPlayerData($player, 'HEARTPARTICLE', true);
                return;
            }
        }
        if ($engine->getPlayerData($player, 'PARTICLE')['particle'] === 'HEART') {
            $player->sendMessage(Loader::Prefix . ' У вас уже установлен партикл §l§cHeart§r.');
            return;
        }
        $engine->setPlayerData($player, 'PARTICLE', 'HEART');
        $player->sendMessage(Loader::Prefix . ' Партикл §l§cHeart§r успешно установлен.');
        $player->sendMessage(Loader::Prefix . ' Хочешь §l§cбольше§r партиклов? Тогда §l§aповысь§r свой ранг сайте - §l§epay.sufixpvp.su');
    }
}