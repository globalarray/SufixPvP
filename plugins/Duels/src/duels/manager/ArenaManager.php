<?php

declare(strict_types=1);

namespace duels\manager;

use pocketmine\utils\Config;
use pocketmine\Player;

use duels\Duels;
use duels\arena\DuelsArena;

use function scandir;
use function is_dir;
use function mkdir;

class ArenaManager {
    private static array $arenas = [];
    
    public function __construct() {
        self::arenaLoad();
    }
    
    private static function arenaLoad() : void{
        $dir = Duels::getInstance()->getDataFolder();
        
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
            mkdir($dir . 'arenas', 0775, true);
        }

        $i = 0;
        foreach (scandir($dir . 'arenas') as $file) {
            if ($file != '.' && $file != '..') {
                $data = new Config($dir . 'arenas/'. $file, Config::YAML);
                $i++;
                self::$arenas[$i] = new DuelsArena($data);
            }
        }
        Duels::getInstance()->getLogger()->notice('Loaded '.sizeof(self::$arenas).' duels-worlds');
    }

    final public static function onDisable() : void{

    }

    public static function getArenas() : array{
        return self::$arenas;
    }
    
    public static function getGameByPlayer(Player $player) {
        foreach (self::$arenas as $name => $game) {
            if ($game->inGame($player)) {
                return $game;
            }
        }
        return null;
    }
    
    public static function inGame(Player $player) : bool{
        foreach (self::$arenas as $name => $game) {
            if ($game->inGame($player)) {
                return true;
            }
        }
        return false;
    }
    
    final public static function getPlayers(string $mode) : int{
        $count = 0;
        foreach(self::$arenas as $game){
            if($game->getGamemode() === $mode){
                $count += count($game->getPlayers());
            }
        }
        return $count;
    }
}
