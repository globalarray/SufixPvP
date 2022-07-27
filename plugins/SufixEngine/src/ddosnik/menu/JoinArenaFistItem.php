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
use pocketmine\Server;
use ddosnik\player\SufixPlayer;
use const PHP_EOL;

final class JoinArenaFistItem extends ClickableItem {

	public function __construct(int $meta = 0, int $count = 1) {
		$this->setCustomName('§r§cFFA FIST' . PHP_EOL . '§7Нажмите, чтобы войти на арену.');
		parent::__construct(self::STEAK, $meta, $count);
	}

	public function handleClick(SufixPlayer $player) : void{
		$player->getInventory()->clearAll();
		$player->teleport(Server::getInstance()->getLevelByName('4FIST')->getSafeSpawn());
		$player->setMaxHealth(20);
        $player->setHealth(20);
        $player->setFood(20);
		foreach ($this->getItemsOnArena('FIST') as $item) {
			$player->getInventory()->addItem($item);
		}
		foreach (Server::getInstance()->getLevelByName('4FIST')->getPlayers() as $playerOnArena) {
			$playerOnArena->sendMessage(Loader::Prefix . ' §fИгрок §l' . $player->getName() . '§f§r присоединился к арене §l§cFFA-FIST§r.');
			$playerOnArena->sendMessage(Loader::Prefix . ' §fИгроков на арене: §l§c' . sizeof($this->getServer()->getLevelByName('4FIST')->getPlayers()) . '§r');
		}
		$player->sendMessage(Loader::Prefix . ' Чтобы выйти с §l§aарены§r используйте команду §e/quit§r');
	}
}