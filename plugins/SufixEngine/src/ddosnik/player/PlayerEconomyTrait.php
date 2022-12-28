<?php

declare(strict_types=1);

namespace ddosnik\player;

use ddosnik\Loader;

trait PlayerEconomyTrait {

    public function addMoney(int $value): void{
        $money = Loader::getInstance()->getPlayerData($this, 'MONEY')['balance'];
        Loader::getInstance()->setPlayerData($this, 'MONEY', $value + $money);
    }

    public function getMoney(): int{
        return Loader::getInstance()->getPlayerData($this, 'MONEY')['balance'];
    }

    public function remMoney(int $value): void{
        $money = Loader::getInstance()->getPlayerData($this, 'MONEY')['balance'];
        Loader::getInstance()->setPlayerData($this, 'MONEY', $money - $value);
    }
}