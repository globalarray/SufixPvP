<?php

declare(strict_types=1);

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
 * @link https://vk.com/ddosnik
 *
 */

namespace ddosnik\handler;

use pocketmine\GameMode;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\MoveEntityPacket;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use ddosnik\Loader;
use ddosnik\player\SufixPlayer;
use ddosnik\task\ParticleDespawn;
use ddosnik\task\ParticleSpawn;
use ddosnik\task\ParticleUpdate;
use ddosnik\menu\ClickableItemFactory;
use ddosnik\menu\ClickableItem;

final class EventHandler implements Listener {

	public function __construct(
		private Loader $loader
	){}

    /*public function handlePacket(DataPacketReceiveEvent $event) : void{
        /*if (($packetId = ord(($packet = $event->getPacket())->buffer[0])) === ProtocolInfo::ANIMATE_PACKET) {
            if ((microtime(true) - ($player = $event->getPlayer())->lastClicksUpdate) > 1.2) {
                $player->clicksPerSecond = 0;
                $player->lastClicksUpdate = microtime(true);
            }
            $player->clicksPerSecond++;
            $player->sendPopup('CPS: ' . $player->getClicksPerSecond());
        }
        if (($packetId = ord(($packet = $event->getPacket())->buffer[0])) !== 254 && $packetId !== 39) {
            $packet->decode();
            var_dump($packet);
        }
    }*/

	public function handlePlayerPreLogin(PlayerPreLoginEvent $event): void{
        $nickname = $event->getPlayer()->getLowerCaseName();
        if (!($this->loader->data->query("SELECT * FROM `database` WHERE `nickname` = '$nickname'")->fetchArray(SQLITE3_ASSOC))) {
            $this->loader->data->query("INSERT INTO `database`(`nickname`,`balance`, `particle`, `wins`, `lvl`, `color`, `blue_tag`, `red_tag`, `green_tag`, `yellow_tag`, `group`, `kills`, `exp`, `factor`, `heart`, `custom`) VALUES('{$nickname}', 0, false, 0, 1, '§7', false, false, false, false, 'GUEST', 0, 0, 1, false, false)");
        }
    }

    public function handleCommand(PlayerCommandPreprocessEvent $event): void{
        if (isset($this->loader->players[$event->getPlayer()->getName()])) {
            if (str_contains($event->getMessage(), '/quit')) {
                $event->getPlayer()->sendMessage(Loader::Prefix . "§fВы находитесь в режиме поединка, команда будет доступна в течении §l§aдесяти§r секунд.");
                $event->setCancelled();
            }
        }
    }

    public function handlePlayerInteract(PlayerInteractEvent $event) : void{
        $player = $event->getPlayer();
        //TODO: if (!$player->isLogined()) return;
        if ($this->loader->auth->players[$player->getLowerCaseName()] !== 'game') return;
        $player->addClickToQueue();
        //$player->sendPopup('CPS: ' . $player->getClicksPerSecond());
        if ($event->getAction() === InteractPacket::ACTION_LEAVE_VEHICLE) {
            if (($item = $event->getItem()) instanceof ClickableItem) {
                $event->setCancelled();
                $item->handleClick($player);
            }
        }
    }

    public function handleFall(PlayerMoveEvent $event): void{
        if (($player = $event->getPlayer())->getFloorY() < 0 && $player->getLevel()->isDefault()) {
            $player->teleport($player->getLevel()->getSpawnLocation());
        }
    }

    public function handleConsume(PlayerItemConsumeEvent $event): void{
        if ($event->getItem() instanceof ClickableItem)
        	$event->setCancelled();
    }

    public function onExhaust(PlayerExhaustEvent $event): void{
        if ($event->getPlayer()->getLevel()->isDefault()) $event->setCancelled();
    }

    public function handleDropPlayer(PlayerDropItemEvent $event): void{
        if ($event->getItem() instanceof ClickableItem)
            $event->setCancelled();
    }

    public function handlePlayerMove(PlayerMoveEvent $event): void{
        $player = $event->getPlayer();
        if (!$player->getLevel()->isDefault()) {
        	$pk = new MoveEntityPacket;
        	$pk->entityRuntimeId = 999888777;
        	$pk->position = new Vector3($player->x, $player->y + 128, $player->z);
        	$pk->x = $player->asVector3()->x;
        	$pk->y = $player->asVector3()->y + 128;
        	$pk->z = $player->asVector3()->z;
        	$pk->yaw = $pk->headYaw = $pk->pitch = 0.0;
        	$player->dataPacket($pk);
        }
    }

    public function handlePlayerDamage(EntityDamageEvent $event) : void{
        if (
					$event->getCause() === EntityDamageEvent::CAUSE_VOID &&
					($entity = $event->getEntity()) instanceof SufixPlayer &&
					!$entity->isSpectator() &&
					$entity->inFFA()
				) {
            $event->setCancelled();
            $this->loader->addPointInFFA($entity, $this->loader->getLastDamager($entity));
            return;
        }
        if ($event instanceof EntityDamageByEntityEvent) {
            if (
							!$event->getDamager()->getLevel()->isDefault() &&
						  ($damager = $event->getDamager()) instanceof SufixPlayer &&
							($entity = $event->getEntity()) instanceof SufixPlayer
						) {
                $this->loader->setTime($damager);
                $this->loader->setTime($entity);
								if ($event instanceof EntityDamageByChildEntityEvent) {
									$damager->sendTip(TextFormat::RED . 'HIT! ' . TextFormat::YELLOW . 'Distance: ' . TextFormat::DARK_GREEN . ceil($entity->getPosition()->distance($damager->getPosition())));
								} else {
									$damager->addClickToQueue();
									$damager->sendTip(TextFormat::YELLOW . 'CPS: ' . TextFormat::DARK_GREEN . $damager->getClicksPerSecond());
								}
								if (!$damager->inFFA()) return;
                if ($damager->getFFAMode() === 'underfined')
                	return;
                else if ($damager->getFFAMode() === 'resistance')
                	$event->setDamage(0);
                if (($entity->getHealth() - $event->getFinalDamage()) <= 2 && ($entity->isAdventure() or $entity->isSurvival()) && $entity->inFFA()) {
                    $event->setCancelled();
                    $this->loader->addPointInFFA($entity, $damager);
								}
						}
					}
				}

	public function handlePlayerQuit(PlayerQuitEvent $event) : void{
		$event->setQuitMessage(null);
        ($player = $event->getPlayer())->unEquipWings();
	}

    public function handleRegisterPlayer(PlayerCreationEvent $event) : void{
        $event->setPlayerClass(SufixPlayer::class);
    }

    public function handleInventoryTransaction(InventoryTransactionEvent $event): void{
        if (($player = $event->getTransaction()->getPlayer())->getLevel()->isDefault() && !$player->isCreative())
        	$event->setCancelled();
    }

    public function handlePlayerChatted(PlayerChatEvent $event): void{
        $player = $event->getPlayer();
				$event->setFormat($player->getSufixNameTag() . TextFormat::GRAY . ': ' . TextFormat::clean($event->getMessage()));
				if (!$player->isOp()) {
					$player->getLevel()->broadcastMessage($event->getFormat());
					$event->setCancelled();
				}
    }

	public function handleEntityLevelChange(EntityLevelChangeEvent $event) : void{
		if(($player = $event->getEntity()) instanceof Player){
			if($event->getTarget() === $this->loader->getServer()->getDefaultLevel()){
				$this->loader->getServer()->getScheduler()->scheduleAsyncTask(new ParticleSpawn($this->loader->getParticles(), $player->getName()));
			}else{
				$this->loader->getServer()->getScheduler()->scheduleAsyncTask(new ParticleDespawn($this->loader->getParticles(), $player->getName()));
			}
		}
	}

	public function handleJoin(PlayerJoinEvent $event) : void{
		$player = $event->getPlayer();
		$event->setJoinMessage(null);
		$player->teleport($this->loader->getServer()->getDefaultLevel()->getSpawnLocation());
		ParticleUpdate::getInstance()->statistics($this->loader);
		if($player->getLevel() === $this->loader->getServer()->getDefaultLevel()){
			$this->loader->getServer()->getScheduler()->scheduleAsyncTask(new ParticleSpawn($this->loader->getParticles(), $player->getName()));
		}
    $player->getInventory()->clearAll();
    $player->removeBossBar();
    $player->setHealth(20);
    $player->setMaxHealth(20);
    $player->setFood(20);
    $player->setXpLevel($player->getLvl());
    $player->removeAllEffects();
    $player->setGamemode(GameMode::ADVENTURE());
    $player->updateNameTag();
    $player->updateDisplayName();
    $player->updateTime();
    $player->getInventory()->setItem(4, ClickableItemFactory::JOIN_ARENA());
    $player->getInventory()->setItem(2, ClickableItemFactory::CLOAKS());
    $player->getInventory()->setItem(6, ClickableItemFactory::CUSTOMIZATION());
    $player->sendMessage("§fДобро пожаловать на §l§dSufixPvP§r§f, §e§l{$player->getName()}§r§f!\n\n§fСообщество во §9ВКонтакте §8- §e@sufixpvp\n§aАвто-донат §8- §ehttps://pay.sufixpvp.fun/");
	}
}
