<?php

namespace pocketmine\network\mcpe\protocol;

use pocketmine\network\mcpe\NetworkSession;

class CommandStepPacket extends DataPacket{
	
	const NETWORK_ID = ProtocolInfo::COMMAND_STEP_PACKET;

	public $command;
	public $overload;
	public $uvarint1;
	public $currentStep;
	public $done;
	public $clientId;
	public $inputJson;
	public $outputJson;
	
	public function decodePayload(){
		$this->command = $this->getString();
		$this->overload = $this->getString();
		$this->uvarint1 = $this->getUnsignedVarInt();
		$this->currentStep = $this->getUnsignedVarInt();
		$this->done = (bool) $this->getBool();
		$this->clientId = $this->getUnsignedVarLong();
		$this->inputJson = json_decode($this->getString());
		$this->outputJson = $this->getString();
		$this->get(true);
	}
	/**
	 *
	 */
	public function encodePayload(){
	}

	/*public function decodePayload(){
		$this->command = $this->getString();
		$this->overload = $this->getString();
		$this->uvarint1 = $this->getUnsignedVarInt();
		$this->currentStep = $this->getUnsignedVarInt();
		$this->done = $this->getBool();
		$this->clientId = $this->getUnsignedVarLong();
		$this->inputJson = json_decode($this->getString());
		$this->outputJson = json_decode($this->getString());

		$this->getRemaining(); //TODO: read command origin data
	}

	public function encodePayload(){
		$this->putString($this->command);
		$this->putString($this->overload);
		$this->putUnsignedVarInt($this->uvarint1);
		$this->putUnsignedVarInt($this->currentStep);
		$this->putBool($this->done);
		$this->putUnsignedVarLong($this->clientId);
		$this->putString(json_encode($this->inputJson));
		$this->putString(json_encode($this->outputJson));

		$this->put("\x00\x00\x00"); //TODO: command origin data
	}*/

	public function handle(NetworkSession $session) : bool{
		return $session->handleCommandStep($this);
	}

}