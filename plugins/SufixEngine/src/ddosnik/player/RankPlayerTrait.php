<?php 

namespace ddosnik\player;

use ddosnik\Loader;

trait RankPlayerTrait {

	public function getRank() : string{
		return Loader::getInstance()->getPlayerData($this, 'GROUP')['group'];
	}

	public function setRank(string $rank) : void{
		Loader::getInstance()->setPlayerData($this, 'GROUP', $rank);
	}

	public function getRankColor() : string{
		return self::RANKS_COLORS[$this->getRank()];
	}
}