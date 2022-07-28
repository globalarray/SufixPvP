<?php

declare(strict_types=1);

namespace ddosnik\flytext\handler;

use ddosnik\flytext\scheduler\ParticleDespawn;
use ddosnik\flytext\scheduler\ParticleSpawn;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\Player;
use ddosnik\Loader;
use ddosnik\flytext\scheduler\ParticleUpdate;

final class EventHandler implements Listener{
	/** @var Loader */
	private $main;
	
	/**
	 * EventHandler constructor.
	 * 
	 * @param Loader $main
	 * 
	 * @return void
	*/
	public function __construct(Loader $main){
		$this->main = $main;
	}
	
	/**
	 * @param EntityLevelChangeEvent $event
	 * 
	 * @return void
	*/
	public function handleEntityLevelChange(EntityLevelChangeEvent $event) : void{
		if(($player = $event->getEntity()) instanceof Player){
			if($event->getTarget() === $this->main->getServer()->getDefaultLevel()){
				$this->main->getServer()->getScheduler()->scheduleAsyncTask(new ParticleSpawn($this->main->getParticles(), $player->getName()));
			}else{
				$this->main->getServer()->getScheduler()->scheduleAsyncTask(new ParticleDespawn($this->main->getParticles(), $player->getName()));
			}
		}
	}
	
	/**
	 * @param PlayerJoinEvent $event
	 * 
	 * @return void
	*/
	public function handleJoin(PlayerJoinEvent $event) : void{
		$player = $event->getPlayer();
		ParticleUpdate::getInstance()->statistics($this->main);
		if($player->getLevel() === $this->main->getServer()->getDefaultLevel()){
			$this->main->getServer()->getScheduler()->scheduleAsyncTask(new ParticleSpawn($this->main->getParticles(), $player->getName()));
		}
	}
}
