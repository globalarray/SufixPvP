<?php

declare(strict_types=1);

namespace pocketmine\item;

abstract class ProjectileItem extends Item {

	public function getMaxStackSize() : int{
		return 16;
	}
}