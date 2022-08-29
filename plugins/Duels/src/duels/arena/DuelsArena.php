<?php

declare(strict_types=1);

namespace duels\arena;

use pocketmine\utils\Config;

use pocketmine\{
    Player,
    Server,
    GameMode
};
use pocketmine\tile\Chest;
use pocketmine\level\{Level, Position};
use pocketmine\level\sound\{ExperienceOrbSound,
    PopSound,
    EndermanTeleportSound,
    MinecraftSound,
    BlazeShootSound,
    NoteblockSound
};
use pocketmine\entity\Item as ItemEntity;
use pocketmine\math\Vector3;
use pocketmine\item\Item;
use pocketmine\lang\Translate;
use duels\task\WorldClear;

final class DuelsArena
{
    public const STATUS_WAITING = 0;
    public const STATUS_COUNTDOWN = 1;
    public const STATUS_RUNNING = 2;
    public const STATUS_END = 3;

    public const STATUS_TIMEOUT = 4;

    public array $last_move = [];

    private Config $config;
    private string $gamemode;

    private int $status = 0;

    private array $players = [];
    private array $spectators = [];

    private int $countdown = 10;
    private int $time = 0;

    private array $spawns = [];

    private int $isReady = 0;
    private array $points = [];
    private array $blockClear = [];

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->api = Server::getInstance()->getPluginManager()->getPlugin('SufixEngine');
        $this->gamemode = $config->get('gamemode');

        Server::getInstance()->loadLevel($levelname = $config->get('level-name'));
        $level = Server::getInstance()->getLevelByName($levelname);
        //	Server::getInstance()->getScheduler()->scheduleAsyncTask(new WorldClear(serialize($level->getChunks()), $level->getFolderName(), true));
        $level->setTime(3000);
        $level->stopTime();
        foreach ($level->getChunks() as $chunk) {
            for ($x = 0; $x < 16; ++$x) {
                for ($z = 0; $z < 16; ++$z) {
                    $chunk->setBiomeId($x, $z, 7);
                }
            }
        }
        $level->saveChunks();
        $level->save();
        $this->getArenaLevel()->setAutoSave(false);
        Server::getInstance()->unloadLevel($level);
    }

    public function addBlockToClear(Vector3 $pos)
    {
        $this->blockClear[$pos->x . ';' . $pos->y . ';' . $pos->z] = true;
    }

    public function startWorldClear(): void
    {
        if ($this->getArenaLevel() === null) {
            return;
        }
        Server::getInstance()->getScheduler()->scheduleAsyncTask(new WorldClear(serialize($this->blockClear), $this->config->get('level-name')));
        /*foreach($this->blockClear as $pos => $value){
            $pos = explode(';', $pos);
            $this->getArenaLevel()->setBlockIdAt((int) $pos[0], (int) $pos[1], (int) $pos[2], 0);
        }
        $this->getArenaLevel()->doChunkGarbageCollection();
        $this->getArenaLevel()->unloadChunks(true);
        $this->getArenaLevel()->clearCache(true);*/
        $this->blockClear = [];
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getGamemode(): string
    {
        return $this->gamemode;
    }

    public function isStarted() : bool
    {
        return ($this->status === self::STATUS_RUNNING);
    }

    public function getState(): int
    {
        return $this->status;
    }

    final public function canBlockPlace(): bool
    {
        return ($this->gamemode === 'sw' && $this->status === self::STATUS_RUNNING);
    }

    final public function canBlockBreak(): bool
    {
        return $this->canBlockPlace();
    }

    public function getPlayers(): array
    {
        return $this->players;
    }

    public function inGame(Player $player): bool
    {
        if (isset($this->players[$player->getName()]) || isset($this->spectators[$player->getName()])) {
            return true;
        }
        return false;
    }

    public function getArenaLevel(): ?Level
    {
        return Server::getInstance()->getLevelByName($this->config->get('level-name'));
    }

    public function getSpawnPos(): Position
    {
        $spawn = $this->config->getAll()['spawns'][count($this->players)];

        $x = $spawn['x'];
        $y = $spawn['y'];
        $z = $spawn['z'];

        return new Position($x, $y, $z, $this->getArenaLevel());
    }

    public function getSpawn(Player $player): Position
    {
        if (isset($this->spawns[$player->getName()])) {
            return $this->spawns[$player->getName()];
        }
        return Server::getInstance()->getDefaultLevel()->getSafeSpawn();
    }

    public function canJoin(): bool
    {
        return (count($this->players) < 2 and $this->status < 2);
    }

    final public function getTeam(Player $player): ?string
    {
        return $this->team[$player->getName()] ?? null;
    }

    final public function getPoints(Player $player): int
    {
        return $this->points[$player->getName()] ?? 0;
    }

    final public function kill(Player $player): void
    {
        $this->setLoser($player);
    }

    public function joinGame(Player $player): bool
    {
        if ($this->canJoin()) {
            $player->setGamemode(GameMode::ADVENTURE());
            $player->getInventory()->clearAll();
            $player->removeAllEffects();
            $player->setMaxHealth(20);
            $player->setHealth(20);
            $player->setFood(20);

            $player->addTitle('§b§lDUELS§r', $this->getPrefixMode());
            $this->players[$player->getName()] = $player;
            $this->points[$player->getName()] = 0;
            Server::getInstance()->loadLevel($this->config->get('level-name'));
            $this->getArenaLevel()->setAutoSave(false);
            $this->getArenaLevel()->setTime(3000);
            $this->getArenaLevel()->stopTime();
            $this->spawns[$player->getName()] = $this->getSpawnPos();
            $player->teleport($this->getSpawnPos());

            foreach ($this->players as $players) {
                $players->sendMessage(Translate::tr($player->getLocale(), 'saintpvp.duels.join', [$player->getRankColor() . $player->getName(true), count($this->players)]));
            }
            $player->getInventory()->setItem(1, Item::get(Item::DYE, 8)->setCustomName(Translate::tr($player->getLocale(), 'saintpvp.duels.ready'))->setType('saintpvp.duels.ready'));
            $player->getInventory()->setItem(8, Item::get(Item::BED, 14)->setCustomName(Translate::tr($player->getLocale(), 'saintpvp.duels.quit'))->setType('sainntpvp.duels.quit'));
            $player->getLevel()->addSound(new EndermanTeleportSound($player), [$player]);
            $player->setAllowFlight(false);
            $player->setFlying(false);
            $player->setMotion(new Position(0.0, -4.0, 0.0));
            return true;
        }
        return false;
    }

    public function quitGame(Player $player, bool $teleport = true): bool
    {
        $player->getInventory()->clearAll();
        $player->setImmobile(false);
        if ($teleport) {
            if ($this->isReady >= 1) {
                $this->isReady--;
            }
            $player->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
            $inv = $player->getInventory();
            $inv->setItem(2, ClickableItemFactory::CLOAKS());
            $inv->setItem(4, ClickableItemFactory::JOIN_ARENA());
            $inv->setItem(6, ClickableItemFactory::CUSTOMIZATION());
        }

        $player->setGamemode(GameMode::ADVENTURE());
        $player->removeAllEffects();
        $player->setMaxHealth(20);
        $player->setHealth(20);
        $player->setFood(20);
        if (isset($this->spawns[$player->getName()])) {
            unset($this->spawns[$player->getName()]);
        }

        if (isset($this->players[$player->getName()])) {
            unset($this->players[$player->getName()]);
        }
        if (isset($this->spectators[$player->getName()])) {
            unset($this->spectators[$player->getName()]);
        }
        if ($this->status === self::STATUS_RUNNING or $this->status === self::STATUS_TIMEOUT) {
            $this->gameStats();
        }
        return true;
    }

    public function setLoser(Player $player): void
    {
        if (isset($this->players[$player->getName()])) {
            unset($this->players[$player->getName()]);
            $this->spectators[$player->getName()] = $player;
            if (isset($this->spawns[$player->getName()])) {
                $player->teleport($this->spawns[$player->getName()]);
            }
            if ($player->isOnline()) {
                $player->getInventory()->clearAll();
                $player->removeAllEffects();
                $player->getLevel()->addSound(new MinecraftSound($player->asVector3(), 'mob.wither.death'), [$player]);
                $player->setGamemode(GameMode::SPECTATOR());
                $player->addTitle(Translate::tr($player->getLocale(), 'saintpvp.duels.lose'));
                $player->getInventory()->setItem(1, Item::get(Item::PAPER)->setCustomName(Translate::tr($player->getLocale(), 'saintpvp.duels.new_game')));
                $player->getInventory()->setItem(7, Item::get(Item::BED)->setCustomName(Translate::tr($player->getLocale(), 'saintpvp.duels.quit')));
            }
            $this->gameStats();
        }
    }

    public function addReady(Player $player): void
    {
        ++$this->isReady;
        foreach ($this->players as $pl) {
            $pl->sendMessage(Translate::tr($pl->getLocale(), 'saintpvp.duels.player_ready', [$player->getRankColor() . $player->getName(true)]));
        }
        $player->getInventory()->setItem(1, Item::get(Item::DYE, 10)->setCustomName('§l§d» §rВы готовы!'));
    }

    public function getOpponent(Player $player)
    {
        foreach ($this->players as $opponent) {
            if ($opponent !== $player) {
                return $opponent;
            }
        }
    }

    public function onUpdate(): bool
    {
        $right = str_repeat(' ', 73);
        if ($this->status === self::STATUS_WAITING) {
            if (count($this->players) > 1) {
                $this->status = self::STATUS_COUNTDOWN;
            } else {
                foreach ($this->players as $players) {
                    $players->sendTip(Translate::tr($players->getLocale(), 'saintpvp.duels.waiting_opponent'));
                }
            }
            return true;
        } elseif ($this->status === self::STATUS_COUNTDOWN) {
            foreach ($this->players as $players) {
                if ($this->isReady > 1) {
                    foreach ($this->players as $player) {
                        if (isset($this->spawns[$player->getName()])) {
                            $player->teleport($this->spawns[$player->getName()]);
                        }
                        InventoryUtils::addItemsByGamemode($player->getInventory(), $this->gamemode);
                        $player->sendMessage(Translate::tr($player->getLocale(), 'saintpvp.duels.start'));
                        
                        if ($this->gamemode === 'sw') {
                            foreach ($this->getArenaLevel()->getTiles() as $tile) {
                                for ($i = 0; $i < 27; $i++) {
                                    $tile_slots_free[$i] = $i;
                                }
                                if ($tile instanceof Chest) {
                                    foreach ($this->api->getSkyWarsItems() as $item) {
                                        var_dump($tile->getInventory()->setItem($tile_slots_free[array_rand($tile_slots_free)], $item));
                                        unset($tile_slots_free[array_rand($tile_slots_free)]);
                                    }
                                }
                            }
                        }
                        $this->last_move[$player->getLowerCaseName()] = microtime(true);
                    }
                    $this->status = self::STATUS_RUNNING;
                    return true;
                }

                if ($this->countdown > 0) {
                    if ($this->countdown <= 5 && $this->countdown >= 1) {
                        $players->sendMessage(Translate::tr($players->getLocale(), 'saintpvp.duels.countdown', [$this->countdown]));
                    }
                }
                $opponent = $this->getOpponent($players);
                if ($opponent !== null) {
                    $players->sendTip(Translate::tr($players->getLocale(), 'saintpvp.duels.running_hotbar', [$opponent->getRankColor() . $opponent->getName(), $this->getPrefixMode()]));
                }
            }
            if (count($this->players) <= 1) {
                $this->status = self::STATUS_WAITING;
                $this->countdown = 10;
            }

            if (--$this->countdown <= 0) {
                foreach ($this->players as $player) {
                    if (isset($this->spawns[$player->getName()])) {
                        $player->teleport($this->spawns[$player->getName()]);
                    }
                    InventoryUtils::addItemsByGamemode($player->getInventory(), $this->gamemode);
                    $player->sendMessage(Translate::tr($player->getLocale(), 'saintpvp.duels.start'));
                    $this->last_move[$player->getLowerCaseName()] = microtime(true);
                }
                $this->status = self::STATUS_RUNNING;
            }
            if ($this->countdown >= 1 and $this->countdown <= 5) {
                foreach ($this->players as $players) {
                    $players->getLevel()->addSound(new NoteblockSound($players), [$players]);
                }
            } else {
                foreach ($this->players as $players) {
                    $players->getLevel()->addSound(new PopSound($players), [$players]);
                }
            }
            return true;
        } elseif ($this->status === self::STATUS_RUNNING) {
            if ($this->gamemode === 'mlgrush') {
                foreach ($this->players as $player) {
                    if (($opponent = $this->getOpponent($player)) !== null) {
                        $player->sendTip('§r§b' . $player->getRankColor() . $player->getName(true) . '§7: §1' . $this->getPoints($player) . ' §l§8| §r§b' . $opponent->getRankColor() . $opponent->getName() . '§7: §c' . $this->getPoints($opponent));
                    }
                }
            }
            if (++$this->time >= 480) {
                $this->time = 6;
                $this->status = 3;
                $this->gameStats(true);
            }
            return true;
        } elseif ($this->status === self::STATUS_END) {
            $this->time--;
            if ($this->time <= 0) {
                $this->gameRestart();
            }
            return true;
        } elseif ($this->status === self::STATUS_TIMEOUT) {
            if (--$this->countdown <= 0) {
                $this->status = self::STATUS_RUNNING;
                foreach ($this->players as $player) {
                    $player->setImmobile(false);
                    $player->getLevel()->addSound(new NoteblockSound($player), [$player]);
                }
            } else {
                foreach ($this->players as $player) {
                    $player->getLevel()->addSound(new PopSound($player), [$player]);
                    $message = Translate::tr($player->getLocale(), 'saintpvp.duels.countdown', [$this->countdown]);
                    $player->sendPopup($message);
                    $player->sendMessage($message);
                }
            }
            return true;
        }
        return false;
    }

    public function getPrefixMode(): string
    {
        return match ($this->gamemode) {
            default => 'underfined',
            'sumo' => '§cSumo',
            'nodebuff' => '§cNodebuff',
            'combo' => '§cCombo',
            'fist' => '§cFist',
            'mlgrush' => '§cMLGRush',
            'bow' => '§cBow',
            'sw' => '§bSkyWars',
            'tntrun' => '§cTNT§fRun'
        };
    }

    public function gameStats(bool $fullTime = false): void
    {
        //$this->startWorldClear();
        $this->status = self::STATUS_END;
        $this->time = 6;
        foreach ($this->players as $players) {
            $players->getInventory()->clearAll();
            $players->setMaxHealth(20);
            $players->setHealth(20);
            $players->setFood(20);
            $players->removeAllEffects();
            $players->setGamemode(GameMode::ADVENTURE());
            $players->updateNameTag();
            $players->getInventory()->setItem(1, Item::get(Item::PAPER)->setCustomName(Translate::tr($players->getLocale(), 'saintpvp.duels.new_game')));
            $players->getInventory()->setItem(7, Item::get(Item::BED, 0, 1)->setCustomName(Translate::tr($players->getLocale(), 'saintpvp.duels.quit')));
        }
        if ($fullTime) {
            foreach ($this->players as $players) {
                $players->sendMessage('§l§b» §r§fВремя закончилось, ничья!');
            }
        } else {
            if (count($this->players) === 1) {
                foreach ($this->players as $players) {
                    $players->addTitle('§6VICTORY!§r');
                    $players->addWin();
                    $this->spectators[$players->getName()] = $players;
                }
            }
        }
    }

    public function gameRestart(): void
    {
        foreach ($this->players as $players) {
            $this->quitGame($players);
        }
        foreach ($this->spectators as $players) {
            $this->quitGame($players);
        }
        $this->getArenaLevel()->setAutoSave(false);
        Server::getInstance()->unloadLevel(Server::getInstance()->getLevelByName($this->config->get('level-name')));
        $this->players = [];
        $this->spectators = [];
        $this->spawns = [];

        $this->status = 0;
        $this->time = 0;
        $this->countdown = 10;
        $this->isReady = 0;
    }
}
