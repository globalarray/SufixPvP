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

//declare(strict_types=1);

namespace ddosnik;

use pocketmine\block\Block;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\item\{Item, ItemIds};
use pocketmine\level\Position;
use ddosnik\task\{Hotbar, LeaveTask, Broadcaster, ParticlesManager};
use pocketmine\math\Vector3;
use pocketmine\entity\{Entity, Attribute, Zombie};
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\level\particle\{DustParticle, RedstoneParticle, Particle};
use ddosnik\wings\task\WingsTask;
use ddosnik\wings\utils\Particles;
use ddosnik\flytext\handler\EventHandler;
use ddosnik\flytext\particle\TextParticle;
use ddosnik\flytext\scheduler\ParticleUpdate;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\network\mcpe\protocol\{InteractPacket,
    BossEventPacket,
    AddEntityPacket,
    MoveEntityPacket,
    SetEntityDataPacket,
    UpdateAttributesPacket,
    SetTimePacket
};
use pocketmine\event\entity\{EntityDamageEvent, EntityDamageByEntityEvent};
use pocketmine\event\player\{
    PlayerJoinEvent,
    PlayerMoveEvent,
    PlayerChatEvent,
    PlayerQuitEvent,
    PlayerDeathEvent,
    PlayerDropItemEvent,
    PlayerCommandPreprocessEvent,
    PlayerExhaustEvent,
    PlayerItemConsumeEvent,
    PlayerRespawnEvent,
    PlayerInteractEvent,
    PlayerPreLoginEvent,
    PlayerCreationEvent
};

use ddosnik\commands\{
    KickCommand
};
use ddosnik\player\SufixPlayer;
use ddosnik\menu\{
    ClickableItemFactory,
    ClickableItem
};

use SQLite3;

class Loader extends PluginBase implements Listener {

    const Prefix = '§l§d» §r';
    /** @array Broadcast */
    const MESSAGES = ['sufixpvp.broadcast.site', 'sufixpvp.broadcast.emoji', 'sufixpvp.broadcast.thanks', 'sufixpvp.broadcast.duels', 'sufixpvp.broadcast.follow_our'];
    /** @array Franchises */
    const FRANCHISES = ['GUEST' => 0, 'GUEST+' => 1, 'YT' => 2, 'SAKURA' => 3, 'MOD' => 4, 'OWNER' => 5];
    /** @var array */
    const CUSTOM_WINGS = [
        'EXAMPLE_WINGS' =>
            ['shape' => [
                [0, 0, 'f', 'f', 'f', 'f', 0, 0, 0, 0, 'f', 'f', 'f', 'f', 0, 0],
                [0, 'f', 'f', 'f', 'f', 'f', 'f', 0, 0, 'f', 'f', 'f', 'f', 'f', 'f', 0],
                ['f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f'],
                ['f', 'f', 'f', 'f', 0, 0, 0, 'f', 'f', 0, 0, 0, 'f', 'f', 'f', 'f'],
                ['f', 'f', 'f', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'f', 'f', 'f'],
                ['f', 'f', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'f', 'f']
            ]
        ]
    ];

    /** @var SQLite3|null */
    public ?SQLite3 $data = null;
    /** @var Loader */
    private static Loader $instance;

    public int $interval = 10;
    /** FlyTexts */
    public array $particles = array();
    public array $players = array();
    public array $ffaworlds = [
        'aCOMBO' => 0,
        '6GAPPLE' => 1,
        '4FIST' => 2
    ];
    /** Wings */
    private array $equip_players = [];
    /** @var array */
    private array $lastDamage = [];

    public function onEnable() : void{
        $floating_texts = [
            [1, new Vector3(11.5, 41.32, 242.5), '§l§d» §r§fDuels§7: §cSumo §7[§aNEW§7]'],
            [2, new Vector3(9.5, 41.32, 247.5), '§l§d» §r§fDuels§7: §cMLGRush'],
            [3, new Vector3(11.5, 41, 242.5), '§fИгроков§7: §c0'],
            [4, new Vector3(9.5, 41, 242.5), '§fИгроков§7: §c0'],
            [5, new Vector3(7.5, 39.9, 260), 'sufixpvp.floatingtext.statistics'],
            [6, new Vector3(7.5, 39.7, 260), 'sufixpvp.floatingtext.nickname'],
            [7, new Vector3(7.5, 39.3, 260), 'sufixpvp.floatingtext.rank'],
            [8, new Vector3(7.5, 39.5, 260), 'sufixpvp.floatingtext.wins'],
            [9, new Vector3(7.5, 39.1, 260), 'sufixpvp.floatingtext.kills']
        ];
        ClickableItemFactory::init();
        for ($i = 0; $i < sizeof($floating_texts); $i++) {
            $this->registerParticle($floating_texts[$i][0], $floating_texts[$i][1], $floating_texts[$i][2], '');
        }
        /** Tasks */
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new ParticleUpdate($this), 20 * 20);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new Broadcaster($this), 20 * 60 * 4);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new Hotbar($this), 20);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new ParticlesManager($this), 20);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new LeaveTask($this, $this->interval), 20);
        /** Register events */
        $this->getServer()->getPluginManager()->registerEvents(new EventHandler($this), $this);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        /** Auth */
        $this->auth = $this->getServer()->getPluginManager()->getPlugin('Authorization');
        $this->registerCommands();
        /** Loader FFA-worlds */
        foreach ($this->ffaworlds as $world => $num) {
            $this->getServer()->loadLevel($world);
        }
        $this->getLogger()->notice('Loaded ' . sizeof($this->ffaworlds) . ' ffa-worlds');
        if (!file_exists($this->getDataFolder() . "database")) {
            @mkdir($this->getDataFolder() . 'database');
        }
        $this->data = new SQLite3($this->getDataFolder() . 'database/database.db');
        $this->data->query('CREATE TABLE IF NOT EXISTS `database`(`nickname` TEXT NOT NULL, `balance` TEXT NOT NULL, `particle` TEXT NOT NULL, `wins` TEXT NOT NULL, `lvl` TEXT NOT NULL, `color` TEXT NOT NULL, `blue_tag` TEXT NOT NULL, `red_tag` TEXT NOT NULL, `green_tag` TEXT NOT NULL, `yellow_tag` TEXT NOT NULL, `group` TEXT NOT NULL, `kills` TEXT NOT NULL, `exp` TEXT NOT NULL, `factor` TEXT NOT NULL, `heart` TEXT NOT NULL, `custom` TEXT NOT NULL);');
        self::$instance = $this;
    }

    public function onRegisterSufixPlayer(PlayerCreationEvent $event) : void{
        $event->setPlayerClass(SufixPlayer::class);
    }

    public function handleInventoryTransaction(InventoryTransactionEvent $event): void
    {
        if ($event->getTransaction()->getPlayer()->getLevel()->getFolderName() === 'lobby') $event->setCancelled(true);
    }

    /**
     * @return array
     */
    public function getParticles(): array
    {
        return $this->particles;
    }

    /**
     * @param int $id
     * @param string $title
     * @param string $text
     * @param Vector3 $pos
     *
     * @return void
     */
    public function registerParticle(int $id, Vector3 $pos, string $title = '', string $text = ''): void
    {
        $this->particles[$id] = new TextParticle($id, $title, $text, $pos);
    }

    /**
     * @return array|\array[][]
     */
    public function getWings(): array
    {
        return self::CUSTOM_WINGS;
    }

    /**
     * @param PlayerJoinEvent $event
     * @return void
     */
    public function handleDisplayAndNametag(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $this->equipWings($player, 'EXAMPLE_WINGS');
        $franchise = match ($this->getGroup($player)) {
            'GUEST' => '§7(§r§e' . $this->getLvL($player) . '§7) ' . $this->getCustomizeCurrentColor($player) . $player->getName(),
            'GUEST+' => '§7(§r§e' . $this->getLvL($player) . '§7) §aＧｕｅｓｔ§6+ ' . $this->getCustomizeCurrentColor($player) . $player->getName(),
            'YT' => '§7(§r§e' . $this->getLvL($player) . '§7) §cＹｏｕＴｕｂｅ ' . $this->getCustomizeCurrentColor($player) . $player->getName(),
            'SAKURA' => '§7(§r§e' . $this->getLvL($player) . '§7) §dＳａｋｕｒａ ' . $this->getCustomizeCurrentColor($player) . $player->getName(),
            'MOD' => '§7(§r§e' . $this->getLvL($player) . '§7) §6Ｍｏｄｅｒａｔｏｒ ' . $this->getCustomizeCurrentColor($player) . $player->getName(),
            'OWNER' => '§7(§r§e' . $this->getLvL($player) . '§7) §aＯｗｎｅｒ ' . $this->getCustomizeCurrentColor($player) . $player->getName(),
        };
        $player->setDisplayName($franchise);
        $player->setNameTag($franchise);
    }

    /**
     * @param Player $player
     * @return string
     */
    public function getCustomizeCurrentColor(Player $player): string
    {
        return $this->getPlayerData($player, 'CURRENTCOLOR')['color'];
    }

    /**
     * @param Player $player
     * @return int
     */
    public function getExpNextLevel(Player $player): int
    {
        $next = [0, 100, 500, 1500, 3000, 5000, 7000, 10000, 15000, 20000, 30000];
        return $next[$this->getLvL($player)];
    }

    /**
     * @param Player $player
     * @param string $value
     * @return bool
     */
    public function getCustomizeColor(Player $player, string $value): bool
    {
        return match ($value) {
            'BLUE' => $this->getPlayerData($player, 'BLUETAG')['blue_tag'],
            'RED' => $this->getPlayerData($player, 'REDTAG')['red_tag'],
            'GREEN' => $this->getPlayerData($player, 'GREENTAG')['green_tag'],
            'YELLOW' => $this->getPlayerData($player, 'YELLOWTAG')['yellow_tag'],
        };
    }

    /**
     * @param Player $player
     * @return int
     */
    public function getLvL(Player $player): int
    {
        return $this->getPlayerData($player, 'LEVEL')['lvl'];
    }

    /**
     * @param Player $player
     * @param int $lvl
     * @return void
     */
    public function setLvL(Player $player, int $lvl): void
    {
        $this->setPlayerData($player, 'LEVEL', $lvl);
    }

    /**
     * @param Player $player
     * @return void
     */
    public function addWin(Player $player): void
    {
        $wins = $this->getPlayerData($player, 'WINS')['wins'];
        $this->setPlayerData($player, 'WINS', $wins + 1);
    }

    /**
     * @param Player $player
     * @param int $value
     * @return void
     */
    public function addMoney(Player $player, int $value): void
    {
        $money = $this->getPlayerData($player, 'MONEY')['balance'];
        $this->setPlayerData($player, 'MONEY', $value + $money);
    }

    /**
     * @param Player $player
     * @return int
     */
    public function getMoney(Player $player): int
    {
        return $this->getPlayerData($player, 'MONEY')['balance'];
    }

    /**
     * @param Player $player
     * @param int $value
     * @return void
     */
    public function remMoney(Player $player, int $value): void
    {
        $money = $this->getPlayerData($player, 'MONEY')['balance'];
        $this->setPlayerData($player, 'MONEY', $value - $money);
    }

    /**
     * @param Player $player
     * @param string $property
     * @return array|false
     */
    public function getPlayerData(SufixPlayer $player, string $property)
    {
        $nickname = $player->getLowerCaseName();
        return match ($property) {
            'MONEY' => $this->data->query("SELECT `balance` FROM `database` WHERE `nickname` = '$nickname'")->fetchArray(SQLITE3_ASSOC),
            'PARTICLE' => $this->data->query("SELECT `particle` FROM `database` WHERE `nickname` = '$nickname'")->fetchArray(SQLITE3_ASSOC),
            'WINS' => $this->data->query("SELECT `wins` FROM `database` WHERE `nickname` = '$nickname'")->fetchArray(SQLITE3_ASSOC),
            'LEVEL' => $this->data->query("SELECT `lvl` FROM `database` WHERE `nickname` = '$nickname'")->fetchArray(SQLITE3_ASSOC),
            'CURRENTCOLOR' => $this->data->query("SELECT `color` FROM `database` WHERE `nickname` = '$nickname'")->fetchArray(SQLITE3_ASSOC),
            'BLUETAG' => $this->data->query("SELECT `blue_tag` FROM `database` WHERE `nickname` = '$nickname'")->fetchArray(SQLITE3_ASSOC),
            'REDTAG' => $this->data->query("SELECT `red_tag` FROM `database` WHERE `nickname` = '$nickname'")->fetchArray(SQLITE3_ASSOC),
            'GREENTAG' => $this->data->query("SELECT `green_tag` FROM `database` WHERE `nickname` = '$nickname'")->fetchArray(SQLITE3_ASSOC),
            'YELLOWTAG' => $this->data->query("SELECT `yellow_tag` FROM `database` WHERE `nickname` = '$nickname'")->fetchArray(SQLITE3_ASSOC),
            'GROUP' => $this->data->query("SELECT `group` FROM `database` WHERE `nickname` = '$nickname'")->fetchArray(SQLITE3_ASSOC),
            'KILLS' => $this->data->query("SELECT `kills` FROM `database` WHERE `nickname` = '$nickname'")->fetchArray(SQLITE3_ASSOC),
            'EXPIRIENCE' => $this->data->query("SELECT `exp` FROM `database` WHERE `nickname` = '$nickname'")->fetchArray(SQLITE3_ASSOC),
            'FACTOR' => $this->data->query("SELECT `factor` FROM `database` WHERE `nickname` = '$nickname'")->fetchArray(SQLITE3_ASSOC),
            'HEARTPARTICLE' => $this->data->query("SELECT `heart` FROM `database` WHERE `nickname` = '$nickname'")->fetchArray(SQLITE3_ASSOC),
            'CUSTOM_ITEM' => $this->data->query("SELECT `custom` FROM `database` WHERE `nickname` = '$nickname'")->fetchArray(SQLITE3_ASSOC),
        };
    }

    /**
     * @param Player $player
     * @param string $property
     * @param mixed $value
     * @return void
     */
    public function setPlayerData(Player $player, string $property, mixed $value): void
    {
        $nickname = $player->getLowerCaseName();
        switch ($property) {
            case 'MONEY':
                $this->data->query("UPDATE `database` SET `balance` = '$value' WHERE `nickname` = '$nickname'");
                break;
            case 'PARTICLE':
                $this->data->query("UPDATE `database` SET `particle` = '$value' WHERE `nickname` = '$nickname'");
                break;
            case 'WINS':
                $this->data->query("UPDATE `database` SET `wins` = '$value' WHERE `nickname` = '$nickname'");
                break;
            case 'LEVEL':
                $this->data->query("UPDATE `database` SET `lvl` = '$value' WHERE `nickname` = '$nickname'");
                break;
            case 'CURRENTCOLOR':
                $this->data->query("UPDATE `database` SET `color` = '$value' WHERE `nickname` = '$nickname'");
                break;
            case 'BLUETAG':
                $this->data->query("UPDATE `database` SET `blue_tag` = '$value' WHERE `nickname` = '$nickname'");
                break;
            case 'REDTAG':
                $this->data->query("UPDATE `database` SET `red_tag` = '$value' WHERE `nickname` = '$nickname'");
                break;
            case 'GREENTAG':
                $this->data->query("UPDATE `database` SET `green_tag` = '$value' WHERE `nickname` = '$nickname'");
                break;
            case 'YELLOWTAG':
                $this->data->query("UPDATE `database` SET `yellow_tag` = '$value' WHERE `nickname` = '$nickname'");
                break;
            case 'GROUP':
                $this->data->query("UPDATE `database` SET `group` = '$value' WHERE `nickname` = '$nickname'");
                break;
            case 'KILLS':
                $this->data->query("UPDATE `database` SET `kills` = '$value' WHERE `nickname` = '$nickname'");
                break;
            case 'EXPIRIENCE':
                $this->data->query("UPDATE `database` SET `exp` = '$value' WHERE `nickname` = '$nickname'");
                break;
            case 'FACTOR':
                $this->data->query("UPDATE `database` SET `factor` = '$value' WHERE `nickname` = '$nickname'");
                break;
            case 'HEARTPARTICLE':
                $this->data->query("UPDATE `database` SET `heart` = '$value' WHERE `nickname` = '$nickname'");
                break;
            case 'CUSTOM_ITEM':
                $this->data->query("UPDATE `database` SET `custom` = '$value' WHERE `nickname` = '$nickname'");
                break;
        }
    }

    /**
     * @param Player $player
     * @return string
     */
    public function getRankColor(Player $player): string
    {
        return match ($this->getGroup($player)) {
            default => '§7',
            'GUEST+' => '§a',
            'YT' => '§c',
            'SAKURA' => '§d',
            'MOD' => '§6',
            'OWNER' => '§b',
        };
    }

    /**
     * @param PlayerJoinEvent $event
     * @return void
     */
    public function handleSetOS(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $os = match (true) {
            ($player->getDeviceOS() !== 1 && $player->getDeviceOS() !== 2) => '§r§8Windows 10',
            ($player->getDeviceModel() === 'Linux') => '§r§8Bedrock Launcher',
            ($player->getDeviceModel() !== 'Linux' && $player->getDeviceOS() === 1) => '§r§8Android',
            ($player->getDeviceModel() !== 'Linux' && $player->getDeviceOS() === 2) => '§r§8iOS',
        };
        $player->setNameTag($player->getNameTag() . PHP_EOL . $os);
    }

    /**
     * @param PlayerChatEvent $event
     * @return void
     */
    public function handleChat(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        $format = match ($this->getGroup($player)) {
            'GUEST' => '§7(§r§e' . $this->getLvL($player) . '§7) ' . $this->getCustomizeCurrentColor($player) . $player->getName() . '§7: ' . $this->removeColors($message),
            'GUEST+' => '§7(§r§e' . $this->getLvL($player) . '§7) §aＧｕｅｓｔ§6+ ' . $this->getCustomizeCurrentColor($player) . $player->getName() . '§7: ' . $this->removeColors($message),
            'YT' => '§7(§r§e' . $this->getLvL($player) . '§7) §cＹｏｕＴｕｂｅ ' . $this->getCustomizeCurrentColor($player) . $player->getName() . '§7: ' . $this->removeColors($message),
            'SAKURA' => '§7(§r§e' . $this->getLvL($player) . '§7) §dＳａｋｕｒａ ' . $this->getCustomizeCurrentColor($player) . $player->getName() . '§7: ' . $this->removeColors($message),
            'MOD' => '§7(§r§e' . $this->getLvL($player) . '§7) §6Ｍｏｄｅｒａｔｏｒ ' . $this->getCustomizeCurrentColor($player) . $player->getName() . '§7: ' . $this->removeColors($message),
            'OWNER' => '§7(§r§e' . $this->getLvL($player) . '§7) §aＯｗｎｅｒ ' . $this->getCustomizeCurrentColor($player) . $player->getName() . '§7: ' . $this->removeColors($message),
        };
        $event->setFormat($format);
    }

    /**
     * @param string $message
     * @return string
     */
    public function removeColors(string $message): string
    {
        return str_replace(array(TextFormat::BLACK, TextFormat::DARK_BLUE, TextFormat::DARK_GREEN, TextFormat::DARK_AQUA,
            TextFormat::DARK_RED, TextFormat::DARK_PURPLE, TextFormat::GOLD, TextFormat::GRAY, TextFormat::DARK_GRAY, TextFormat::BLUE,
            TextFormat::GREEN, TextFormat::AQUA, TextFormat::RED, TextFormat::LIGHT_PURPLE, TextFormat::YELLOW, TextFormat::WHITE,
            TextFormat::OBFUSCATED, TextFormat::BOLD, TextFormat::ITALIC, TextFormat::RESET), '', $message);
    }

    /**
     * @param PlayerPreLoginEvent $event
     * @return void
     */
    public function createData(PlayerPreLoginEvent $event): void
    {
        $nickname = $event->getPlayer()->getLowerCaseName();
        if (!($this->data->query("SELECT * FROM `database` WHERE `nickname` = '$nickname'")->fetchArray(SQLITE3_ASSOC))) {
            $this->data->query("INSERT INTO `database`(`nickname`,`balance`, `particle`, `wins`, `lvl`, `color`, `blue_tag`, `red_tag`, `green_tag`, `yellow_tag`, `group`, `kills`, `exp`, `factor`, `heart`, `custom`) VALUES('{$nickname}', 0, false, 0, 1, '§7', false, false, false, false, 'GUEST', 0, 0, 1, false, false)");
        }
    }

    /**
     * @param Player $player
     * @param string $cloak
     * @return void
     */
    public function setCloak(Player $player, string $cloak): void
    {
        $player->setSkin($player->getSkinData(), $cloak);
    }

    /*public function checkPlayerBan(\pocketmine\event\player\PlayerPreLoginEvent $pk)
    {
        $bans = new Config($this->getDataFolder() . "database/bans/" . $pk->getPlayer()->getLowerCaseName() . ".json", Config::JSON);
        if ($bans->get('BAN') === true) {
            $pk->getPlayer()->close('', "§dYou are banned!\n" . Loader::Prefix . "§rReason: §e{$bans->get('REASON')}\n" . Loader::Prefix . "§rFrom: §e{$bans->get('ADMIN')}");
        }
    }
*/
    private function registerCommands(): void{
        $commands = [
            new KickCommand($this)
        ];
        $aliased = [];
        foreach ($commands as $cmd) {
            $commands[$cmd->getName()] = $cmd;
            $aliased[$cmd->getName()] = $cmd->getName();
            foreach ($cmd->getAliases() as $alias) {
                $aliased[$alias] = $cmd->getName();
            }
        }
        $this->getServer()->getCommandMap()->registerAll("sufix", $commands);
    }

    public function onCommand(CommandSender $p, Command $cmd, string $label, array $args): bool
    {
        switch ($cmd->getName()) {
            case "tpw":
                $p->getServer()->loadLevel($args[0]);
                $level = $p->getServer()->getLevelByName($args[0])->getSafeSpawn();
                $p->teleport($level);
                $p->sendMessage("§8[§aシ§8] §fВы успешно телепортировались");
                break;
            case "unban":
                if ($p instanceof Player) {
                    if ($this->getGroup($p) !== "MOD" && $this->getGroup($p) !== "OWNER") {
                        $p->sendMessage(Loader::Prefix . "§cДанная команда доступна игрокам с привилегией §l§6Ｍｏｄｅｒａｔｏｒ§r\n" . Loader::Prefix . "Повысить свой §aранг§r можно в нашем магазине §8- §epay.sufixpvp.su");
                        return true;
                    }
                }
                if (!isset($args[0])) {
                    $p->sendMessage(Loader::Prefix . "Используйте §a/unban <никнейм игрока>");
                    return true;
                }
                $bans = new Config($this->getDataFolder() . "database/bans/" . mb_strtolower($args[0]) . ".json", Config::JSON);
                if (!$p instanceof Player) {
                    $this->getServer()->broadcastMessage(Loader::Prefix . "§b{$args[0]} §fбыл разблокирован §dSERVER§r.");
                    $bans->set('BAN', false);
                    $bans->save();
                    return true;
                }
                $this->getServer()->broadcastMessage(Loader::Prefix . "§b{$args[0]} §fбыл разблокирован §d{$p->getName()}§r.");
                $bans->set('BAN', false);
                $bans->save();
                break;
            case "ban":
                if (!$p instanceof Player) {
                    if (!isset($args[1])) {
                        $p->sendMessage(Loader::Prefix . "Используйте §a/ban <никнейм игрока> <причина бана>.");
                        return true;
                    }
                    $bans = new Config($this->getDataFolder() . "database/bans/" . mb_strtolower($args[0]) . ".json", Config::JSON);
                    $reason = implode(" ", $args);
                    $reason = str_replace($args[0], '', $reason);
                    $bans->set("BAN", true);
                    $bans->save();
                    $bans->set("REASON", $reason);
                    $bans->save();
                    $bans->set("ADMIN", 'SERVER');
                    $bans->save();
                    $this->getServer()->broadcastMessage('§l§d» §r§b' . $args[0] . ' §fбыл заблокирован §dSERVER§f. Причина§7:§e' . $reason);
                    if ($this->getServer()->getPlayer($args[0]) instanceof Player) {
                        $this->getServer()->getPlayer($args[0])->close('', '§l§d» §fNfxNW§d | §r§fВы заблокированы!' . PHP_EOL . '§l§d» §r§fПричина§7:§e' . $reason . PHP_EOL . '§l§d» §r§fОт руки§7: §e' . 'SERVER');
                    }
                    return true;
                }
                if ($this->getGroup($p) !== "MOD" && $this->getGroup($p) !== "OWNER") {
                    $p->sendMessage(Loader::Prefix . "§cДанная команда доступна игрокам с привилегией §l§6Ｍｏｄｅｒａｔｏｒ§r\n" . Loader::Prefix . "Повысить свой §aранг§r можно в нашем магазине §8- §epay.sufixpvp.su");
                    return true;
                }
                if (!isset($args[1])) {
                    $p->sendMessage(Loader::Prefix . "Используйте §a/ban <никнейм игрока> <причина бана>.");
                    return true;
                }
                $bans = new Config($this->getDataFolder() . "database/bans/" . mb_strtolower($args[0]) . ".json", Config::JSON);
                $bans->set("BAN", true);
                $bans->save();
                $bans->set("REASON", $reason);
                $bans->save();
                $bans->set("ADMIN", $p->getLowerCaseName());
                $bans->save();
                $this->getServer()->broadcastMessage('§l§d» §r§b' . $this->getServer()->getPlayer($args[0])->getName() . ' §fбыл заблокирован §d' . $p->getLowerCaseName() . '§f. Причина§7:§e' . $reason);
                if ($this->getServer()->getPlayer($args[0]) instanceof Player) {
                    $this->getServer()->getPlayer($args[0])->close('', '§l§d» §l§dSufix§fPvP§r§d | §r§fYou banned!' . PHP_EOL . '§l§d» §r§fReason§7:§e' . $reason . PHP_EOL . '§l§d» §r§fFrom§7: §e' . $p->getLowerCaseName());
                }
                break;
            case 'balance':
                if (!$p instanceof Player) {
                    $p->sendMessage(Loader::Prefix . 'Используйте данную команду в игре!');
                    return false;
                }
                $p->sendMessage(Loader::Prefix . 'Ваше состояние: §l§b' . $this->getPlayerData($p, 'MONEY')['balance'] . '§r ');
                $p->sendMessage(Loader::Prefix . 'Убивай игроков и §l§eповышай§r свой баланс §l§a§r');
                $p->sendMessage(Loader::Prefix . 'Не хочешь §l§aсовершать§r убийства? Тогда §l§6посети§r наш магазин - §l§bpay.sufixpvp.su§r');
                break;
            case 'quit':
                if ($p->getLevel()->getName() === 'lobby') {
                    $p->sendMessage(Loader::Prefix . ' Ты уже находишься в §l§aлобби§r сервера.');
                    $p->sendMessage(Loader::Prefix . ' Используй данную §6§lкоманду§r на арене.');
                    return true;
                }
                $p->getInventory()->clearAll();
                $p->getInventory()->setItem(2, Item::get(388)->setCustomName("§r§aПлащи\n§7Нажмите, чтобы выбрать себе плащ."));
                $p->teleport($this->getServer()->getDefaultLevel()->getSpawnLocation());
                $p->removeAllEffects();
                $p->setGamemode(2);
                $p->setMaxHealth(20);
                $p->setHealth(20);
                $p->setFood(20);
                $p->getInventory()->setItem(4, Item::get(345)->setCustomName("§r§eВойти на арену\n§7Нажмите, чтобы открыть."));
                $p->getInventory()->setItem(6, Item::get(351, 9, 1)->setCustomName("§r§dКастомизация\n§7Нажмите, чтобы изменить свою кастомизацию."));
                $p->sendMessage(Loader::Prefix . ' Вы телепортированы в §l§aлобби§r сервера.');
                $p->sendMessage(Loader::Prefix . ' Не §l§bзадерживайся§r там...');
                break;
            case "setgroup":
                if (!$p->isOp()) {
                    $p->sendMessage(Loader::Prefix . "Данная команда доступна только §aадминистрации§r сервера.");
                    return true;
                }
                if (sizeof($args) == 0) {
                    $p->sendMessage(Loader::Prefix . "Использование - §a/setgroup §f<ник> <привилегия>");
                    return true;
                }
                if (!isset(self::FRANCHISES[$args[1]])) {
                    $p->sendMessage(Loader::Prefix . 'Данной привилегии §cне§f существует.');
                    $p->sendMessage(Loader::Prefix . 'Список §aдоступных§f групп: GUEST, GUEST+, YT, SAKURA, MOD, OWNER');
                    return true;
                }
                $this->data->query("UPDATE `database` SET `group` = '$args[1]' WHERE `nickname` = '$args[0]'");
                $this->data->query("UPDATE `database` SET `color` = '§f' WHERE `nickname` = '$args[0]'");
                $p->sendMessage(Loader::Prefix . "Игроку §l§d" . $args[0] . " §rбыла выдана привилегия §l§5" . $args[1] . "§r");
                break;
        }
        return true;
    }

    /**
     * @param PlayerJoinEvent $event
     * @return void
     */
    public function handleJoinToServer(PlayerJoinEvent $event): void
    {
        // $e->getPlayer()->getLevel()->addParticle(new \pocketmine\level\particle\FloatingTextParticle(new \pocketmine\math\Vector3(250, 19, 296), "", "§8— §l§aВаша статистика:§r\n\n Монеток: §a".Loader::getInstance()->getMoney($e->getPlayer()->getLowerCaseName())."§6\n §r§fУровень:§l §e".Loader::getInstance()->getLvL($e->getPlayer()->getLowerCaseName())."\n §r§fОпыт: §8[§l§c".Loader::getInstance()->getExp($e->getPlayer()->getLowerCaseName())."§r§8/§l§c".Loader::getInstance()->getExpNextLevel($e->getPlayer()->getLowerCaseName())."§r§8]\n §r§fУбийств: §l§c".Loader::getInstance()->getKills($e->getPlayer()->getLowerCaseName())), array($e->getPlayer()));
        $player = $event->getPlayer();
        $event->setJoinMessage(null);
        $this->addMoney($player, 3000);
        $player->sendMessage("§fДобро пожаловать на §l§dSufixPvP§r§f, §e§l{$player->getName()}§r§f!\n\n§fСообщество во §9ВКонтакте §8- §e@sufixpvp\n§aАвто-донат §8- §ehttps://pay.sufixpvp.fun/");
        $player->getInventory()->clearAll();
        $player->teleport($this->getServer()->getDefaultLevel()->getSpawnLocation());
        $player->setMaxHealth(20);
        $player->setXpLevel($this->getLvL($player));
        $player->removeAllEffects();
        $player->setGamemode(2);
        $player->setHealth(20);
        $player->setFood(20);
        $player->getInventory()->setItem(4, ClickableItemFactory::get('join_arena'));
        $player->getInventory()->setItem(2, ClickableItemFactory::get('item_cloaks'));
        //$player->getInventory()->setItem(4, Item::get(345)->setCustomName("§r§eВойти на арену\n§7Нажмите, чтобы открыть."));
        $player->getInventory()->setItem(6, Item::get(351, 9)->setCustomName("§r§dКастомизация\n§7Нажмите, чтобы изменить свою кастомизацию."));
    }

    /**
     * @param Player $player
     * @param int $time
     * @return void
     */
    public function sendTimePacket(Player $player, int $time): void
    {
        $pk = new SetTimePacket;
        $pk->time = $time;
        $player->dataPacket($pk);
    }

    /**
     * @param PlayerInteractEvent $event
     * @return bool|void
     */
    public function handleMenu(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        $event->setCancelled();
        if ($this->auth->players[$player->getLowerCaseName()] !== 'game') return false;
        if ($event->getAction() === InteractPacket::ACTION_LEAVE_VEHICLE) {
            if (($item = $event->getItem()) instanceof ClickableItem) {
                $item->handleClick($player);
            }
        }
    }
    /*
            switch ($player->getInventory()->getItemInHand()->getCustomName()) {
                case "§r§eВойти на арену\n§7Нажмите, чтобы открыть.":
                    $player->getInventory()->clearAll();
                    $player->getInventory()->setItem(2, Item::get(322)->setCustomName('§r§eFFA GAPPLE' . PHP_EOL . '§7Нажмите, чтобы войти на арену.'));
                    $player->getInventory()->setItem(4, Item::get(364, 1, 1)->setCustomName('§r§cFFA FIST' . PHP_EOL . '§7Нажмите, чтобы войти на арену.'));
                    $player->getInventory()->setItem(6, Item::get(373)->setCustomName('§r§3FFA RESISTANCE' . PHP_EOL . '§7Нажмите, чтобы войти на арену.'));
                    $player->getInventory()->setItem(7, Item::get(262)->setCustomName('§r§cВернуться§c' . PHP_EOL . '§7Нажми, чтобы вернуться'));
                    break;
                case "§r§aВозродиться §fна арене §e§lGAPPLE§r\n§7Нажмите, чтобы вернуться к жизни.":
                    $player->getInventory()->clearAll();
                    $player->teleport(new Position($this->getServer()->getLevelByName('6GAPPLE')->getSafeSpawn()->x, $this->getServer()->getLevelByName('6GAPPLE')->getSafeSpawn()->y, $this->getServer()->getLevelByName('6GAPPLE')->getSafeSpawn()->z, $this->getServer()->getLevelByName('6GAPPLE')));
                    $player->getInventory()->setHelmet(Item::get(310));
                    $player->getInventory()->setChestplate(Item::get(307));
                    $player->getInventory()->setLeggings(Item::get(312));
                    $player->getInventory()->setBoots(Item::get(313));
                    $player->getInventory()->addItem(Item::get(276));
                    $player->getInventory()->addItem(Item::get(322, 0, 8));
                    $player->setGamemode(2);
                    $player->setMaxHealth(20);
                    $player->setHealth(20);
                    $player->setFood(20);
                    $player->addTitle("§aYOU REBORN", "", 6, 10, 6);
                    break;
                case "§r§aВозродиться §fна арене §c§lFIST§r\n§7Нажмите, чтобы вернуться к жизни.":
                    $player->getInventory()->clearAll();
                    $player->teleport(new Position($this->getServer()->getLevelByName('4FIST')->getSafeSpawn()->x, $this->getServer()->getLevelByName('4FIST')->getSafeSpawn()->y, $this->getServer()->getLevelByName('4FIST')->getSafeSpawn()->z, $this->getServer()->getLevelByName('4FIST')));
                    $player->getInventory()->addItem(Item::get(364, 0, 64));
                    $player->setGamemode(2);
                    $player->setMaxHealth(20);
                    $player->setHealth(20);
                    $player->setFood(20);
                    $player->addTitle("§aYOU REBORN", "", 6, 10, 6);
                    break;
                case "§r§dКастомизация\n§7Нажмите, чтобы изменить свою кастомизацию.":
                    $player->getInventory()->clearAll();
                    $player->getInventory()->setItem(8, Item::get(262)->setCustomName("§r§cВернуться§c\n§7Нажми, чтобы вернуться"));
                    $player->getInventory()->setItem(1, Item::get(351, 15)->setCustomName("§r§6Изменить цвет никнейма\n§7Нажмите, чтобы открыть цвета."));
                    $player->getInventory()->setItem(4, Item::get(351, 12)->setCustomName("§r§bПерки\n§7Нажми, чтобы посмотреть партиклы"));
                    $player->getInventory()->setItem(7, Item::get(347)->setCustomName("§r§9Время\n§7Нажмите, чтобы изменить свое время."));
                    break;
                case "§r§bПерки\n§7Нажми, чтобы посмотреть партиклы":
                    $player->getInventory()->clearAll();
                    $player->getInventory()->setItem(1, Item::get(360)->setCustomName("§r§2Melon Particle\n§7Нажми, чтобы активировать"));
                    $player->getInventory()->setItem(2, Item::get(351, 1)->setCustomName("§r§cHeart Particle\n§7Нажми, чтобы активировать"));
                    $player->getInventory()->setItem(3, Item::get(388)->setCustomName("§r§aHappy Particle\n§7Нажми, чтобы активировать"));
                    $player->getInventory()->setItem(4, Item::get(9)->setCustomName("§r§bRain Particle\n§7Нажми, чтобы активировать"));
                    $player->getInventory()->setItem(5, Item::get(351, 14)->setCustomName("§r§6Flame Particle\n§7Нажми, чтобы активировать"));
                    $player->getInventory()->setItem(6, Item::get(175)->setCustomName("§r§dCustom Particle\n§7Нажми, чтобы активировать"));
                    $player->getInventory()->setItem(8, Item::get(262)->setCustomName("§r§cВернуться§c\n§7Нажми, чтобы вернуться"));
                    break;
                case "§r§2Melon Particle\n§7Нажми, чтобы активировать":
                    if ($this->getGroup($player) === 'GUEST') {
                        $player->sendMessage(Loader::Prefix . ' §fПартикл §l§2Melon§r доступен игрокам с привилегией §l§aＧｕｅｓｔ§6+§r.');
                        $player->sendMessage(Loader::Prefix . ' Повысить свой §l§aранг§r можно в нашем магазине §8- §l§epay.sufixpvp.su');
                        return false;
                    }
                    if ($this->getPlayerData($player, 'PARTICLE')['particle'] === 'MELON') {
                        $player->sendMessage(Loader::Prefix . ' У вас уже установлен партикл §l§2Melon§r.');
                        return false;
                    }
                    $this->setPlayerData($player, 'PARTICLE', 'MELON');
                    $player->sendMessage(Loader::Prefix . ' Партикл §l§2Melon§r успешно установлен.');
                    $player->sendMessage(Loader::Prefix . ' Хочешь §l§cбольше§r партиклов? Тогда §l§aповысь§r свой ранг сайте - §l§epay.sufixpvp.su');
                    break;
                case "§r§cHeart Particle\n§7Нажми, чтобы активировать":
                    if (!$this->getPlayerData($player, 'HEARTPARTICLE')['heart']) {
                        if (($money = $this->getMoney($player)) < 3000) {
                            $player->sendMessage(Loader::Prefix . ' Недостаточно §l§a' . (3000 - $money) . '§r  для покупки партикла §l§cHeart§r');
                            $player->sendMessage(Loader::Prefix . ' Приобрести §l§bвалюту§r можно на нашем сайте - §l§epay.sufixpvp.su');
                            return false;
                        } else {
                            $player->sendMessage(Loader::Prefix . ' Партикл §l§cHeart§r успешно куплен за §l§b3000 §r');
                            $player->sendMessage(Loader::Prefix . ' Партикл §l§cHeart§r успешно установлен.');
                            $this->setPlayerData($player, 'PARTICLE', 'HEART');
                            $this->setPlayerData($player, 'HEARTPARTICLE', true);
                            return true;
                        }
                    }
                    if ($this->getPlayerData($player, 'PARTICLE')['particle'] === 'HEART') {
                        $player->sendMessage(Loader::Prefix . ' У вас уже установлен партикл §l§cHeart§r.');
                        return false;
                    }
                    $this->setPlayerData($player, 'PARTICLE', 'HEART');
                    $player->sendMessage(Loader::Prefix . ' Партикл §l§cHeart§r успешно установлен.');
                    $player->sendMessage(Loader::Prefix . ' Хочешь §l§cбольше§r партиклов? Тогда §l§aповысь§r свой ранг сайте - §l§epay.sufixpvp.su');
                    break;
                case "§r§aHappy Particle\n§7Нажми, чтобы активировать":
                    if ($this->getGroup($player) === 'GUEST') {
                        $player->sendMessage(Loader::Prefix . ' §fПартикл §l§aHappy§r доступен игрокам с привилегией §l§aＧｕｅｓｔ§6+§r.');
                        $player->sendMessage(Loader::Prefix . ' Повысить свой §l§aранг§r можно в нашем магазине §8- §l§epay.sufixpvp.su');
                        return false;
                    }
                    if ($this->getPlayerData($player, 'PARTICLE')['particle'] === 'HAPPY') {
                        $player->sendMessage(Loader::Prefix . ' У вас уже установлен партикл §l§aHappy§r.');
                        return false;
                    }
                    $this->setPlayerData($player, 'PARTICLE', 'HAPPY');
                    $player->sendMessage(Loader::Prefix . ' Партикл §l§aHappy§r успешно установлен.');
                    $player->sendMessage(Loader::Prefix . ' Хочешь §l§cбольше§r партиклов? Тогда §l§aповысь§r свой ранг сайте - §l§epay.sufixpvp.su');
                    break;
                case "§r§bRain Particle\n§7Нажми, чтобы активировать":
                    if ($this->getGroup($player) === 'GUEST') {
                        $player->sendMessage(Loader::Prefix . ' §fПартикл §l§bRain§r доступен игрокам с привилегией §l§aＧｕｅｓｔ§6+§r.');
                        $player->sendMessage(Loader::Prefix . ' Повысить свой §l§aранг§r можно в нашем магазине §8- §l§epay.sufixpvp.su');
                        return false;
                    }
                    if ($this->getPlayerData($player, 'PARTICLE')['particle'] === 'RAIN') {
                        $player->sendMessage(Loader::Prefix . ' У вас уже установлен партикл §l§bRain§r.');
                        return false;
                    }
                    $this->setPlayerData($player, 'PARTICLE', 'RAIN');
                    $player->sendMessage(Loader::Prefix . ' Партикл §l§bRain§r успешно установлен.');
                    $player->sendMessage(Loader::Prefix . ' Хочешь §l§cбольше§r партиклов? Тогда §l§aповысь§r свой ранг сайте - §l§epay.sufixpvp.su');
                    break;
                case "§r§6Flame Particle\n§7Нажми, чтобы активировать":
                    if ($this->getGroup($player) === 'GUEST') {
                        $player->sendMessage(Loader::Prefix . ' §fПартикл §l§6Flame§r доступен игрокам с привилегией §l§aＧｕｅｓｔ§6+§r.');
                        $player->sendMessage(Loader::Prefix . ' Повысить свой §l§aранг§r можно в нашем магазине §8- §l§epay.sufixpvp.su');
                        return false;
                    }
                    if ($this->getPlayerData($player, 'PARTICLE')['particle'] === 'FLAME') {
                        $player->sendMessage(Loader::Prefix . ' У вас уже установлен партикл §l§6Flame§r.');
                        return false;
                    }
                    $this->setPlayerData($player, 'PARTICLE', 'FLAME');
                    $player->sendMessage(Loader::Prefix . ' Партикл §l§6Flame§r успешно установлен.');
                    $player->sendMessage(Loader::Prefix . ' Хочешь §l§cбольше§r партиклов? Тогда §l§aповысь§r свой ранг сайте - §l§epay.sufixpvp.su');
                    break;
                case "§r§dCustom Particle\n§7Нажми, чтобы активировать":
                    if (self::FRANCHISES[$this->getGroup($player)] < 3) {
                        $player->sendMessage(Loader::Prefix . ' §fПартикл §l§eCustom§r доступен игрокам с привилегией §l§dＳａｋｕｒａ§r.');
                        $player->sendMessage(Loader::Prefix . ' Повысить свой §l§aранг§r можно в нашем магазине §8- §l§epay.sufixpvp.su');
                        return false;
                    }
                    if (!($this->getPlayerData($player, 'CUSTOM_ITEM'))) {
                        $player->sendMessage(Loader::Prefix . ' Установите §l§6предмет§r командой - §l§b/custom');
                        return false;
                    }
                    if ($this->getPlayerData($player, 'PARTICLE')['particle'] === 'CUSTOM') {
                        $player->sendMessage(Loader::Prefix . ' У вас уже установлен партикл §l§eCustom§r.');
                        return false;
                    }
                    $this->setPlayerData($player, 'PARTICLE', 'CUSTOM');
                    $player->sendMessage(Loader::Prefix . ' Партикл §l§eCustom§r успешно установлен.');
                    $player->sendMessage(Loader::Prefix . ' Изменить §l§6предмет§r можно командой - §l§b/custom');
                    $player->sendMessage(Loader::Prefix . ' Хочешь §l§cбольше§r партиклов? Тогда §l§aповысь§r свой ранг сайте - §l§epay.sufixpvp.su');
                    break;
                case '§r§921:00':
                    $this->sendTimePacket($player, 13000);
                    $player->sendMessage(Loader::Prefix . "§eУстановленное время: §r§921:00");
                    break;
                case '§r§e12:00':
                    $this->sendTimePacket($player, 1000);
                    $player->sendMessage(Loader::Prefix . "§eУстановленное время: §r§e12:00");
                    break;
                case '§r§a9:00':
                    $this->sendTimePacket($player, 0);
                    $player->sendMessage(Loader::Prefix . "§eУстановленное время: §r§a9:00");
                    break;
                case "§r§9Время\n§7Нажмите, чтобы изменить свое время.":
                    $player->getInventory()->clearAll();
                    $player->getInventory()->setItem(2, Item::get(347)->setCustomName("§r§a9:00"));
                    $player->getInventory()->setItem(4, Item::get(347)->setCustomName("§r§e12:00"));
                    $player->getInventory()->setItem(6, Item::get(347)->setCustomName("§r§921:00"));
                    $player->getInventory()->setItem(8, Item::get(262)->setCustomName("§r§cВернуться§c\n§7Нажми, чтобы вернуться"));
                    break;
                case "§r§6Изменить цвет никнейма\n§7Нажмите, чтобы открыть цвета.":
                    $player->getInventory()->clearAll();
                    $player->getInventory()->setItem(2, Item::get(351, 12)->setCustomName("§r§bГолубой цвет\n§7Нажмите, чтобы установить."));
                    $player->getInventory()->setItem(4, Item::get(351, 1)->setCustomName("§r§cКрасный цвет\n§7Нажмите, чтобы установить."));
                    $player->getInventory()->setItem(6, Item::get(351, 10)->setCustomName("§r§aЗеленый цвет\n§7Нажмите, чтобы установить."));
                    $player->getInventory()->setItem(0, Item::get(351, 11)->setCustomName("§r§eЖелтый цвет\n§7Нажмите, чтобы установить."));
                    $player->getInventory()->setItem(8, Item::get(262)->setCustomName("§r§cВернуться§c\n§7Нажми, чтобы вернуться"));
                    break;
                case "§r§bГолубой цвет\n§7Нажмите, чтобы установить.":
                    if (!$this->getCustomizeColor($player, 'BLUE')) {
                        if ($this->getMoney($player) < 2500) {
                            $player->sendMessage(Loader::Prefix . "У вас не куплен §r§bголубой цвет §fникнейма. У вас §cнедостаточно§f монет для его покупки. Стоимость цвета: §e2500 монет.");
                            return false;
                        }
                        if ($this->getMoney($player) >= 2500) {
                            $player->sendMessage(Loader::Prefix . "§r§bГолубой цвет §fникнейма успешно приобретен за §e2500 монет§f.");
                            $this->remMoney($player, 2500);
                            $this->setPlayerData($player, 'BLUETAG', true);
                            $this->setPlayerData($player, 'CURRENTCOLOR', '§b');
                            $player->sendMessage(Loader::Prefix . "§r§bГолубой цвет §fникнейма успешно установлен, перезайдите для активации.");
                        }
                    } else {
                        $this->setPlayerData($player, 'CURRENTCOLOR', '§b');
                        $player->sendMessage(Loader::Prefix . "§r§bГолубой цвет §fникнейма успешно установлен, перезайдите для активации.");
                    }
                    break;
                case "§r§eЖелтый цвет\n§7Нажмите, чтобы установить.":
                    if (!$this->getCustomizeColor($player, 'YELLOW')) {
                        if ($this->getMoney($player) < 2500) {
                            $player->sendMessage(Loader::Prefix . "У вас не куплен §r§eжелтый цвет §fникнейма. У вас §cнедостаточно§f монет для его покупки. Стоимость цвета: §e2500 монет.");
                            return false;
                        }
                        if ($this->getMoney($player) >= 2500) {
                            $player->sendMessage(Loader::Prefix . "§r§eЖелтый цвет §fникнейма успешно приобретен за §e2500 монет§f.");
                            $this->remMoney($player, 2500);
                            $this->setPlayerData($player, 'YELLOWTAG', true);
                            $this->setPlayerData($player, 'CURRENTCOLOR', '§e');
                            $player->sendMessage(Loader::Prefix . "§r§eЖелтый цвет §fникнейма успешно установлен, перезайдите для активации.");
                        }
                    } else {
                        $this->setPlayerData($player, 'CURRENTCOLOR', '§e');
                        $player->sendMessage(Loader::Prefix . "§r§eЖелтый цвет §fникнейма успешно установлен, перезайдите для активации.");
                    }
                    break;
                case "§r§cКрасный цвет\n§7Нажмите, чтобы установить.":
                    if (!$this->getCustomizeColor($player, 'RED')) {
                        if ($this->getMoney($player) < 2500) {
                            $player->sendMessage(Loader::Prefix . "У вас не куплен §r§cкрасный цвет §fникнейма. У вас §cнедостаточно§f монет для его покупки. Стоимость цвета: §e2500 монет.");
                        }
                        if ($this->getMoney($player) >= 2500) {
                            $player->sendMessage(Loader::Prefix . "§r§cКрасный цвет §fникнейма успешно приобретен за §e2500 монет§f.");
                            $this->remMoney($player, 2500);
                            $this->setPlayerData($player, 'REDTAG', true);
                            $this->setPlayerData($player, 'CURRENTCOLOR', '§c');
                            $player->sendMessage(Loader::Prefix . "§r§cКрасный цвет §fникнейма успешно установлен, перезайдите для активации.");
                        }
                    } else {
                        $this->setPlayerData($player, 'CURRENTCOLOR', '§c');
                        $player->sendMessage(Loader::Prefix . "§r§cКрасный цвет §fникнейма успешно установлен, перезайдите для активации.");
                    }
                    break;
                case "§r§aЗеленый цвет\n§7Нажмите, чтобы установить.":
                    if (!$this->getCustomizeColor($player, 'GREEN')) {
                        if ($this->getMoney($player) < 2500) {
                            $player->sendMessage(Loader::Prefix . "У вас не куплен §r§aзеленый цвет §fникнейма. У вас §cнедостаточно§f монет для его покупки. Стоимость цвета: §e2500 монет.");
                        }
                        if ($this->getMoney($player) >= 2500) {
                            $player->sendMessage(Loader::Prefix . "§r§aЗеленый цвет §fникнейма успешно приобретен за §e2500 монет§f.");
                            $this->remMoney($player, 2500);
                            $this->setPlayerData($player, 'GREENTAG', true);
                            $this->setPlayerData($player, 'CURRENTCOLOR', '§a');
                            $player->sendMessage(Loader::Prefix . "§r§aЗеленый цвет §fникнейма успешно установлен, перезайдите для активации.");
                        }
                    } else {
                        $this->setPlayerData($player, 'CURRENTCOLOR', '§a');
                        $player->sendMessage(Loader::Prefix . "§r§aЗеленый цвет §fникнейма успешно установлен, перезайдите для активации.");
                    }
                    break;
                case "§r§cВыход\n§7Нажмите, чтобы выйти в лобби.":
                    $player->getInventory()->clearAll();
                    $player->getInventory()->setItem(2, Item::get(388)->setCustomName("§r§aПлащи\n§7Нажмите, чтобы выбрать себе плащ."));
                    $player->teleport($this->getServer()->getDefaultLevel()->getSpawnLocation());
                    $player->removeAllEffects();
                    $player->setGamemode(2);
                    $player->setMaxHealth(20);
                    $player->setHealth(20);
                    $player->setFood(20);
                    $player->getInventory()->setItem(4, Item::get(345)->setCustomName("§r§eВойти на арену\n§7Нажмите, чтобы открыть."));
                    $player->getInventory()->setItem(6, Item::get(351, 9)->setCustomName("§r§dКастомизация\n§7Нажмите, чтобы изменить свою кастомизацию."));
                    break;
                case "§r§aПлащи\n§7Нажмите, чтобы выбрать себе плащ.":
                    if ($this->getGroup($player) === "GUEST") {
                        $player->sendMessage(Loader::Prefix . " §cДанный раздел доступен игрокам с привилегией §l§aＧｕｅｓｔ§6+§r\n" . Loader::Prefix . "Повысить свой §aранг§r можно в нашем магазине §8- §epay.sufixpvp.su");
                        return true;
                    }
                    $player->getInventory()->clearAll();
                    $player->getInventory()->setItem(1, Item::get(397, 5)->setCustomName("§r§5Dragon Cloak\n§7Нажми, чтобы активировать"));
                    $player->getInventory()->setItem(2, Item::get(42)->setCustomName("§r§3Golem Cloak\n§7Нажми, чтобы активировать"));
                    $player->getInventory()->setItem(3, Item::get(33)->setCustomName("§r§2Piston Cloak\n§7Нажми, чтобы активировать"));
                    $player->getInventory()->setItem(4, Item::get(285)->setCustomName("§r§9Pick Cloak\n§7Нажми, чтобы активировать"));
                    $player->getInventory()->setItem(5, Item::get(397, 4)->setCustomName("§r§cCrieper Cloak\n§7Нажми, чтобы активировать"));
                    $player->getInventory()->setItem(7, Item::get(262)->setCustomName("§r§cВернуться§c\n§7Нажми, чтобы вернуться"));
                    break;
                case "§r§5Dragon Cloak\n§7Нажми, чтобы активировать":
                    $player->sendMessage("§l§d» §rВы успешно установили себе плащ §l§5Dragon Cloak§r");
                    $this->setCloak($player, 'Minecon_MineconSteveCape2016');
                    break;
                case "§r§3Golem Cloak\n§7Нажми, чтобы активировать":
                    $player->sendMessage("§l§d» §rВы успешно установили себе плащ §l§3Golem Cloak§r");
                    $this->setCloak($player, 'Minecon_MineconSteveCape2015');
                    break;
                case "§r§2Piston Cloak\n§7Нажми, чтобы активировать":
                    $player->sendMessage("§l§d» §rВы успешно установили себе плащ §l§2Piston Cloak§r");
                    $this->setCloak($player, 'Minecon_MineconSteveCape2013');
                    break;
                case "§r§9Pick Cloak\n§7Нажми, чтобы активировать":
                    $player->sendMessage("§l§d» §rВы успешно установили себе плащ §l§9Pick Cloak§r");
                    $this->setCloak($player, 'Minecon_MineconSteveCape2012');
                    break;
                case "§r§cCrieper Cloak\n§7Нажми, чтобы активировать":
                    $player->sendMessage("§l§d» §rВы успешно установили себе плащ §l§cCrieper Cloak§r");
                    $this->setCloak($player, 'Minecon_MineconSteveCape2011');
                    break;
                case "§r§cВернуться§c\n§7Нажми, чтобы вернуться":
                    $player->getInventory()->clearAll();
                    $player->getInventory()->setItem(4, Item::get(345)->setCustomName("§r§eВойти на арену\n§7Нажмите, чтобы открыть."));
                    $player->getInventory()->setItem(2, Item::get(388)->setCustomName("§r§aПлащи\n§7Нажмите, чтобы выбрать себе плащ."));
                    $player->getInventory()->setItem(6, Item::get(351, 9)->setCustomName("§r§dКастомизация\n§7Нажмите, чтобы изменить свою кастомизацию."));
                    break;
                case "§r§3FFA RESISTANCE\n§7Нажмите, чтобы войти на арену.":
                    $player->getInventory()->clearAll();
                    $player->teleport(new Position($this->getServer()->getLevelByName('aCOMBO')->getSafeSpawn()->x, $this->getServer()->getLevelByName('aCOMBO')->getSafeSpawn()->y, $this->getServer()->getLevelByName('aCOMBO')->getSafeSpawn()->z, $this->getServer()->getLevelByName('aCOMBO')));
                    $player->setMaxHealth(20);
                    $player->setHealth(20);
                    $player->setFood(20);
                    foreach ($this->getServer()->getLevelByName('aCOMBO')->getPlayers() as $p) {
                        $p->sendMessage(Loader::Prefix . ' §fИгрок §l' . $player->getName() . '§f§r присоединился к арене §l§3FFA-RESISTANCE§r');
                        $p->sendMessage(Loader::Prefix . ' §fИгроков на арене: §l§c' . sizeof($this->getServer()->getLevelByName('aCOMBO')->getPlayers()) . '§r');
                    }
                    $player->sendMessage(Loader::Prefix . " Чтобы выйти с §l§aарены§r используйте команду §e/quit§r");
                    break;
                case "§r§cFFA FIST\n§7Нажмите, чтобы войти на арену.":
                    $player->getInventory()->clearAll();
                    $player->teleport(new Position($this->getServer()->getLevelByName('4FIST')->getSafeSpawn()->x, $this->getServer()->getLevelByName('4FIST')->getSafeSpawn()->y, $this->getServer()->getLevelByName('4FIST')->getSafeSpawn()->z, $this->getServer()->getLevelByName('4FIST')));
                    $player->getInventory()->addItem(Item::get(364, 0, 64));
                    $player->setMaxHealth(20);
                    $player->setHealth(20);
                    $player->setFood(20);
                    foreach ($this->getServer()->getLevelByName('4FIST')->getPlayers() as $p) {
                        $p->sendMessage(Loader::Prefix . ' §fИгрок §l' . $player->getName() . '§f§r присоединился к арене §l§cFFA-FIST§r.');
                        $p->sendMessage(Loader::Prefix . ' §fИгроков на арене: §l§c' . sizeof($this->getServer()->getLevelByName('4FIST')->getPlayers()) . '§r');
                    }
                    $player->sendMessage(Loader::Prefix . " Чтобы выйти с §l§aарены§r используйте команду §e/quit§r");
                    break;
                case "§r§eFFA GAPPLE\n§7Нажмите, чтобы войти на арену.":
                    $player->getInventory()->clearAll();
                    $player->teleport(new Position($this->getServer()->getLevelByName('6GAPPLE')->getSafeSpawn()->x, $this->getServer()->getLevelByName('6GAPPLE')->getSafeSpawn()->y, $this->getServer()->getLevelByName('6GAPPLE')->getSafeSpawn()->z, $this->getServer()->getLevelByName('6GAPPLE')));
                    $player->getInventory()->setHelmet(Item::get(310));
                    $player->getInventory()->setChestplate(Item::get(307));
                    $player->getInventory()->setLeggings(Item::get(312));
                    $player->getInventory()->setBoots(Item::get(313));
                    $player->getInventory()->addItem(Item::get(276));
                    $player->getInventory()->addItem(Item::get(322, 0, 8));
                    $player->setMaxHealth(20);
                    $player->setHealth(20);
                    $player->setFood(20);
                    foreach ($this->getServer()->getLevelByName("6GAPPLE")->getPlayers() as $p) {
                        $p->sendMessage(Loader::Prefix . ' §fИгрок §l' . $player->getName() . '§f§r присоединился к арене §l§eFFA-GAPPLE§r.');
                        $p->sendMessage(Loader::Prefix . ' §fИгроков на арене: §l§c' . sizeof($this->getServer()->getLevelByName("6GAPPLE")->getPlayers()) . '§r');
                    }
                    $player->sendMessage(Loader::Prefix . " Чтобы выйти с §l§aарены§r используйте команду §e/quit§r");
                    break;
            }
        }
    }
*/
    public function getGroup(Player $player): string
    {
        return $this->getPlayerData($player, 'GROUP')['group'];
    }

    public function setGroup(Player $player, string $group): void
    {
        $this->setPlayerData($player, 'GROUP', $group);
    }

    public function setTime(Player $player): void
    {
        $this->players[$player->getName()] = time();
    }

    public function handleSteal(EntityDamageEvent $event): void
    {
        if ($event instanceof EntityDamageByEntityEvent) {
            if ($event->getDamager() instanceof Player && $event->getEntity() instanceof Player) {
                $this->setTime($event->getDamager());
                $this->setTime($event->getEntity());
            }
        }
    }

    public static function sendBoss(Player $player, string $title): void
    {
        $pk = new BossEventPacket;
        $pk->bossEid = 999888777;
        $pk->eventType = BossEventPacket::TYPE_SHOW;
        $pk->healthPercent = 1.0;
        $pk->title = $title;
        $pk->unknownShort = 1;
        $pk->color = 5;
        $pk->overlay = 1;
        $player->dataPacket($pk);
    }

    public static function setBossTitle(Player $player, string $text): void
    {
        $pk = new SetEntityDataPacket;
        $pk->entityRuntimeId = 999888777;
        $pk->metadata = [
            Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $text]
        ];
        $player->dataPacket($pk);
    }

    public function getFactor(Player $player): int
    {
        return $this->getPlayerData($player, 'FACTOR')['factor'];
    }

    public function getFactorString(Player $player): string
    {
        $factors = ['Unknown', 'Отсутсвует', 'X2', 'X3'];
        return $factors[$this->getFactor($player)];
    }

    public function getExp(Player $player): int
    {
        return $this->getPlayerData($player, 'EXPIRIENCE')['exp'];
    }

    public function addExp(Player $player, int $count): void
    {
        $exp = $this->getPlayerData($player, 'EXPIRIENCE')['exp'];
        $this->setPlayerData($player, 'EXPIRIENCE', $exp + $count);
    }

    public function addKill(Player $player): void
    {
        $kills = $this->getPlayerData($player, 'KILLS')['kills'];
        $this->setPlayerData($player, 'KILLS', $kills + 1);
    }

    public function handleCommand(PlayerCommandPreprocessEvent $event): void
    {
        if (isset($this->players[$event->getPlayer()->getName()])) {
            if (str_contains($event->getMessage(), '/quit')) {
                $event->getPlayer()->sendMessage(Loader::Prefix . "§fВы находитесь в режиме поединка, команда будет доступна в течении §l§aдесяти§r секунд.");
                $event->setCancelled(true);
            }
        }
    }

    public function getFFAMode(Player $player): string
    {
        return match ($player->getLevel()->getFolderName()) {
            '6GAPPLE' => 'gapple',
            '4FIST' => 'fist',
            'aCOMBO' => 'resistance',
            default => 'underfined',
        };
    }

    public function addPointInFFA(Player $entity, mixed $damager)
    {
        $entity->setGamemode(3);
        $entity->getLevel()->addParticle(new \pocketmine\level\particle\DestroyBlockParticle($entity->getPosition(), Block::get(152, 0)));
        $entity->addTitle("§cYOU DEAD!");
        $entity->getInventory()->clearAll();
        $rand = mt_rand(5, 20);
        $rand2 = mt_rand(1, 15);
        switch ($this->getFFAMode($entity)) {
            case 'gapple':
                $entity->getInventory()->setItem(2, Item::get(388)->setCustomName("§r§aВозродиться §fна арене §e§lGAPPLE§r\n§7Нажмите, чтобы вернуться к жизни."));
                $entity->getInventory()->setItem(6, Item::get(355)->setCustomName("§r§cВыход\n§7Нажмите, чтобы выйти в лобби."));
                break;
            case 'fist':
                $entity->getInventory()->setItem(2, Item::get(388)->setCustomName("§r§aВозродиться §fна арене §c§lFIST§r\n§7Нажмите, чтобы вернуться к жизни."));
                $entity->getInventory()->setItem(6, Item::get(355)->setCustomName("§r§cВыход\n§7Нажмите, чтобы выйти в лобби."));
                break;
            case 'sumo':
                $entity->getInventory()->setItem(2, Item::get(388)->setCustomName("§r§aВозродиться §fна арене §c§bSUMO§r\n§7Нажмите, чтобы вернуться к жизни."));
                $entity->getInventory()->setItem(6, Item::get(355)->setCustomName("§r§cВыход\n§7Нажмите, чтобы выйти в лобби."));
                break;
        }
        unset($this->players[$entity->getName()]);
        if (!$damager) return false;
        $factor = $this->getFactor($damager);
        unset($this->players[$damager->getName()]);
        $damager->addTitle('§7KILL', '§c' . $entity->getName());
        $this->addKill($damager);
        $damager->sendMessage(' §a+' . $rand * $factor . ' опыта! (Множитель: §l§b' . $this->getFactorString($damager) . '§r§a)');
        $damager->sendMessage(' §e+' . $rand2 * $factor . ' монет! (Множитель: §l§b' . $this->getFactorString($damager) . '§r§e)');
        $damager->setHealth(20);
        $damager->setFood(20);
        $this->addExp($damager, $rand * $factor);
        $this->addMoney($damager, $rand2 * $factor);
        switch ($this->getLvL($damager)) {
            case 1:
                if ($this->getExp($damager) > 100) {
                    $damager->addTitle("§l§92", "§9уровень");
                    $damager->sendMessage(Loader::Prefix . "Вы перешли на §l§e2§r§f уровень");
                    $this->setLvL($damager, 2);
                }
                break;
            case 2:
                if ($this->getExp($damager) > 500) {
                    $damager->addTitle("§l§93", "§9уровень");
                    $damager->sendMessage(Loader::Prefix . "Вы перешли на §e§l3§r§f уровень");
                    $this->setLvL($damager, 3);
                }
                break;
            case 3:
                if ($this->getExp($damager) > 1500) {
                    $damager->addTitle("§l§94", "§9уровень");
                    $damager->sendMessage(Loader::Prefix . "Вы перешли на §e§l4§r§f уровень");
                    $this->setLvL($damager, 4);
                }
                break;
            case 4:
                if ($this->getExp($damager) > 3000) {
                    $damager->addTitle("§l§95", "§9уровень");
                    $damager->sendMessage(Loader::Prefix . "Вы перешли на §e§l5§r§f уровень");
                    $this->setLvL($damager, 5);
                }
                break;
            case 5:
                if ($this->getExp($damager) > 5000) {
                    $damager->addTitle("§l§96", "§9уровень");
                    $damager->sendMessage(Loader::Prefix . "Вы перешли на §e§l6§r§f уровень");
                    $this->setLvL($damager, 6);
                }
                break;
            case 6:
                if ($this->getExp($damager) > 7000) {
                    $damager->addTitle("§l§97", "§9уровень");
                    $damager->sendMessage(Loader::Prefix . "Вы перешли на §e§l7§r§f уровень");
                    $this->setLvL($damager, 7);
                }
                break;
            case 7:
                if ($this->getExp($damager) > 10000) {
                    $damager->addTitle("§l§98", "§9уровень");
                    $damager->sendMessage(Loader::Prefix . "Вы перешли на §e§l8§r§f уровень");
                    $this->setLvL($damager, 8);
                }
                break;
            case 8:
                if ($this->getExp($damager) > 15000) {
                    $damager->addTitle("§l§99", "§9уровень");
                    $damager->sendMessage(Loader::Prefix . "Вы перешли на §e§l9§r§f уровень");
                    $this->setLvL($damager, 9);
                }
                break;
            case 9:
                if ($this->getExp($damager) > 20000) {
                    $damager->addTitle("§l§910", "§9уровень");
                    $damager->sendMessage(Loader::Prefix . "Вы перешли на §e§l10§r§f уровень");
                    $this->setLvL($damager, 10);
                }
                break;
            case 10:
                if ($this->getExp($damager) > 30000) {
                    $damager->addTitle("§l§911", "§9уровень");
                    $damager->sendMessage(Loader::Prefix . "Вы перешли на §e§l11§r§f уровень");
                    $this->setLvL($damager, 11);
                }
                break;
        }
    }

    public function getLastDamager(Player $player) : mixed
    {
        if (isset($this->lastDamage[$player->getLowerCaseName()])) {
            $data = $this->lastDamage[$player->getLowerCaseName()];
            if ((microtime(true) - $data['time']) < 10) {
                return $data['damager'];
            }
        }
        return false;
    }

    public function handleDeathPlayerEvent(EntityDamageEvent $event): bool
    {
        if ($event->getCause() === EntityDamageEvent::CAUSE_VOID && $event->getEntity() instanceof SufixPlayer && !$event->getEntity()->isSpectator()) {
            $event->setCancelled();
            $this->addPointInFFA($event->getEntity(), $this->getLastDamager($event->getEntity()));
            return false;
        }
        if ($event instanceof EntityDamageByEntityEvent) {
            if ($event->getDamager()->getLevel()->getFolderName() !== 'lobby' && $event->getDamager() instanceof Player) {
                $damager = $event->getDamager();
                $entity = $event->getEntity();
                if ($this->getFFAMode($damager) === 'underfined') return true;
                if ($this->getFFAMode($damager) === 'resistance') $event->setDamage(0);
                if (($entity->getHealth() - $event->getFinalDamage()) <= 2 && ($entity->isAdventure() or $entity->isSurvival())) {
                    $event->setCancelled();
                    $this->addPointInFFA($entity, $damager);
                }
            } else {
                $event->setCancelled();
            }
        }
        return false;
    }

    public function hadnleRespawn(PlayerRespawnEvent $event): void
    {
        $player = $event->getPlayer();
        $player->getInventory()->clearAll();
        $player->teleport($this->getServer()->getDefaultLevel()->getSpawnLocation());
        $player->setMaxHealth(20);
        $player->removeAllEffects();
        $player->setGamemode(2);
        $player->setHealth(20);
        $player->setFood(20);
        $player->getInventory()->setItem(2, Item::get(388)->setCustomName("§r§aПлащи\n§7Нажмите, чтобы выбрать себе плащ."));
        $player->getInventory()->setItem(4, Item::get(345)->setCustomName("§r§eВойти на арену\n§7Нажмите, чтобы открыть."));
        $player->getInventory()->setItem(6, Item::get(351, 9, 1)->setCustomName("§r§dКастомизация\n§7Нажмите, чтобы изменить свою кастомизацию."));
    }

    public function handleDropPlayer(PlayerDropItemEvent $event): void
    {
        $event->setCancelled(true);
    }

    public function getPing(Player $player): string
    {
        $ping = $player->getPing();
        return match (true) {
            $ping < 100 => '§a' . $ping . '§r',
            $ping >= 100 && $ping < 250 => '§e' . $ping . '§r',
            $ping >= 250 => '§c' . $ping . '§r',
        };
    }

    public static function sendHealthAttribute(Player $player, float $progress): void
    {
        $pk = new AddEntityPacket;
        $pk->entityRuntimeId = 999888777;
        $pk->type = Zombie::NETWORK_ID;
        $pk->position = $player->asVector3();
        $pk->x = $player->asVector3()->x;
        $pk->y = $player->asVector3()->y;
        $pk->z = $player->asVector3()->z;
        $pk->motion = new Vector3;
        $pk->metadata = [
            Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0.0],
            Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0],
            Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0]
        ];
        $player->dataPacket($pk);
        $pk = new UpdateAttributesPacket;
        $pk->entityRuntimeId = 999888777;
        $pk->entries = [
            Attribute::getAttribute(Attribute::HEALTH)->setMaxValue(101)->setValue($progress)
        ];
        $player->dataPacket($pk);
    }

    public function onMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        $pk = new MoveEntityPacket;
        $pk->entityRuntimeId = 999888777;
        $pk->position = new Vector3($player->x, $player->y + 128, $player->z);
        $pk->x = $player->asVector3()->x;
        $pk->y = $player->asVector3()->y + 128;
        $pk->z = $player->asVector3()->z;
        $pk->yaw = $pk->headYaw = $pk->pitch = 0.0;
        $player->dataPacket($pk);
    }

    public function handleQuitPlayer(PlayerQuitEvent $event): void
    {
        $event->setQuitMessage(null);
        $this->unEquipWings($event->getPlayer());
    }

    public function parseWings(Vector3 $pos, mixed $character): Particle
    {
        return match ($character) {
            'x' => new RedstoneParticle($pos),
            1 => new DustParticle($pos, 3, 0, 132),
            2 => new DustParticle($pos, 0, 102, 0),
            4 => new DustParticle($pos, 179, 0, 0),
            'f' => new Particles(Particle::TYPE_FLAME, $pos),
        };
    }

    public function equipWings(Player $player, string $wings): bool
    {
        $shape = $this->getWings()[$wings]['shape'];
        $nickname = $player->getLowerCaseName();
        $wingstask = new WingsTask($player, $shape);
        if (!isset($this->equip_players[$nickname])) {
            $this->getServer()->getScheduler()->scheduleRepeatingTask($wingstask, 10);
            $this->equip_players[$nickname]['id'] = $wingstask->getTaskId();
            $this->equip_players[$nickname]['name'] = $wings;
            return false;
        }
        if ($this->equip_players[$nickname]['name'] === $wings) {
            $this->unEquipWings($player);
            return false;
        } else {
            $this->unEquipWings($player);
            $this->getServer()->getServer()->getScheduler()->scheduleRepeatingTask($wingstask, 10);
            $this->equip_players[$nickname]['id'] = $wingstask->getTaskId();
            $this->equip_players[$nickname]['name'] = $wings;
        }
        return false;
    }

    public function unEquipWings(Player $player): void
    {
        $nickname = $player->getLowerCaseName();
        if (isset($this->equip_players[$nickname])) {
            $this->getServer()->getScheduler()->cancelTask($this->equip_players[$nickname]['id']);
            unset($this->equip_players[$nickname]);
        }
    }

    public function onExhaust(PlayerExhaustEvent $event): void
    {
        $event->getPlayer()->setFood(20);
        $event->setCancelled();
    }

    public function handleFall(PlayerMoveEvent $event): void
    {
        if ($event->getPlayer()->getFloorY() < 0 && $event->getPlayer()->getLevel()->getName() === 'lobby') {
            $event->getPlayer()->teleport($event->getPlayer()->getLevel()->getSpawnLocation());
        }
    }

    public function eatGappleJoin(PlayerItemConsumeEvent $event): void
    {
        if ($event->getItem()->getCustomName() === "§r§eFFA GAPPLE\n§7Нажмите, чтобы войти на арену." || $event->getItem()->getCustomName() === "§r§3FFA RESISTANCE\n§7Нажмите, чтобы войти на арену.") $event->setCancelled();
    }

    /**
     * @return Loader
     */
    public static function getInstance(): Loader{
        return self::$instance;
    }
}
