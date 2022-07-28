<?php

declare(strict_types=1);

namespace ddosnik\anticheat\handler;

use pocketmine\Server;
use ddosnik\anticheat\Loader;
use ddosnik\anticheat\scheduler\{HelloTitle, SendGuardian};
use pocketmine\event\entity\{EntityDamageByEntityEvent, EntityDamageEvent, EntityLevelChangeEvent};
use pocketmine\network\mcpe\protocol\{AnimatePacket, LevelEventPacket, EntityEventPacket, TextPacket};
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\Listener;
use pocketmine\item\{Item, Food};
use pocketmine\math\Vector3;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\{PlayerJoinEvent, PlayerQuitEvent, PlayerInteractEvent, PlayerMoveEvent, PlayerPreLoginEvent};
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\entity\Effect;

class EventHandler implements Listener{
    private $main;

    private $warnings = [];
    private $multiaura = [];
    private $animate = [];
    private $breakTime = [];
    private $eat = [];
    private $ticking = [];

    public function __construct(Loader $main){
        $this->main = $main;
    }

    public function handleInteract(PlayerInteractEvent $event) : void{
        if ($event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK) {
            $this->breakTime[$event->getPlayer()->getUUID()] = floor(microtime(true) * 20);
        }
    }

    public function handlePlayerPreLogin(PlayerPreLoginEvent $event) : void{
        if (($player = $event->getPlayer())->getSkinId() === 'GreekMythology_GreekMythologyZeus') {
            $this->log('Игрок '. TextFormat::RED . $player->getName() . TextFormat::WHITE . ' пытался зайти на сервер с невидимым скином');
            $player->close($player->getLeaveMessage(), TextFormat::RED . 'Запрещено использовать невидимый скин');
        }
    }

    public function filterMessage(string $message) : string{
        return preg_replace_callback($this->main->regex, function($matches) {
            return str_replace($matches[1], "***", $matches[0]);
        }, $message);
    }

    public function handleBreak(BlockBreakEvent $event) : void{
        if (!$event->getInstaBreak()) {
            $player = $event->getPlayer();
            if (!isset($this->breakTime[$secret = $player->getUUID()])) {
                $this->log('Игрок '. TextFormat::RED . $player->getName() . TextFormat::WHITE . ' пытался сломать блок без начала ломания блока.');
                $event->setCancel();
                return;
            }

            $block = $event->getBlock();
            $item = $event->getItem();

            $expectedTime = ceil($block->getBreakTime($item) * 20);

            if ($hasteEffect = $player->hasEffect(Effect::HASTE)) {
                $expectedTime *= 1 - (0.2 * $hasteEffect->getEffectLevel());
            }

            if ($fatigueEffect = $player->hasEffect(Effect::MINING_FATIGUE)) {
                $expectedTime *= 1 + (0.3 * $fatigueEffect->getEffectLevel());
            }

            $expectedTime -= 1;

            $actualTime = ceil(microtime(true) * 20) - $this->breakTime[$secret];

            if ($actualTime < $expectedTime) {
                $this->log('Игрок '.$player->getName().' сломал блок за '.$actualTime.'тик., чтобы сломать блок нужно было потратить '.$expectedTime.'тик.');
                $event->setCancel();
                return;
            }

            unset($this->breakTime[$secret]);
        }
    }

    public function handleQuit(PlayerQuitEvent $event) : void{
        $player = $event->getPlayer();
        $this->log(TextFormat::GREEN . $player->getName() . TextFormat::WHITE . ' вышел с сервера');
        unset($this->breakTime[$secret = $player->getUUID()]);
        unset($this->animate[$secret]);
        unset($this->warnings[$player->getUUID()]);
        if(isset($this->ticking[spl_object_hash($player)])){
			unset($this->ticking[spl_object_hash($player)]);
		}
    }

    public function handleJoin(PlayerJoinEvent $event) : void{
        $player = $event->getPlayer();
        $this->main->getServer()->getScheduler()->scheduleDelayedTask(new SendGuardian($player), 90);
        $this->main->getServer()->getScheduler()->scheduleDelayedTask(new HelloTitle($player), 100);
        $this->log(TextFormat::GREEN . $player->getName() . TextFormat::WHITE . ' присоединился на сервер');
        $this->createData($player);
    }

    /*public function handleMove(PlayerMoveEvent $event) {
		$player = $event->getPlayer();
        $uuid = $player->getUUID();
        if ($this->warnings[$uuid]['movement'] >= 3) {
            $player->close($player->getLeaveMessage(), 'Fly detected');
            return;
        }
		$currentTick = round(microtime(true) * 20);
		if (!isset($this->ticking[spl_object_hash($player)])){
			$this->ticking[spl_object_hash($player)] = $currentTick;
		}
		$tickDiff = $currentTick - $this->ticking[spl_object_hash($player)];
		if ($tickDiff == 0)
			$tickDiff = 1;
		$this->ticking[spl_object_hash($player)] = $currentTick;
		$newPos = $event->getTo();
		$diffX = $player->x - $newPos->x;
		$diffY = $player->y - $newPos->y;
		$diffZ = $player->z - $newPos->z;
		$diff = ($diffX ** 2 + $diffY ** 2 + $diffZ ** 2) / ($tickDiff ** 2);
		if ($diff > $this->main->getLibrary()->maxDiff){
            ++$this->warnings[$uuid]['movement'];
			$this->log(TextFormat::GREEN . $player->getName() . TextFormat::WHITE . ' использует MovementHack §7(§d'.$this->warnings[$uuid]['movement'].'§7/§d3§7)');
 			$event->setCancelled();
 			return;
 		}
 		$speed = $newPos->subtract($player->getLocation())->divide($tickDiff);
 		if ($player->isAlive() and !$player->isSpectator()){
			if ($player->getInAirTicks() > 10 and !$player->isSleeping() and !$player->isImmobile() and !$player->getAllowFlight()){
				$blockUnder = $player->getLevel()->getBlock(new Vector3($player->x, $player->y - 1, $player->z));
				if (in_array($blockUnder->getId(), Loader::UNHANDLING_BLOCKS)) {
					$player->resetAirTicks();
					return;
				}
				$expectedVelocity = -0.08 / 0.02 - (-0.08 / 0.02) * exp(-0.02 * ($player->getInAirTicks() - $player->getStartAirTicks()));
				$jumpVelocity = (0.42 + ($player->hasEffect(Effect::JUMP) ? ($player->getEffect(Effect::JUMP)->getEffectLevel() /10) : 0)) / 0.42;
				$diff = (($speed->y - $expectedVelocity) ** 2) / $jumpVelocity;
				if ($diff > 0.6 and $expectedVelocity < $speed->y){
					if ($player->getInAirTicks() < 100){
						$player->setMotion(new Vector3(0, $expectedVelocity, 0));
					} else {
                        ++$this->warnings[$uuid]['movement'];
						$this->log(TextFormat::GREEN . $player->getName() . TextFormat::WHITE . ' использует MovementHack §7(§d'.$this->warnings[$uuid]['movement'].'§7/§d3§7)');
					}
				}
			}
		}
	}
*/
    public function createData(Player $player) : void{
        for ($i=0; $i < $this->main->getLibrary()->getCountTypes(); $i++) $this->warnings[$player->getUUID()][$this->main->getLibrary()->getTypes()[$i]] = 0;
    }

    public function handleDataPacket(DataPacketReceiveEvent $e) {
        $packet = $e->getPacket();
        if ($packet instanceof AnimatePacket)
            $this->animate[$e->getPlayer()->getUUID()] = microtime(true);
        if ($packet instanceof EntityEventPacket) {
            $packet->decode();
            $player = $e->getPlayer();
            $uuid = $player->getUUID();
            if (!isset($this->eat[$uuid])) {
                $this->eat[$uuid] = 1;
            }
            if ($packet->event === 57) {
                if (!(Item::get($packet->data) instanceof Food)) {
                    $this->log(TextFormat::GREEN . $player->getName() . TextFormat::WHITE . 'пытался использовать критический баг (неккор. дата в событии).');
                    $player->close($player->getLeaveMessage(), 'Invalid eat item, stupid...');
                    return false;
                }
                $this->eat[$uuid] += 1;
            }
            if ($packet->event === 9) {
                if ($this->warnings[$uuid]['eat'] >= 3) $player->close($player->getLeaveMessage(), 'FastEat detected');
                if ($this->eat[$uuid] < $this->main->getLibrary()->legitEatings && $player->isOnline()) {
                    $this->log(TextFormat::GREEN . $player->getName() . TextFormat::WHITE . ' использует FastEat §7(§d'.$this->warnings[$uuid]['eat'].'§7/§d3§7)');
                    ++$this->warnings[$uuid]['eat'];
                    unset($this->eat[$uuid]);
                } else {
                    unset($this->eat[$uuid]);
                }
            }
        }
        if ($packet instanceof TextPacket) {
            if ($packet->type !== TextPacket::TYPE_TRANSLATION) {
                $packet->message = $this->filterMessage($packet->message);
            }
            foreach ($packet->parameters as $k => $param) {
                $packet->parameters[$k] = $this->filterMessage($packet->parameters[$k]);
            }
        }
    }

    public function getMaxDistance(Player $player, $tickDifference) {
		$effects = $player->getEffects();

		$amplifier = 0;
		if(!empty($effects)) {
			foreach($effects as $effect) {
				if($effect->getId() == Effect::SPEED) {
					$a = $effect->getAmplifier();
					if ($a > $amplifier) {
						$amplifier = $a;
					}
				}
			}
		}

		$distance = $this->main->getLibrary()->walkingSpeed + ($amplifier != 0) ? ($this->main->getLibrary()->walkingSpeed / (0.2 * $amplifier)) : 0;

		return $distance * ($tickDifference / 20);
	}

    private function log(string $message) : void{
        foreach($this->main->getServer()->getOnlinePlayers() as $player){
            if($this->main->api->getGroup($player) === 'MOD' || $player->isOp()){
                $player->sendMessage(TextFormat::GRAY . '[' . TextFormat::LIGHT_PURPLE . 'SUFIX-AC' . TextFormat::GRAY . '] ' . TextFormat::RESET . $message);
            }
        }
    }


    public function handleDamage(EntityDamageEvent $event) : void{
            if($event instanceof EntityDamageByEntityEvent and $event->getEntity() instanceof Player and $event->getDamager() instanceof Player and $event->getCause() === EntityDamageEvent::CAUSE_ENTITY_ATTACK and !$event->isCancelled()) {
                $damager = $event->getDamager();
                $player = $event->getEntity();
                if ($this->warnings[$damager->getUUID()]['reach'] >= 3) {
                    $damager->close($damager->getLeaveMessage(), 'Reach detected');
                    return;
                } else if ($this->warnings[$damager->getUUID()]['killaura'] >= 3) {
                    $damager->close($damager->getLeaveMessage(), 'Killaura detected');
                    return;
                }
                if ($player->distance($damager) >= 8) {
                    $event->setCancelled();
                    ++$this->warnings[$uuid = $damager->getUUID()]['reach'];
                    $this->log(TextFormat::GREEN . $damager->getName() . ' §7(§d' . $damager->getPing() . 'ms§7)' . TextFormat::WHITE . ' использует Reach §7(§d'.$this->warnings[$uuid]['reach'].'§7/§d3§7)');
                    return;
                }
                if (microtime(true) - $this->animate[$uuid = $damager->getUUID()] > 2) {
                    $event->setCancelled();
                    ++$this->warnings[$uuid]['killaura'];
                    $this->log(TextFormat::GREEN . $damager->getName().' §7(§d' . $damager->getPing() . 'ms§7)' . TextFormat::WHITE . ' использует Killaura §7(§d'.$this->warnings[$uuid]['killaura'].'§7/§d3§7)');
                    return;
                }
            }
        }
}