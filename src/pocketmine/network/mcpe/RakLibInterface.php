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

declare(strict_types=1);

namespace pocketmine\network\mcpe;

use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\network\AdvancedSourceInterface;
use pocketmine\network\mcpe\multiversion\Multiversion;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\PacketPool120;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\encryption\DecryptionException;
use pocketmine\network\Network;
use pocketmine\Player;
use pocketmine\Server;
use raklib\protocol\EncapsulatedPacket;
use raklib\protocol\PacketReliability;
use raklib\RakLib;
use raklib\server\RakLibServer;
use raklib\server\ServerHandler;
use raklib\server\ServerInstance;
use raklib\utils\InternetAddress;
use Exception;
use function spl_object_hash;

class RakLibInterface implements ServerInstance, AdvancedSourceInterface{

	private const MCPE_RAKNET_PACKET_ID = "\xfe";
	private const PONG_DATA_UPDATE_RATE = 1.0;

	/** @var Server */
	private Server $server;

	/** @var Network */
	private Network $network;

	/** @var RakLibServer */
	private RakLibServer $rakLib;

	/** @var Player[] */
	private array $players = [];

	/** @var string[] */
	private array $identifiers;

	/** @var int[] */
	private array $identifiersACK = [];

	/** @var ServerHandler */
	private ServerHandler $interface;

	/** @var PongData */
	private PongData $pongData;

	/** @var float */
	private float $lastPongDataUpdate = 0.0;

	public function __construct(Server $server){

		$this->server = $server;
		$this->identifiers = [];

	    $this->rakLib = new RakLibServer(
			$this->server->getLogger(),
			$this->server->getLoader(),
			$this->server->getAddress()
		);
		$this->interface = new ServerHandler($this->rakLib, $this);
		$this->setPongData(new PongData());
		$this->server->getLogger()->debug("Waiting for RakLib to start...");
		$this->rakLib->startAndWait(PTHREADS_INHERIT_CONSTANTS); //HACK: MainLogger needs constants for exception logging
		$this->server->getLogger()->debug("RakLib booted successfully");
	}

	public function setNetwork(Network $network){
		$this->network = $network;
	}
	
	public function process() : bool{
		$work = false;
		if($this->interface->handlePacket()){
			$work = true;
			while($this->interface->handlePacket()){
			}
		}

		if(microtime(true) - $this->lastPongDataUpdate > self::PONG_DATA_UPDATE_RATE){
			$this->updatePongData();
		}

		if(!$this->rakLib->isRunning() and !$this->rakLib->isShutdown()){
			$this->network->unregisterInterface($this);

			$e = $this->rakLib->getCrashInfo();
			if($e !== null){
				throw $e;
			}
			throw new \Exception("RakLib Thread crashed without crash information");
		}

		return $work;
	}

	public function closeSession($identifier, $reason){
		if(isset($this->players[$identifier])){
			$player = $this->players[$identifier];
			unset($this->identifiers[spl_object_hash($player)]);
			unset($this->players[$identifier]);
			unset($this->identifiersACK[$identifier]);
			$player->close($player->getLeaveMessage(), $reason);
		}
	}

	public function close(Player $player, string $reason = "unknown reason"){
		if(isset($this->identifiers[$h = spl_object_hash($player)])){
			unset($this->players[$this->identifiers[$h]]);
			unset($this->identifiersACK[$this->identifiers[$h]]);
			$this->interface->closeSession($this->identifiers[$h], $reason);
			unset($this->identifiers[$h]);
		}
	}

	public function shutdown(){
		$this->interface->shutdown();
	}

	public function emergencyShutdown(){
		$this->interface->emergencyShutdown();
	}

	public function openSession($identifier, $address, $port, $clientID){
		$ev = new PlayerCreationEvent($this, Player::class, Player::class, null, $address, $port);
		$this->server->getPluginManager()->callEvent($ev);
		$class = $ev->getPlayerClass();

		$player = new $class($this, $ev->getClientId(), $ev->getAddress(), $ev->getPort());
		$this->players[$identifier] = $player;
		$this->identifiersACK[$identifier] = 0;
		$this->identifiers[spl_object_hash($player)] = $identifier;
		$this->server->addPlayer($identifier, $player);
	}

	public function handleEncapsulated($identifier, EncapsulatedPacket $packet, $flags) {
		if(isset($this->players[$identifier])){
			try{
				if(!empty($packet->buffer)){
					if($packet->buffer[0] !== self::MCPE_RAKNET_PACKET_ID){
						throw new \UnexpectedValueException("Unexpected non-FE packet");
					}
					$cipher = ($player = &$this->players[$identifier])->getCipher();
					$buffer = substr($packet->buffer, 1);
					try {
						if($cipher !== null) {
							$buffer = $cipher->decrypt($buffer);
						}
					} catch (DecryptionException $e) {}
					$pk = $this->getPacket(self::MCPE_RAKNET_PACKET_ID . $buffer, $player->getProtocol());
					$player->handleDataPacket($pk);
				}
			}catch(\Throwable $e){
				$logger = $this->server->getLogger();
				$logger->debug("Packet " . (isset($pk) ? get_class($pk) : "unknown") . " 0x" . bin2hex($packet->buffer));
				$logger->logException($e);
				if(isset($this->players[$identifier])){
					$this->interface->blockAddress($this->players[$identifier]->getAddress(), 5);
				}
			}
		}
	}

	public function blockAddress(string $address, int $timeout = 300){
		$this->interface->blockAddress($address, $timeout);
	}

	public function handleRaw($address, $port, $payload){
		$this->server->handlePacket($address, $port, $payload);
	}

	public function sendRawPacket(string $address, int $port, string $payload){
		$this->interface->sendRaw($address, $port, $payload);
	}

	public function notifyACK($identifier, $identifierACK){

	}

	public function updatePongData() : void{
		$info = $this->server->getQueryInformation();

		$this->pongData->setOnline($info->getPlayerCount());
		$this->pongData->setSlots($info->getMaxPlayerCount());
		$this->pongData->setMotd($info->getServerName());
		$this->pongData->setSubMotd($info->getSubMotd());
		$this->pongData->setGameMode($this->server->getGamemode()->getEnglishName());

		$this->interface->sendOption("name", $this->pongData->toServerName());

		$this->lastPongDataUpdate = microtime(true);
	}

	public function setName(string $name){
		$info = $this->server->getQueryInformation();

		$this->interface->sendOption("name", implode(";",
			[
				"MCPE",
				rtrim(addcslashes($name, ";"), '\\'),
				ProtocolInfo::CURRENT_PROTOCOL,
				ProtocolInfo::VERSION,
				$info->getPlayerCount(),
				$info->getMaxPlayerCount(),
				$this->rakLib->getServerId(),
                $this->server->getName() . ' ' . $this->server->getPocketmineVersion(),
				$this->server->getGamemode()->getEnglishName()
			]) . ";"
		);
	}

	public function setPortCheck($name){
		$this->interface->sendOption("portChecking", (bool) $name);
	}

	public function handleOption($name, $value){
		if($name === "bandwidth"){
			$v = unserialize($value);
			$this->network->addStatistics($v["up"], $v["down"]);
		}
	}
	public function handlePing($identifier, $ping){
		if(isset($this->players[$identifier])){
			$player = $this->players[$identifier];
			$player->ping = (int)$ping;
		}
	}

	public function putPacket(Player $player, DataPacket $packet, bool $needACK = false, bool $immediate = true){
		if(isset($this->identifiers[$h = spl_object_hash($player)])){
			$identifier = $this->identifiers[$h];
			if(!($packet instanceof BatchPacket) and $player->getProtocol() === ProtocolInfo::MULTIVERSION_PROTOCOL and $packet->protocol !== ProtocolInfo::MULTIVERSION_PROTOCOL and count($packets = Multiversion::convertTo120($packet, $player)) > 0){
				$this->server->batchPackets([$player], $packets, true, $immediate);
				return null;
			}
			if(!$packet->isEncoded){
				$packet->encode();
			}

			if($packet instanceof BatchPacket){
				return $this->putBuffer($player, $packet->buffer, $needACK, $immediate);
			}else{
				$this->server->batchPackets([$player], [$packet], true, $immediate);
				return null;
			}
		}

		return null;
	}

	public function putBuffer(Player $player, string $buffer, bool $needACK = false, bool $immediate = true) : ?int{
		if(isset($this->identifiers[$h = spl_object_hash($player)])){
			$sessionId = $this->identifiers[$h];

			$cipher = $player->getCipher();
			$rawBuffer = substr($buffer, 1);
			$buffer = self::MCPE_RAKNET_PACKET_ID . ($cipher !== null ? $cipher->encrypt($rawBuffer) : $rawBuffer);

			$pk = new EncapsulatedPacket();
			$pk->buffer = $buffer;
			$pk->reliability = $immediate ? PacketReliability::RELIABLE : PacketReliability::RELIABLE_ORDERED;
			$pk->orderChannel = 0;

			if($needACK === true){
				$pk->identifierACK = $this->identifiersACK[$sessionId]++;
			}

			$this->interface->sendEncapsulated($sessionId, $pk, ($needACK === true ? RakLib::FLAG_NEED_ACK : 0) | ($immediate === true ? RakLib::PRIORITY_IMMEDIATE : RakLib::PRIORITY_NORMAL));
			return $pk->identifierACK;
		}

		return null;
	}

	private function getPacket($buffer, int $protocol = ProtocolInfo::CURRENT_PROTOCOL) {
		$pid = ord($buffer[0]);
		if($protocol < ProtocolInfo::MULTIVERSION_PROTOCOL) {
			if(($data = PacketPool::getPacketById($pid)) === null) {
				return null;
			}
			$data->setBuffer($buffer, 1);
		}else{
			if(($data = PacketPool120::getPacketById($pid)) === null) {
				return null;
			}
			$data->setBuffer($buffer, 1);
		}

		return $data;
	}

	private function setPongData(PongData $pongData) : void{
		if (empty($pongData->getEdition())) {
			$pongData->setEdition('MCPE');
		}

		if (empty($pongData->getMinecraftVersion())) {
			$pongData->setMinecraftVersion(ProtocolInfo::MINECRAFT_VERSION);
		}

		if (empty($pongData->getProtocolVersion())) {
			$pongData->setProtocolVersion(ProtocolInfo::CURRENT_PROTOCOL);
		}

		if (empty($pongData->getServerId())) {
			$pongData->setServerId($this->raklib->getServerId());
		}
		$this->pongData = $pongData;
	}
}