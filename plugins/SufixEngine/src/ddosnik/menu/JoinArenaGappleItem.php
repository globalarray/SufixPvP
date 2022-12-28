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
use pocketmine\item\Armor;
use pocketmine\Server;
use ddosnik\player\SufixPlayer;
use ddosnik\utils\ArmorUtils;
use const PHP_EOL;
use function sizeof;

final class JoinArenaGappleItem extends ClickableItem {

	public function __construct(int $meta = 0, int $count = 1) {
		$this->setCustomName('§r§eFFA GAPPLE' . PHP_EOL . '§7Нажмите, чтобы войти на арену.');
		parent::__construct(self::GOLDEN_APPLE, $meta, $count);
	}

	public function handleClick(SufixPlayer $player) : void{
		$player->getInventory()->clearAll();
		$player->teleport(($level = Server::getInstance()->getLevelByName('6GAPPLE'))->getSafeSpawn());
		$player->updateTime();
		$player->setMaxHealth(20);
        $player->setHealth(20);
        $player->setFood(20);
		foreach ($this->getItemsOnArena('GAPPLE') as $item) {
			if ($item instanceof Armor) {
				ArmorUtils::equip($player, $item);
				continue;
			}
			$player->getInventory()->addItem($item);
		}
		foreach ($level->getPlayers() as $playerOnArena) {
			$playerOnArena->sendMessage(Loader::Prefix . ' §fИгрок §l' . $player->getName() . '§f§r присоединился к арене §l§eFFA-GAPPLE§r.');
			$playerOnArena->sendMessage(Loader::Prefix . ' §fИгроков на арене: §l§c' . sizeof($level->getPlayers()) . '§r');
		}
		$player->sendMessage(Loader::Prefix . ' Чтобы выйти с §l§aарены§r используйте команду §e/quit§r');
	}
}
