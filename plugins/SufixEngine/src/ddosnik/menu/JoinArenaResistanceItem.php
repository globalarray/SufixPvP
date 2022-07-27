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

final class JoinArenaResistanceItem extends ClickableItem {

	public function __construct(int $meta = 0, int $count = 1) {
		$this->setCustomName('§r§3FFA RESISTANCE' . PHP_EOL . '§7Нажмите, чтобы войти на арену.');
		parent::__construct(self::POTION, $meta, $count);
	}

	public function handleClick(SufixPlayer $player) : void{
		$player->getInventory()->clearAll();
		$player->teleport(Server::getInstance()->getLevelByName('aCOMBO')->getSafeSpawn());
		$player->setMaxHealth(20);
        $player->setHealth(20);
        $player->setFood(20);
		foreach (Server::getInstance()->getLevelByName('aCOMBO')->getPlayers() as $playerOnArena) {
			$playerOnArena->sendMessage(Loader::Prefix . ' §fИгрок §l' . $player->getName() . '§f§r присоединился к арене §l§3FFA-RESISTANCE§r');
			$playerOnArena->sendMessage(Loader::Prefix . ' §fИгроков на арене: §l§c' . sizeof($this->getServer()->getLevelByName('aCOMBO')->getPlayers()) . '§r');
		}
		$player->sendMessage(Loader::Prefix . ' Чтобы выйти с §l§aарены§r используйте команду §e/quit§r');
	}
}