<?php

/*
 *
 *  _____            _               _____           
 * / ____|          (_)             |  __ \          
 *| |  __  ___ _ __  _ ___ _   _ ___| |__) | __ ___  
 *| | |_ |/ _ \ '_ \| / __| | | / __|  ___/ '__/ _ \ 
 *| |__| |  __/ | | | \__ \ |_| \__ \ |   | | | (_) |
 * \_____|\___|_| |_|_|___/\__, |___/_|   |_|  \___/ 
 *                         __/ |                    
 *                        |___/                     
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author GenisysPro
 * @link https://github.com/GenisysPro/GenisysPro
 *
 *
*/

namespace pocketmine\block;

use pocketmine\event\block\BlockGrowEvent;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;

class ChorusFlower extends Transparent{

	protected $id = self::CHORUS_FLOWER;

	/**
	 * ChorusFlower constructor.
	 *
	 * @param int $meta
	 */
	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	/**
	 * @return float
	 */
	public function getHardness(){
		return 0.4;
	}

	/**
	 * @return int
	 */
	public function getToolType(){
		return Tool::TYPE_AXE;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return "Chorus Flower";
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $this->getSide(0);
		if($down->getId() === 240 or $down->getId() === 121 or $down->getId() === 200){
			$block0 = $this->getSide(2);
			$block1 = $this->getSide(3);
			$block2 = $this->getSide(4);
			$block3 = $this->getSide(5);
			if(!$block0->isSolid() and !$block1->isSolid() and !$block2->isSolid() and !$block3->isSolid()){
				$this->getLevel()->setBlock($this, $this, true);
				return true;
			}
		}

		return false;
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_RANDOM){
			if(mt_rand(1, 2) == 2){
			    return;
			}

			$count = 0;
			for($i = 0; $i <= 15; $i++){
				if($this->getLevel()->getBlock(new Vector3($this->getX(), $this->getY() - $i, $this->getZ()))->getId() == 240){
				    $count++;
				}
			}
			if($count >= 10){
				return;
			}

			if($this->getSide(Vector3::SIDE_DOWN)->getId() == 121 or $this->getSide(Vector3::SIDE_DOWN)->getId() == 240){
				Server::getInstance()->getPluginManager()->callEvent($ev = new BlockGrowEvent($this->getLevel()->getBlock($this), new ChorusFlower()));
				if($ev->isCancelled()){
				    return;
				}

				$rand = mt_rand(1, 3);
				for($i = 0; $i <= $rand; $i++){
					if($this->getLevel()->getBlock(new Vector3($this->getX(), $this->getY() + $i, $this->getZ()))->getId() !== 0 and $this->getLevel()->getBlock(new Vector3($this->getX(), $this->getY() + $i, $this->getZ()))->getId() !== 200 and $this->getLevel()->getBlock(new Vector3($this->getX(), $this->getY() + $i, $this->getZ()))->getId() !== 240){
						return;
				    }

					$this->getLevel()->setBlock(new Vector3($this->getX(), $this->getY() + $i, $this->getZ()), Block::get(240, 0));
				}

				$this->getLevel()->setBlock(new Vector3($this->getX(), $this->getY() + $rand, $this->getZ()), Block::get(200, 0));
			}else{
				$this->getLevel()->useBreakOn($this);
			}

			return Level::BLOCK_UPDATE_RANDOM;
		}

		return false;
	}

	/**
	 * @param Item $item
	 *
	 * @return array
	 */
	public function getDrops(Item $item) : array{
		$drops = [];
		if($this->meta >= 0x07){
			$drops[] = [Item::CHORUS_FRUIT, 0, 1];
		}
		return $drops;
	}

}