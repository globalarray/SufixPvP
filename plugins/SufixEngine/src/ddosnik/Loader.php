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

use pocketmine\{
    Player,
    GameMode
};
use pocketmine\block\Block;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
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
use pocketmine\network\mcpe\protocol\{
    InteractPacket,
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
use ddosnik\sw\SkyWarsTrait;

use SQLite3;

class Loader extends PluginBase implements Listener {
    use SkyWarsTrait;

    private const ARMOR_ENCHANTMENTS = [0, 1, 4, 5];
    private const WEAPONS_ENCHANTMENTS = [9, 13, 12, 17];
    private const BOW_ENCHANTMENTS = [19, 20, 21, 22];

    public const Prefix = '§l§d» §r';
    public const MESSAGES = ['sufixpvp.broadcast.site', 'sufixpvp.broadcast.emoji', 'sufixpvp.broadcast.thanks', 'sufixpvp.broadcast.duels', 'sufixpvp.broadcast.follow_our'];
    public const FRANCHISES = ['GUEST' => 0, 'GUEST+' => 1, 'YT' => 2, 'SAKURA' => 3, 'MOD' => 4, 'OWNER' => 5];
    public const FFA_WORLDS = ['6GAPPLE' => 'gapple', '4FIST' => 'fist', 'aCOMBO' => 'resistance'];
    public const CUSTOM_WINGS = [
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
        $this->setPlayerData($player, 'MONEY', $money - $value);
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
        $event->setFormat($player->getSufixNameTag() . '§7: '. self::removeColors($event->getMessage()));
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
                $p->teleport($this->getServer()->getDefaultLevel()->getSpawnLocation());
                $p->removeBossBar();
                $p->removeAllEffects();
                $p->setGamemode(GameMode::ADVENTURE());
                $p->setMaxHealth(20);
                $p->setHealth(20);
                $p->setFood(20);
                $p->getInventory()->setItem(2, ClickableItemFactory::CLOAKS());
                $p->getInventory()->setItem(4, ClickableItemFactory::JOIN_ARENA());
                $p->getInventory()->setItem(6, ClickableItemFactory::CUSTOMIZATION());
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
    public function handlePlayerJoin(PlayerJoinEvent $event): void{
        $player = $event->getPlayer();
        $event->setJoinMessage(null);
        $player->sendMessage("§fДобро пожаловать на §l§dSufixPvP§r§f, §e§l{$player->getName()}§r§f!\n\n§fСообщество во §9ВКонтакте §8- §e@sufixpvp\n§aАвто-донат §8- §ehttps://pay.sufixpvp.fun/");
        $player->getInventory()->clearAll();
        $player->teleport($this->getServer()->getDefaultLevel()->getSpawnLocation());
        $player->setMaxHealth(20);
        $player->setXpLevel($player->getLvl());
        $player->removeAllEffects();
        $player->setGamemode(GameMode::ADVENTURE());
        $player->setHealth(20);
        $player->setFood(20);
        $this->addMoney($player, 10000);
        $player->updateNameTag();
        $player->updateDisplayName();
        $player->getInventory()->setItem(4, ClickableItemFactory::JOIN_ARENA());
        $player->getInventory()->setItem(2, ClickableItemFactory::CLOAKS());
        $player->getInventory()->setItem(6, ClickableItemFactory::CUSTOMIZATION());
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function handleMenu(PlayerInteractEvent $event) : void{
        $player = $event->getPlayer();
        if ($this->auth->players[$player->getLowerCaseName()] !== 'game') return;
        if ($event->getAction() === InteractPacket::ACTION_LEAVE_VEHICLE) {
            if (($item = $event->getItem()) instanceof ClickableItem) {
                $event->setCancelled();
                $item->handleClick($player);
            }
        }
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

    public function handleCommand(PlayerCommandPreprocessEvent $event): void
    {
        if (isset($this->players[$event->getPlayer()->getName()])) {
            if (str_contains($event->getMessage(), '/quit')) {
                $event->getPlayer()->sendMessage(Loader::Prefix . "§fВы находитесь в режиме поединка, команда будет доступна в течении §l§aдесяти§r секунд.");
                $event->setCancelled(true);
            }
        }
    }

    public function addPointInFFA(Player $entity, mixed $damager) : void{
        $entity->setGamemode(GameMode::SPECTATOR());
        $entity->getLevel()->addParticle(new DestroyBlockParticle($entity->getPosition(), Block::get(152, 0)));
        $entity->addTitle('§cYOU DEAD!');
        $entity->getInventory()->clearAll();
        $rand = mt_rand(5, 20);
        $rand2 = mt_rand(1, 15);
        $entity->getInventory()->setItem(2, ClickableItemFactory::REBORN());
        $entity->getInventory()->setItem(6, ClickableItemFactory::QUIT_LOBBY());
        unset($this->players[$entity->getName()]);
        if (!$damager) return;
        $factor = $damager->getFactor();
        unset($this->players[$damager->getName()]);
        $damager->addTitle('§7KILL', '§c' . $entity->getName());
        $damager->sendMessage(' §a+' . $rand * $factor . ' опыта! (Множитель: §l§b' . $this->getFactorString($damager) . '§r§a)');
        $damager->sendMessage(' §e+' . $rand2 * $factor . ' монет! (Множитель: §l§b' . $this->getFactorString($damager) . '§r§e)');
        $damager->addKill();
        $damager->setHealth(20);
        $damager->setFood(20);
        $damager->addExp($rand * $factor);
        $this->addMoney($damager, $rand2 * $factor);
        switch ($damager->getLvL()) {
            case 1:
                if ($damager->getExperience() > 100) {
                    $damager->addTitle("§l§92", "§9уровень");
                    $damager->sendMessage(Loader::Prefix . "Вы перешли на §l§e2§r§f уровень");
                    $damager->setLvl(2);
                }
                break;
            case 2:
                if ($damager->getExperience() > 500) {
                    $damager->addTitle("§l§93", "§9уровень");
                    $damager->sendMessage(Loader::Prefix . "Вы перешли на §e§l3§r§f уровень");
                    $damager->setLvl(3);
                }
                break;
            case 3:
                if ($damager->getExperience() > 1500) {
                    $damager->addTitle("§l§94", "§9уровень");
                    $damager->sendMessage(Loader::Prefix . "Вы перешли на §e§l4§r§f уровень");
                    $damager->setLvL(4);
                }
                break;
            case 4:
                if ($damager->getExperience() > 3000) {
                    $damager->addTitle("§l§95", "§9уровень");
                    $damager->sendMessage(Loader::Prefix . "Вы перешли на §e§l5§r§f уровень");
                    $damager->setLvl(5);
                }
                break;
            case 5:
                if ($damager->getExperience() > 5000) {
                    $damager->addTitle("§l§96", "§9уровень");
                    $damager->sendMessage(Loader::Prefix . "Вы перешли на §e§l6§r§f уровень");
                    $damager->setLvl(6);
                }
                break;
            case 6:
                if ($damager->getExperience() > 7000) {
                    $damager->addTitle("§l§97", "§9уровень");
                    $damager->sendMessage(Loader::Prefix . "Вы перешли на §e§l7§r§f уровень");
                    $damager->setLvl(7);
                }
                break;
            case 7:
                if ($damager->getExperience() > 10000) {
                    $damager->addTitle("§l§98", "§9уровень");
                    $damager->sendMessage(Loader::Prefix . "Вы перешли на §e§l8§r§f уровень");
                    $damager->setLvl(8);
                }
                break;
            case 8:
                if ($damager->getExperience() > 15000) {
                    $damager->addTitle("§l§99", "§9уровень");
                    $damager->sendMessage(Loader::Prefix . "Вы перешли на §e§l9§r§f уровень");
                    $damager->setLvL(9);
                }
                break;
            case 9:
                if ($damager->getExperience() > 20000) {
                    $damager->addTitle("§l§910", "§9уровень");
                    $damager->sendMessage(Loader::Prefix . "Вы перешли на §e§l10§r§f уровень");
                    $damager->setLvl(10);
                }
                break;
            case 10:
                if ($damager->getExperience() > 30000) {
                    $damager->addTitle("§l§911", "§9уровень");
                    $damager->sendMessage(Loader::Prefix . "Вы перешли на §e§l11§r§f уровень");
                    $damager->setLvl(11);
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

    public function handleDeathPlayerEvent(EntityDamageEvent $event) : void{
        if ($event->getCause() === EntityDamageEvent::CAUSE_VOID && $event->getEntity() instanceof SufixPlayer && !$event->getEntity()->isSpectator()) {
            $event->setCancelled();
            $this->addPointInFFA($event->getEntity(), $this->getLastDamager($event->getEntity()));
            return;
        }
        if ($event instanceof EntityDamageByEntityEvent) {
            if ($event->getDamager()->getLevel()->getFolderName() !== 'lobby' && $event->getDamager() instanceof Player) {
                $damager = $event->getDamager();
                $entity = $event->getEntity();
                if ($damager->getFFAMode() === 'underfined') return;
                if ($damager->getFFAMode() === 'resistance') $event->setDamage(0);
                if (($entity->getHealth() - $event->getFinalDamage()) <= 2 && ($entity->isAdventure() or $entity->isSurvival())) {
                    $event->setCancelled();
                    $this->addPointInFFA($entity, $damager);
                }
            } else {
                $event->setCancelled();
            }
        }
    }

    public function hadnleRespawn(PlayerRespawnEvent $event): void
    {
        $player = $event->getPlayer();
        $player->getInventory()->clearAll();
        $player->teleport($this->getServer()->getDefaultLevel()->getSpawnLocation());
        $player->setMaxHealth(20);
        $player->removeAllEffects();
        $player->setGamemode(GameMode::ADVENTURE());
        $player->setHealth(20);
        $player->setFood(20);
        $player->getInventory()->setItem(2, ClickableItemFactory::CLOAKS());
        $player->getInventory()->setItem(4, ClickableItemFactory::JOIN_ARENA());
        $player->getInventory()->setItem(6, ClickableItemFactory::CUSTOMIZATION());
    }

    public function handleDropPlayer(PlayerDropItemEvent $event): void{
        if ($event->getItem() instanceof ClickableItem)
            $event->setCancelled();
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
        $event->getPlayer()->unEquipWings();
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

    public function onExhaust(PlayerExhaustEvent $event): void{
        $event->setCancelled();
    }

    public function handleFall(PlayerMoveEvent $event): void{
        if (($player = $event->getPlayer())->getFloorY() < 0 && $player->getLevel()->getName() === 'lobby') {
            $player->teleport($player->getLevel()->getSpawnLocation());
        }
    }

    public function handleConsume(PlayerItemConsumeEvent $event): void{
        if ($event->getItem() instanceof ClickableItem) $event->setCancelled();
    }

    /**
     * @return Loader
     */
    public static function getInstance(): Loader{
        return self::$instance;
    }
}