<?php

declare(strict_types=1);

namespace pocketmine\network\query;

use pocketmine\network\Network;

use pocketmine\{
	Thread,
	Server
};
use pocketmine\utils\Binary;

class QueryThread extends Thread {

	/** @var QueryHandler */
	private $queryHandler;
	private string $source;
	private int $port;
	private string $buffer;
	private $lastToken, $token, $longData, $shortData, $timeout;

	const HANDSHAKE = 9;
	const STATISTICS = 0;

	public function __construct(string $source, int $port, string $payload) {
		$this->network = Server::getInstance()->getNetwork();
		$this->source = $source;
		$this->port = $port;
		$this->buffer = $payload;
		$this->regenerateToken();
		$this->lastToken = $this->token;
		$this->regenerateInfo();
		$this->handlePacket($source, $port, $payload);
		$this->start();
	}

	public function regenerateInfo(){
		$ev = Server::getInstance()->getQueryInformation();
		$this->longData = $ev->getLongQuery();
		$this->shortData = $ev->getShortQuery();
		$this->timeout = microtime(true) + $ev->getTimeout();
	}

	public function regenerateToken(){
		$this->lastToken = $this->token;
		$this->token = random_bytes(16);
	}

	public static function getTokenString($token, $salt){
		return Binary::readInt(substr(hash("sha512", $salt . ":" . $token, true), 7, 4));
	}

	public function handle($source, $port, $packet) : void{
		$offset = 2;
		$packetType = ord($packet[$offset++]);
		$sessionID = Binary::readInt(substr($packet, $offset, 4));
		$offset += 4;
		$payload = substr($packet, $offset);

		switch($packetType){
			case self::HANDSHAKE: //Handshake
				$reply = chr(self::HANDSHAKE);
				$reply .= Binary::writeInt($sessionID);
				$reply .= self::getTokenString($this->token, $source) . "\x00";

				$this->getNetwork()->sendPacket($soruce, $port, $reply);
				break;
			case self::STATISTICS: //Stat
				$token = Binary::readInt(substr($payload, 0, 4));
				if($token !== self::getTokenString($this->token, $source) and $token !== self::getTokenString($this->lastToken, $source)){
					break;
				}
				$reply = chr(self::STATISTICS);
				$reply .= Binary::writeInt($sessionID);

				if($this->timeout < microtime(true)){
					$this->regenerateInfo();
				}

				if(strlen($payload) === 8){
					$reply .= $this->longData;
				}else{
					$reply .= $this->shortData;
				}
				$this->getNetwork()->sendPacket($source, $port, $reply);
				break;
		}
	}

	/**
	 * @return Network
	 */
	public function getNetwork() {
		return $this->network;
	}

	public function run() {
		try{
			if(strlen($payload) > 2 and substr($payload, 0, 2) === "\xfe\xfd"){
				$this->handle($this->source, $this->port, $this->buffer);
			}
		}catch(\Throwable $e){
			if(\pocketmine\DEBUG > 1){
				Server::getInstance()->logger->logException($e);
			}

			Server::getInstance()->getNetwork()->blockAddress($this->source, 600);
		}
	}
}