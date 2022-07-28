<?php

declare(strict_types=1);

namespace duels;

use pocketmine\plugin\PluginBase;
use pocketmine\command\{CommandSender, Command};
use pocketmine\Player;

use duels\handler\DuelsEventHandler;
use duels\task\DuelsArenaUpdate;
use duels\manager\ArenaManager;
use duels\arena\InventoryUtils;

class Duels extends PluginBase {
    private static $instance = null;
    
    public function onEnable() {
        self::$instance = &$this;
        
        $this->getServer()->getPluginManager()->registerEvents(new DuelsEventHandler, $this);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new DuelsArenaUpdate($this), 20);

        new InventoryUtils;
        new ArenaManager();
    }

    public function onDisable() : void{
        ArenaManager::onDisable();
    }
    
    public static function getInstance() : Duels{
        return self::$instance;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        if ($command == 'xyz') {
            if ($sender instanceof Player && $sender->isOp()) {
                $sender->sendMessage('X: '.$sender->x.', Y: '.$sender->y.', Z: '.$sender->z . ' yaw: ' . $sender->yaw . ' pitch: ' . $sender->pitch);
                return true;
            }
            return true;
        } elseif ($command == 'duels') {
            if (isset($args[0])) {
                if ($args[0] == 'join') {
                    if (ArenaManager::inGame($sender)) {
                        return true;
                    }
                    
                    if($sender->getLevel() !== $this->getServer()->getDefaultLevel()){
                        $sender->sendMessage('§l§b» §r§fВы можете зайти в игру только в лобби!');
                        return true;
                    }
                    
                    if (isset($args[1])) {
                        foreach(ArenaManager::getArenas() as $game){
                            if($game->getGamemode() === $args[1] and $game->canJoin()){
                                $game->joinGame($sender);
                                $sender->sendMessage('§l§b» §r§fИгра найдена!');
                                return true;
                            }
                        }
                        $sender->sendMessage('§l§b» §r§fНет свободных арен, попробуйте позже.');
                        return true;
                    }
                    return true;
                } elseif ($args[0] == 'quit') {
                    if (!ArenaManager::inGame($sender)) {
                        return true;
                    }

                    foreach (ArenaManager::getArenas() as $name => $game) {
                        if ($game->inGame($sender)) {
                            $game->quitGame($sender);
                        }
                    }
                    return true;
                }
            }
            return true;
        }
        return true;
    }
}
