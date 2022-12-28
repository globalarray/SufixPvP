<?php

/*
 *
 * ╔═══╗───╔═╗───╔═══╗
 * ║╔═╗║───║╔╝───║╔══╝
 * ║╚══╦╗╔╦╝╚╦╦╗╔╣╚══╦═╗╔══╦╦═╗╔══╗
 * ╚══╗║║║╠╗╔╬╬╬╬╣╔══╣╔╗╣╔╗╠╣╔╗╣║═╣
 * ║╚═╝║╚╝║║║║╠╬╬╣╚══╣║║║╚╝║║║║║║═╣
 * ╚═══╩══╝╚╝╚╩╝╚╩═══╩╝╚╩═╗╠╩╝╚╩══╝
 * ─────────────────────╔═╝║
 * ─────────────────────╚══╝
 *
 * @author David Ratnikov
 * @link https://vk.com/showyouass
 *
 */

declare(strict_types=1);

namespace ddosnik\player;

use ddosnik\Loader;
use ddosnik\task\WingsTask;

trait PlayerCustomizationTrait {

	public function getCustomColor() : string{
		return Loader::getInstance()->getPlayerData($this, 'CURRENTCOLOR')['color'];
	}

	public function isHaveCustomColor(string $color) : bool{
		return (bool) match ($color) {
            'BLUE' => Loader::getInstance()->getPlayerData($this, 'BLUETAG')['blue_tag'],
            'RED' => Loader::getInstance()->getPlayerData($this, 'REDTAG')['red_tag'],
            'GREEN' => Loader::getInstance()->getPlayerData($this, 'GREENTAG')['green_tag'],
            'YELLOW' => Loader::getInstance()->getPlayerData($this, 'YELLOWTAG')['yellow_tag'],
        };
    }

    public function getParticle() : string{
        return Loader::getInstance()->getPlayerData($this, 'PARTICLE')['particle'];
    }

    public function setCloak(string $skinId) : void{
    	$this->setSkin($this->getSkinData(), $skinId);
    }

    public function isHaveParticle(string $identifier) : bool{
        return Loader::getInstance()->getPlayerData($this, self::PARTICLES[$identifier])[$identifier];
    }

    public function equipWings(string $wings) : void{
        $shape = Loader::getInstance()->getWings()[$wings]['shape'];
        $nickname = $this->getLowerCaseName();
        $wingstask = new WingsTask($this, $shape);
        if (!isset(Loader::getInstance()->equip_players[$nickname])) {
            Loader::getInstance()->getServer()->getScheduler()->scheduleRepeatingTask($wingstask, 10);
            Loader::getInstance()->equip_players[$nickname]['id'] = $wingstask->getTaskId();
            Loader::getInstance()->equip_players[$nickname]['name'] = $wings;
            return;
        }
        if (Loader::getInstance()->equip_players[$nickname]['name'] === $wings) {
            $this->unEquipWings();
            return;
        } else {
            $this->unEquipWings();
            Loader::getInstance()->getServer()->getScheduler()->scheduleRepeatingTask($wingstask, 10);
            Loader::getInstance()->equip_players[$nickname]['id'] = $wingstask->getTaskId();
            Loader::getInstance()->equip_players[$nickname]['name'] = $wings;
        }
    }

    public function unEquipWings(): void{
        $nickname = $this->getLowerCaseName();
        if (isset(Loader::getInstance()->equip_players[$nickname])) {
            Loader::getInstance()->getServer()->getScheduler()->cancelTask(Loader::getInstance()->equip_players[$nickname]['id']);
            unset(Loader::getInstance()->equip_players[$nickname]);
        }
    }
}
