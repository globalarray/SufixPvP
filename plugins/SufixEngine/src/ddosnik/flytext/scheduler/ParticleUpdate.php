<?php

declare(strict_types=1);

namespace ddosnik\flytext\scheduler;

use pocketmine\scheduler\PluginTask;
use pocketmine\lang\Translate;
use pocketmine\Player;
use ddosnik\Loader;
use duels\manager\ArenaManager;

final class ParticleUpdate extends PluginTask{
	private const LEADERBOARD_UPDATE = 3000;
	private const STATISTICS_UPDATE = 30;

	private $leaderboard = self::LEADERBOARD_UPDATE;
	private $statistics = self::STATISTICS_UPDATE;
	private static $instance = null;

	/**
	 * ParticleUpdate constructor.
	 * 
	 * @param Loader $main
	 * 
	 * @return void
	*/
	public function __construct(Loader $main){
		parent::__construct($main);
		$this->api = $main;
		static::$instance = &$this;
	}

	final public static function getInstance() : self{
	    return static::$instance;
    }
	
	private function duels() : void{
		$modes = ['sumo', 'tntrun'];
		$i = 3;
		foreach($modes as $mode){
			$this->getOwner()->getParticles()[$i]->setTitle('§fИгроков§7: §c' . ArenaManager::getPlayers($mode));
			++$i;
		}
	}

	final public function statistics(Loader $owner) : void{
		$particles = &$owner->particles;
		foreach($owner->getServer()->getDefaultLevel()->getPlayers() as $player){
			$particles[5]->setTitle(Translate::tr($player->getLocale(), 'sufixpvp.floatingtext.statistics'), [$player]);
			$particles[6]->setTitle(Translate::tr($player->getLocale(), 'sufixpvp.floatingtext.statistics.nickname', [$player->getName()]), [$player]);
			$particles[7]->setTitle(Translate::tr($player->getLocale(), 'sufixpvp.floatingtext.statistics.rank', [$player->getRankColor() . $player->getRank()]), [$player]);
			$particles[8]->setTitle(Translate::tr($player->getLocale(), 'sufixpvp.floatingtext.statistics.wins', [$player->getWins()]), [$player]);
			$particles[9]->setTitle(Translate::tr($player->getLocale(), 'sufixpvp.floatingtext.statistics.kills', [$player->getKills()]), [$player]);
		}
	}

	final public function sendStatistics(Loader $owner, Player $player) : void{
        $particles = &$owner->particles;
        $particles[5]->setTitle(Translate::tr($player->getLocale(), 'sufixpvp.floatingtext.statistics'), [$player]);
        $particles[6]->setTitle(Translate::tr($player->getLocale(), 'sufixpvp.floatingtext.statistics.nickname', [$player->getName()]), [$player]);
        $particles[7]->setTitle(Translate::tr($player->getLocale(), 'sufixpvp.floatingtext.statistics.rank', [$player->getRankColor() . $this->api->getGroup($player)]), [$player]);
        $particles[8]->setTitle(Translate::tr($player->getLocale(), 'sufixpvp.floatingtext.statistics.wins', [$player->getWins()]), [$player]);
        $particles[9]->setTitle(Translate::tr($player->getLocale(), 'sufixpvp.floatingtext.statistics.kills', [$player->getKills()]), [$player]);
    }


	/**
	 * @param int $currentTick
	 * 
	 * @return void
	*/
	final public function onRun(int $currentTick) : void{
		if(--$this->leaderboard <= 0){
			$this->leaderboard = self::LEADERBOARD_UPDATE;
		}
		if(--$this->statistics <= 0){
			foreach($this->getOwner()->getServer()->getDefaultLevel()->getPlayers() as $player){
			    $this->sendStatistics($this->getOwner(), $player);
            }
			$this->statistics = self::STATISTICS_UPDATE;
		}
		$this->duels();
	}
}