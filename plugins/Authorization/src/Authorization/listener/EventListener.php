<?php

declare(strict_types=1);


namespace Authorization\listener;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\event\player\PlayerCommandPreprocessEvent;

use pocketmine\event\block\BlockPlaceEvent;

use pocketmine\event\block\BlockBreakEvent;

use pocketmine\event\player\PlayerItemConsumeEvent;

use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\event\player\PlayerDropItemEvent;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\event\player\PlayerQuitEvent;

use Authorization\Loader;

class EventListener extends PluginBase implements Listener
{

	private $loader;
	
	public function __construct(Loader $loader) {
		$this->loader = $loader;
	}

	public function onPlayerJoin(PlayerJoinEvent $event) : void {
		$player = $event->getPlayer();

		$nickname = $player->getLowerCaseName();

		$ip = $player->getAddress();
		$cid = $player->getClientId();

		$player_in_database = $this->loader->thePlayerIsInDatabase($player);
		if($player_in_database === true) {
			if($this->loader->canAuthByIp($player) === true) { //может ли авторизироваться без ввода пароля по IP
				$this->loader->players[$nickname] = "game";

				$player->setImMobile(false);

				$this->loader->setCid($player, $cid); //сменим CID, при авторизации по IP, на всякий случай!

				$player->sendMessage(Loader::Prefix."§fВы были авторизированы автоматически.");
			} elseif($this->loader->canAuthByCid($player) === true) { //может ли авторизироваться без ввода пароля по CID
				$this->loader->players[$nickname] = "game";

				$this->loader->setIp($player, $ip); //сменим IP, при авторизации по IP, на всякий случай!

				$player->setImMobile(false);

				$player->sendMessage(Loader::Prefix."§fВы были авторизированы автоматически!");
			} else {
				$this->loader->players[$nickname] = "log";

				$player->setImMobile(true);

				$player->sendMessage(Loader::Prefix."§fАвторизируйтесь, введя пароль в чат!");
			}

		} else {
			$this->loader->players[$nickname] = "reg";

			$player->setImMobile(true);

			$player->sendMessage(Loader::Prefix."§fЗарегистрируйтесь, введя придуманный пароль в чат!");
		}

	}

	public function onChat(PlayerCommandPreprocessEvent $event) : void {
		$player = $event->getPlayer();
		$message = $event->getMessage();

		$ip = $player->getAddress();
		$cid = $player->getClientId();

		$nickname = $player->getLowerCaseName();
		if($this->loader->players[$nickname] === "reg") {
			if($message[0] === "/") {
				$player->sendMessage(Loader::Prefix."§fИспользование команд во время регистрации §cзапрещено§f!");
			} elseif(stripos($message, " ") != false) {
				$player->sendMessage(Loader::Prefix."§fПароль§c не может §fсодержать пробелы!");
			} elseif(strlen($message) < 6) {
				$player->sendMessage(Loader::Prefix."§fПароль §cне может §fбыть меньше 6-ти символов!");
			} else {
				$this->loader->players[$nickname] = "game";

				$this->loader->addPlayerInDataBase($player, $message);//Занести в базу данных: ник, ип, сид, пароль

				$player->setImMobile(false);

				$player->sendMessage(Loader::Prefix."§fВы §aуспешно §fзарегистрировались, ваш пароль: §7{$message}§f! Запомните его, или сделайте скриншот.");
			}
			$event->setCancelled(true);
		} elseif($this->loader->players[$nickname] === "log") {
			if($message[0] === "/") {
				$player->sendMessage(Loader::Prefix."§fНельзя использовать команды во время авторизации!");
			} else {
				if($this->loader->getPassword($player) === $message) {
					$this->loader->setIp($player, $ip);
					$this->loader->setCid($player, $cid);

					$this->loader->players[$nickname] = "game";

					$player->setImMobile(false);

					$player->sendMessage(Loader::Prefix."§fВы §aуспешно авторизировались!");
				} else {
					$player->sendMessage(Loader::Prefix."§8» §fНеверный пароль!");
				}

			}
			$event->setCancelled(true);
		} elseif($this->loader->players[$nickname] === "game") {
			$event->setCancelled(false);
		}

	}

	public function onBlockPlace(BlockPlaceEvent $event) {
		$player = $event->getPlayer();

		$nickname = $player->getLowerCaseName();
		if($this->loader->players[$nickname] != "game") {
			$event->setCancelled(true);
		} else {
			$event->setCancelled(false);
		}

	}

	public function onBlockBreak(BlockBreakEvent $event) {
		$player = $event->getPlayer();
		
		$nickname = $player->getLowerCaseName();
		if($this->loader->players[$nickname] != "game") {
			$event->setCancelled(true);
		} else {
			$event->setCancelled(false);
		}

	}

	public function onPlayerItemConsume(PlayerItemConsumeEvent $event) {
		$player = $event->getPlayer();
		
		$nickname = $player->getLowerCaseName();
		if($this->loader->players[$nickname] != "game") {
			$event->setCancelled(true);
		} else {
			$event->setCancelled(false);
		}

	}

	public function onPlayerInteract(PlayerInteractEvent $event) {
		$player = $event->getPlayer();
		
		$nickname = $player->getLowerCaseName();
		if($this->loader->players[$nickname] != "game") {
			$event->setCancelled(true);
		} else {
			$event->setCancelled(false);
		}

	}

	public function onPlayerDropItem(PlayerDropItemEvent $event){ 
		$player = $event->getPlayer();
		
		$nickname = $player->getLowerCaseName();
		if($this->loader->players[$nickname] != "game") {
			$event->setCancelled(true);
		} else {
			$event->setCancelled(false);
		}

	}

	public function onPlayerDamage(EntityDamageEvent $event) {
		if($event instanceof EntityDamageByEntityEvent) {
			$entity = $event->getEntity();
			$damager = $event->getDamager();
			if ($entity instanceof Player && $damager instanceof Player) {
				$nickname_of_damager = $damager->getLowerCaseName();
				$nickname_of_entity = $entity->getLowerCaseName();
				
				if($this->loader->players[$nickname_of_damager] != "game") {
					$event->setCancelled(true);
				} else {
					$event->setCancelled(false);
				}

				if($this->loader->players[$nickname_of_entity] != "game") {
					$event->setCancelled(true);
				} else {
					$event->setCancelled(false);
				}

			}

		}

	}

	public function onQuit(PlayerQuitEvent $event) : void {
		$event->setQuitMessage(null);
	}

}