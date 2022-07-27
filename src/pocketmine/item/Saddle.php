<?php

namespace pocketmine\item;

class Saddle extends Item {

	public function __construct()
	{
		parent::__construct(ItemIds::SADDLE, 0, 1, "Saddle");
	}

	public function getMaxStackSize(): int
	{
		return 1;
	}
}