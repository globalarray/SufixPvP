<?php

declare(strict_types=1);


namespace Authorization;

use pocketmine\plugin\PluginBase;

# Путь до слушателя событий

use Authorization\listener\EventListener;

# Путь до команды смены пароля

use Authorization\commands\ChangePassword;

class Loader extends PluginBase
{
    /** @var Prefix */
    const Prefix = "§l§d» §r";

	public $players = [];

	public function onEnable() : void {
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

		$this->getServer()->getCommandMap()->register("chp", new ChangePassword($this));

		$this->databaseCreation();
	}

	public function databaseCreation() : void {
		$this->db = new \SQLite3($this->getDataFolder() . "authorizationdata.db");
        $this->db->query("CREATE TABLE IF NOT EXISTS `authorizationdata`(`nickname` TEXT NOT NULL, `ip` TEXT NOT NULL, `cid` TEXT NOT NULL, `password` TEXT NOT NULL);");
	}

	public function thePlayerIsInDatabase($player) : bool {
		$nickname = $player->getLowerCaseName();

		if(($this->db->query("SELECT * FROM `authorizationdata` WHERE `nickname` = '$nickname'")->fetchArray(SQLITE3_ASSOC))) {
			return true; //Игрок есть в базе данных
		} else {
			return false; //Игрока нет в базе данных
		}

	}

	public function addPlayerInDataBase($player, $password) : void {
		$nickname = $player->getLowerCaseName();

		$ip = $player->getAddress();
		$cid = $player->getClientId();

		$insert = "INSERT INTO `authorizationdata`(`nickname`,`ip`, `cid`, `password`) VALUES('{$nickname}', '{$ip}', '{$cid}', '{$password}')";

		$this->db->query($insert);
	}

	public function canAuthByIp($player) {
		$last_ip = $this->getIp($player);
		$ip = $player->getAddress();

# Манипуляции с Ip
		$last_ip_string = str_replace('.','',$last_ip);
		$ip_string = str_replace('.','',$ip);
# Манипуляции с Ip

		if($last_ip_string == $ip_string) {
			return true;
		} else {
			return false;
		}

	}

	public function canAuthByCid($player) {
		$last_cid = $this->getCid($player);
		$cid = $player->getClientId();

		if($last_cid == $cid) {
			return true;
		} else {
			return false;
		}

	}

	public function getIp($player) : string {
		$nickname = $player->getLowerCaseName();

		$result = $this->db->query("SELECT `ip` FROM `authorizationdata` WHERE `nickname` = '$nickname'")->fetchArray(SQLITE3_ASSOC);
		return $result["ip"];
    }

    public function setIp($player, $ip) : void {
		$nickname = $player->getLowerCaseName();

		$this->db->query("UPDATE `authorizationdata` SET `ip` = '$ip' WHERE `nickname` = '$nickname'");
	}

	public function getCid($player) : string {
		$nickname = $player->getLowerCaseName();

		$result = $this->db->query("SELECT `cid` FROM `authorizationdata` WHERE `nickname` = '$nickname'")->fetchArray(SQLITE3_ASSOC);
		return $result["cid"];
    }

    public function setCid($player, $cid) : void {
		$nickname = $player->getLowerCaseName();

		$this->db->query("UPDATE `authorizationdata` SET `cid` = '$cid' WHERE `nickname` = '$nickname'");
	}

	public function getPassword($player) : string {
		$nickname = $player->getLowerCaseName();

		$result = $this->db->query("SELECT `password` FROM `authorizationdata` WHERE `nickname` = '$nickname'")->fetchArray(SQLITE3_ASSOC);
		return $result["password"];
    }

    public function setPassword($player, $password) : void {
		$nickname = $player->getLowerCaseName();

		$this->db->query("UPDATE `authorizationdata` SET `password` = '$password' WHERE `nickname` = '$nickname'");
	}

}