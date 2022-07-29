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

final class QuitToLobbyItem extends ClickableItem {

	public function __construct(int $meta = 0, int $count = 1) {
		$this->setCustomName('§r§cВыход' . PHP_EOL . '§7Нажмите, чтобы выйти в лобби');
		parent::__construct(self::BED, $meta, $count);
	}

    public function handleClick(SufixPlayer $player) : void{
        $player->getInventory()->clearAll();
        $player->removeAllEffects();
        $player->setGamemode(2);
        $player->setMaxHealth(20);
        $player->setHealth(20);
        $player->teleport(Server::getInstance()->getDefaultLevel()->getSpawnLocation());
        for ($i = 0; $i < sizeof($items = $this->getMainMenuItems()); $i++) {
            $player->getInventory()->setItem($items[$i][0], $items[$i][1]);
        }
    }
}