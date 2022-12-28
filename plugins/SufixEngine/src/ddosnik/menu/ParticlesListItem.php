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

use pocketmine\utils\TextFormat;
use pocketmine\Server;
use ddosnik\player\SufixPlayer;
use const PHP_EOL;
use function sizeof;

final class ParticlesListItem extends ClickableItem {

	public function __construct(int $meta = 12, int $count = 1) {
		$this->setCustomName('§r§bПартиклы' . PHP_EOL . '§7Нажмите, чтобы выбрать себе партикл.');
		parent::__construct(self::DYE, $meta, $count);
	}

    public function handleClick(SufixPlayer $player) : void{
        $player->getInventory()->clearAll();
        $player->sendPopup(TextFormat::GRAY . 'Ты открыл(а) меню выбора партикла!');
        for ($i = 0; $i < sizeof($particles = $this->getParticlesList()); $i++) {
            $player->getInventory()->setItem($particles[$i][0], $particles[$i][1]);
        }
        $player->getInventory()->setItem(7, ClickableItemFactory::BACK_MENU());
    }
}