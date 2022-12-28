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
use ddosnik\Loader;
use pocketmine\Server;
use ddosnik\player\SufixPlayer;
use const PHP_EOL;
use function sizeof;

final class CloaksListItem extends ClickableItem {

	public function __construct(int $meta = 0, int $count = 1) {
		$this->setCustomName('§r§aПлащи' . PHP_EOL . '§7Нажмите, чтобы выбрать себе плащ.');
		parent::__construct(self::EMERALD, $meta, $count);
	}

    public function handleClick(SufixPlayer $player) : void{
        if ($player->getRank() === 'GUEST') {
            $player->sendMessage(Loader::Prefix . ' §cДанный раздел доступен игрокам с привилегией §l§aＧｕｅｓｔ§6+§r' . PHP_EOL . Loader::Prefix . "Повысить свой §aранг§r можно в нашем магазине §8- §epay.cristalix.fun");
            return;
        }
        $player->getInventory()->clearAll();
        $player->sendPopup(TextFormat::GRAY . 'Ты открыл(а) меню выбора плаща!');
        for ($i = 0; $i < sizeof($cloaks = $this->getCloaksList()); $i++) {
            $player->getInventory()->setItem($cloaks[$i][0], $cloaks[$i][1]);
        }
        $player->getInventory()->setItem(7, ClickableItemFactory::BACK_MENU());
    }
}