<?php

/**
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

declare(strict_types=1);

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
use ddosnik\handler\EventHandler;
use ddosnik\task\{Hotbar, LeaveTask, Broadcaster, ParticlesManager};
use pocketmine\math\Vector3;
use pocketmine\entity\{Entity, Attribute, Zombie};
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\network\mcpe\protocol\{AddEntityPacket, BossEventPacket, SetEntityDataPacket, UpdateAttributesPacket};
use pocketmine\level\particle\{DustParticle, RedstoneParticle, Particle, DestroyBlockParticle};
use ddosnik\wings\task\WingsTask;
use ddosnik\particles\TextParticle;
use ddosnik\task\ParticleUpdate;
use ddosnik\commands\{
    KickCommand,
    PosCommand
};
use ddosnik\player\SufixPlayer;
use ddosnik\menu\{
    ClickableItemFactory,
    ClickableItem
};
use ddosnik\sw\SkyWarsTrait;
use ddosnik\particles\Particles;

use SQLite3;

class Loader extends PluginBase implements Listener {
    use SkyWarsTrait;

    private const ARMOR_ENCHANTMENTS = [0, 1, 4, 5];
    private const WEAPONS_ENCHANTMENTS = [9, 13, 12, 17];
    private const BOW_ENCHANTMENTS = [19, 20, 21, 22];

    public const Prefix = '§l§d» §r';
    public const DUELS_MODES = [
        'sumo' => 6,
        'tntrun' => 7,
        'skywars' => 8,
        'bow' => 9,
        'spleef' => 10
    ];
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
    public array $particles, $players = [];
    public array $ffaworlds = [
        'aCOMBO' => 0,
        '6GAPPLE' => 1,
        '4FIST' => 2
    ];
    /** Wings */
    public array $equip_players = [];
    /** @var array */
    private array $lastDamage = [];

    public function onEnable() : void{
        $floating_texts = [
            [1, new Vector3(11.3, 41.64, 242.5807), '§l§d» §r§fDuels§7: §cSumo'],
            [2, new Vector3(9.4826, 41.64, 237.5413), '§l§d» §r§fDuels§7: §cTNT§fRun §7[§aNEW§7]'],
            [3, new Vector3(4.4455, 41.64, 234.604), '§l§d» §r§fDuels§7: §bSkyWars'],
            [4, new Vector3(9.43, 41.64, 247.4384), '§l§d» §r§fDuels§7: §eBow'],
            [5, new Vector3(4.5124, 41.64, 250.3339), '§l§d» §r§fDuels§7: §1Spleef'],
            [6, new Vector3(11.3, 41.32, 242.5807), '§fИгроков§7: §c0'],
            [7, new Vector3(9.4826, 41.32, 237.5413), '§fИгроков§7: §c0'],
            [8, new Vector3(4.4455, 41.32, 234.604), '§fИгроков§7: §c0'],
            [9, new Vector3(9.43, 41.32, 247.4384), '§fИгроков§7: §c0'],
            [10, new Vector3(4.5124, 41.32, 250.3339), '§fИгроков§7: §c0'],
            [11, new Vector3(5.9639, 39.9, 260.6636), 'sufixpvp.floatingtext.statistics'],
            [12, new Vector3(5.9639, 39.7, 260.6636), 'sufixpvp.floatingtext.nickname'],
            [13, new Vector3(5.9639, 39.3, 260.6636), 'sufixpvp.floatingtext.rank'],
            [14, new Vector3(5.9639, 39.5, 260.6636), 'sufixpvp.floatingtext.wins'],
            [15, new Vector3(5.9639, 39.1, 260.6636), 'sufixpvp.floatingtext.kills']
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
            new KickCommand($this),
            new PosCommand($this)
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

    public function setTime(Player $player): void
    {
        $this->players[$player->getName()] = time();
    }

    public static function sendBoss(Player $player, string $title): void
    {
        $pk = new BossEventPacket;
        $pk->bossEid = $player->getClientId();
        $pk->eventType = BossEventPacket::TYPE_SHOW;
        $pk->healthPercent = 4.0;
        $pk->title = $title;
        $pk->unknownShort = 2;
        $pk->color = 5;
        $pk->overlay = 1;
        $player->dataPacket($pk);
    }

    public static function setBossTitle(Player $player, string $text): void
    {
        $pk = new SetEntityDataPacket;
        $pk->entityRuntimeId = $player->getClientId();
        $pk->metadata = [
            Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $text]
        ];
        $player->dataPacket($pk);
    }

    public function addPointInFFA(Player $entity, mixed $damager) : void{
        $entity->setGamemode(GameMode::SPECTATOR());
        $entity->teleport($entity->getLevel()->getSpawnLocation());
        $entity->getLevel()->addParticle(new DestroyBlockParticle($entity->getPosition(), Block::get(152, 0)));
        $entity->addTitle('§cYOU DEAD!');
        $entity->getInventory()->clearAll();
        $rand = mt_rand(5, 20);
        $rand2 = mt_rand(1, 15);
        $entity->getInventory()->setItem(2, ClickableItemFactory::REBORN());
        $entity->getInventory()->setItem(6, ClickableItemFactory::QUIT_LOBBY());
        unset($this->players[$entity->getName()]);
        if (!$damager) return;
        $damager->getLevel()->broadcastMessage("§l§c⚔§r §l" . $damager->getName() . "§r §7->§r §l" . $entity->getName());
        $factor = $damager->getFactor();
        unset($this->players[$damager->getName()]);
        $damager->addTitle('§7KILL', '§c' . $entity->getName());
        $damager->sendMessage(' §a+' . $rand * $factor . ' опыта! (Множитель: §l§b' . $damager->getFactorToString() . '§r§a)');
        $damager->sendMessage(' §e+' . $rand2 * $factor . ' монет! (Множитель: §l§b' . $damager->getFactorToString() . '§r§e)');
        $damager->addKill();
        $damager->setHealth(20);
        $damager->setFood(20);
        $damager->addExperience($rand * $factor);
        $damager->addMoney($rand2 * $factor);
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

    public static function sendHealthAttribute(Player $player, float $progress): void
    {
        $pk = new AddEntityPacket;
        $pk->entityRuntimeId = $player->getClientId();
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
        $pk->entityRuntimeId = $player->getClientId();
        $pk->entries = [
            Attribute::getAttribute(Attribute::HEALTH)->setMaxValue(101)->setValue($progress)
        ];
        $player->dataPacket($pk);
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

    /**
     * @return Loader
     */
    public static function getInstance(): Loader{
        return self::$instance;
    }
}
