<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\utils\TextFormat;
use pocketmine\Player;

class RakLibCommand extends VanillaCommand {

	/**
	 * RakLibCommand constructor.
	 *
	 * @param $name
	 */
	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.raklib.description",
			"%pocketmine.command.raklib.usage"
		);
		$this->setPermission("pocketmine.command.raklib");
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $currentAlias
	 * @param array         $args
	 *
	 * @return bool
	 */
	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) === 0){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return false;
		}

		$argCommand = array_shift($args);
		$ip = array_shift($args);
		if ($argCommand == "block") {
			if(preg_match("/^([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])$/", $ip)){
				$sender->getServer()->getNetwork()->blockAddress($ip, 500);
			}else{
				if(($player = $sender->getServer()->getPlayer($ip)) instanceof Player){
				    $sender->getServer()->getNetwork()->blockAddress($player->getAddress(), 500);
			    }else{
				    $sender->sendMessage(new TranslationContainer("pocketmine.command.raklib.invalid"));

				    return false;
			    }
			}
		}elseif ($argCommand == "unblock") {
			if(preg_match("/^([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])$/", $ip)){
				$sender->getServer()->getNetwork()->unblockAddress($ip);
			}else{
				$sender->sendMessage(new TranslationContainer("pocketmine.command.raklib.invalid"));

				return false;
			}
		}elseif ($argCommand == "packetlimit") {
			$limit = $ip;
            if (is_int($limit)) {
				$sender->getServer()->getNetwork()->setPacketLimit($limit);
				$sender->sendMessage(new TranslationContainer("pocketmine.command.raklib.edited", [$limit]));
			}else{
				$sender->sendMessage("Значение не является целым (int)");

				return false;
			}
		}elseif ($argCommand == "isblocked") {
			if(preg_match("/^([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])$/", $ip)){
				if ($sender->getServer()->getNetwork()->isBlockedAddress($ip)) {
					$sender->sendMessage(new TranslationContainer("pocketmine.command.raklib.blocked", [$ip]));
				}else{
					$sender->sendMessage(new TranslationContainer("pocketmine.command.raklib.notBlocked", [$ip]));
				}
			}else{
				$sender->sendMessage(new TranslationContainer("pocketmine.command.raklib.invalid"));

				return false;
		    }
		}else{
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return false;
		}

		return true;
	}
}
