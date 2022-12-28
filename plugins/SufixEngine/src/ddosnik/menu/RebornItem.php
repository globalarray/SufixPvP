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

use pocketmine\Server;
use pocketmine\GameMode;
use pocketmine\item\{
	Item,
	Armor
};
use ddosnik\utils\ArmorUtils;
use ddosnik\player\SufixPlayer;

final class RebornItem extends ClickableItem {

	public function __construct(int $meta = 0, int $count = 1) {
		$this->setCustomName('§r§aВозродиться §fна арене'. PHP_EOL .'§7Нажмите, чтобы вернуться к жизни.');
		parent::__construct(self::EMERALD, $meta, $count);
	}

	public function handleClick(SufixPlayer $player) : void{
		$player->getInventory()->clearAll();
		$player->setGamemode(GameMode::ADVENTURE());
		$player->setMaxHealth(20);
		$player->setHealth(20);
		$player->setFood(20);
		$player->updateTime();
		switch ($player->getLevel()->getFolderName()) {
			case '6GAPPLE':
				$player->teleport(Server::getInstance()->getLevelByName('6GAPPLE')->getSafeSpawn());
					foreach ($this->getItemsOnArena('GAPPLE') as $item) {
					if ($item instanceof Armor) {
						ArmorUtils::equip($player, $item);
						continue;
					}
					$player->getInventory()->addItem($item);
				}
			break;
			case '4FIST':
				$player->teleport(Server::getInstance()->getLevelByName('4FIST')->getSafeSpawn());
				foreach ($this->getItemsOnArena('FIST') as $item) {
					$player->getInventory()->addItem($item);
				}
			break;
			case 'aCOMBO':
				$player->teleport(Server::getInstance()->getLevelByName('aCOMBO')->getSafeSpawn());
			break;
		}
		$player->addTitle('', '§aТы возродился', 6, 10, 6);
	}
}
