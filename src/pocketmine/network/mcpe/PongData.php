<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe;

final class PongData {

    /** @var string */
	protected string $edition = '';
	/** @var string */
	protected string $motd = '';
	/** @var int */
	protected int $protocolVersion = -1;
	/** @var string */
	protected string $minecraftVersion = '';
	/** @var int */
	protected int $online = -1;
	/** @var int */
	protected int $slots = -1;
	/** @var int */
	protected int $serverId = -1;
	/** @var string */
	protected string $subMotd = '';
	/** @var string */
	protected string $gameMode = '';
	/** @var string[] */
	protected array $extraData = [];

	public function fromServerName(string $name) : self{
		$info = explode(';', $name);

		$pong = new self;
		$pong->edition = $info[0] ?? '';
		$pong->motd = $info[1] ?? '';
		$pong->protocolVersion = intval(($info[2] ?? -1));
		$pong->minecraftVersion = $info[3] ?? '';
		$pong->online = intval($info[4] ?? -1);
		$pong->slots = intval($info[5] ?? -1);
		$pong->serverId = intval($info[6] ?? -1);
		$pong->subMotd = $info[7] ?? '';
		$pong->gameMode = $info[8] ?? '';
		if (count($info) > 9) {
			$pong->extraData = array_slice($info, 9);
		}
		return $pong;
	}

	public function toServerName() : string{
		$pongData = array_merge([
			$this->edition,
			$this->motd,
			(string) $this->protocolVersion,
			$this->minecraftVersion,
			(string) $this->online,
			(string) $this->slots,
			(string) $this->serverId,
			$this->subMotd,
			$this->gameMode
		], $this->extraData);
		return implode(';', $pongData) . ';';
	}

	public function getEdition() : string{
		return $this->edition;
	}

	public function setEdition(string $edition) : void{
		$this->edition = $edition;
	}

	public function getMotd() : string{
		return $this->motd;
	}

	public function setMotd(string $motd) : void{
		$this->motd = $motd;
	}

	public function getProtocolVersion() : int{
		return $this->protocolVersion;
	}

	public function setProtocolVersion(int $protocolVersion) : void{
		$this->protocolVersion = $protocolVersion;
	}

	public function getMinecraftVersion() : string{
		return $this->minecraftVersion;
	}

	public function setMinecraftVersion(string $minecraftVersion) : void{
		$this->minecraftVersion = $minecraftVersion;
	}

	public function getOnline() : int{
		return $this->online;
	}

	public function setOnline(int $playersCount) : void{
		$this->online = $playersCount;
	}

	public function getSlots() : int{
		return $this->slots;
	}

	public function setSlots(int $slots) : void{
		$this->slots = $slots;
	}

	public function getServerId() : int{
		return $this->serverId;
	}

	public function setServerId(int $serverId) : void{
		$this->serverId = $serverId;
	}

	public function getSubMotd() : string{
		return $this->subMotd;
	}

	public function setSubMotd(string $subMotd) : void{
		$this->subMotd = $subMotd;
	}

	public function getGameMode() : string{
		return $this->gameMode;
	}

	public function setGameMode(string $gameMode) : void{
		$this->gameMode = $gameMode;
	}

	public function getExtraData() : array{
		return $this->extraData;
	}

	public function setExtraData(array $extraData) : void{
		$this->extraData = $extraData;
	}
}