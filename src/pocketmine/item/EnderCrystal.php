<?php

/*
 *
 *  _____   _____   __   _   _   _____  __    __  _____
 * /  ___| | ____| |  \ | | | | /  ___/ \ \  / / /  ___/
 * | |     | |__   |   \| | | | | |___   \ \/ /  | |___
 * | |  _  |  __|  | |\   | | | \___  \   \  /   \___  \
 * | |_| | | |___  | | \  | | |  ___| |   / /     ___| |
 * \_____/ |_____| |_|  \_| |_| /_____/  /_/     /_____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace pocketmine\item;

use pocketmine\level\Level;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pocketmine\{Player, Server};
use pocketmine\nbt\tag\{CompoundTag, StringTag, ShortTag, ListTag, LongTag, ByteTag, IntTag, DoubleTag, FloatTag, Enum};
use pocketmine\entity\Entity;
use pocketmine\entity\EnderCrystal as Crystal;

class EnderCrystal extends Item{

	/**
	 * EyeOfEnder constructor.
	 *
	 * @param int $meta
	 * @param int $count
	 */
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::END_CRYSTAL, 0, $count, 'Ender Crystal');
	}

	/**
	 * @return bool
	 */
	public function canBeActivated() : bool{
		return true;
	}

	/**
	 * @param Level  $level
	 * @param Player $player
	 * @param Block  $block
	 * @param Block  $target
	 * @param        $face
	 * @param        $fx
	 * @param        $fy
	 * @param        $fz
	 *
	 * @return bool
	 */
	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
        if($target->getId() !== 49){
        	return;
        }

        if($level->getBlock($target->asVector3()->add(0, 1, 0))->getId() !== 0){
            return;
        }

        $player->getInventory()->setItemInHand(Item::get(426, 0, $player->getItemInHand()->getCount() - 1));
        
        $nbt = new CompoundTag('', [
            new ListTag('Pos', [new DoubleTag('', $target->getX() + 0.5), new DoubleTag('', $target->getY() + 1), new DoubleTag('', $target->getZ() + 0.5)]), 
            new ListTag('Motion', [new DoubleTag('', 0.0), new DoubleTag('', 0.0), new DoubleTag('', 0.0)]), 
            new ListTag('Rotation', [new FloatTag('', $player->getYaw()), new FloatTag('', $player->getPitch())])
        ]);

        $npc = new Crystal($player->level, $nbt);
        $npc->spawnToAll();
	}
}