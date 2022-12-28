<?php

namespace ddosnik\player;

use ddosnik\Loader;

trait PlayerRankTrait {

	public function getRank() : string{
		return Loader::getInstance()->getPlayerData($this, 'GROUP')['group'];
	}

	public function getSufixNameTag() : string{
		return match ($this->getRank()) {
            'GUEST' => '§7(§r§e' . $this->getLvl() . '§7) ' . $this->getCustomColor() . $this->getName(),
            'GUEST+' => '§7(§r§e' . $this->getLvl() . '§7) §aＧｕｅｓｔ§6+ ' . $this->getCustomColor() . $this->getName(),
            'YT' => '§7(§r§e' . $this->getLvl() . '§7) §cＹｏｕＴｕｂｅ ' . $this->getCustomColor() . $this->getName(),
            'SAKURA' => '§7(§r§e' . $this->getLvl() . '§7) §dＳａｋｕｒａ ' . $this->getCustomColor() . $this->getName(),
            'MOD' => '§7(§r§e' . $this->getLvl() . '§7) §6Ｍｏｄｅｒａｔｏｒ ' . $this->getCustomColor() . $this->getName(),
            'OWNER' => '§7(§r§e' . $this->getLvl() . '§7) §aＯｗｎｅｒ ' . $this->getCustomColor() . $this->getName(),
        };
    }

  public function updateNameTag() : void{
    $this->setNameTag($this->getSufixNameTag() . PHP_EOL . $this->getOsAsString());
  }

  public function updateDisplayName() : void{
    $this->setDisplayName($this->getSufixNameTag());
  }

	public function setRank(string $rank) : void{
		Loader::getInstance()->setPlayerData($this, 'GROUP', $rank);
	}

	public function getRankColor() : string{
		return self::RANKS_COLORS[$this->getRank()];
	}
}
