<?php

/**
 * ┏━━━┓╋╋╋┏━┓╋╋╋┏━━┓
 * ┃┏━┓┃╋╋╋┃┏┛╋╋╋┃┏┓┃
 * ┃┗━━┳┓┏┳┛┗┳┳┓┏┫┗┛┗┳━━┳━━┳━━┓
 * ┗━━┓┃┃┃┣┓┏╋╋╋╋┫┏━┓┃┏┓┃━━┫┃━┫
 * ┃┗━┛┃┗┛┃┃┃┃┣╋╋┫┗━┛┃┏┓┣━━┃┃━┫
 * ┗━━━┻━━┛┗┛┗┻┛┗┻━━━┻┛┗┻━━┻━━┛
 *
 * @author RootiTeam
 * @link https://github.com/RootiTeam
 */

declare(strict_types=1);

namespace pocketmine;

use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\utils\{
	TextFormat,
	UUID
};

class PlayerInfo {

	public readonly string $username;
	public readonly string $iusername;
	public readonly string $languageCode;
	public readonly int $deviceOS;
	public readonly string $deviceModel;
	public readonly int $randomClientId;
	public readonly int $protocol;
	public readonly int $clientInput;
	public readonly string $strUUID;

	public function __construct(LoginPacket $packet) {
		$this->username = TextFormat::clean($packet->username);
		$this->iusername = mb_strtolower($this->username);
		$this->languageCode = $packet->languageCode;
		$this->randomClientId = $packet->clientId;
		$this->protocol = $packet->protocol;
		$this->deviceOS = $packet->deviceOS;
		$this->deviceModel = $packet->deviceModel;
		$this->clientInput = $packet->clientInput;
		$this->strUUID = $packet->clientUUID;
	}
}