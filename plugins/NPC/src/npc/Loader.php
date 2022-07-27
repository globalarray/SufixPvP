<?php

declare(strict_types=1);

namespace npc;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\entity\Human;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\level\Position;
use npc\utils\PlayerClone;
use ddosnik\flytext\scheduler\ParticleUpdate;

final class Loader extends PluginBase implements Listener{
	private const COMMANDS = [
		'duels-sumo' => 'duels join sumo',
		'duels-fist' => 'duels join fist',
		'duels-nodebuff' => 'duels join nodebuff',
		'duels-combo' => 'duels join combo',
		'duels-mlgrush' => 'duels join mlgrush',
		'duels-uhc' => 'duels join uhc',
        'duels-bow' => 'duels join bow'
	];

	private $clones = [];

	public function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	private function makeNPC(Position $pos, float $yaw, float $pitch, string $skinData, string $skinId, string $nametag) : Human{
		$entity = new Human($pos->level, new CompoundTag('', [
			'Pos' => new ListTag('Pos', [
				new DoubleTag('', $pos->x),
				new DoubleTag('', $pos->y),
				new DoubleTag('', $pos->z)
			]),
			'Motion' => new ListTag('Motion', [
				new DoubleTag('', 0),
				new DoubleTag('', 0),
				new DoubleTag('', 0)
			]),
			'Rotation' => new ListTag('Rotation', [
				new DoubleTag('', $yaw),
				new DoubleTag('', $pitch)
			]),
			'Skin' => new CompoundTag('Skin', [
				'Data' => new StringTag('Data', $skinData),
				'Name' => new StringTag('Name', $skinId)
			])
		]));
		$entity->setNameTag($nametag);
		$entity->setNameTagAlwaysVisible(false);
		$entity->setNameTagVisible(false);
		$entity->spawnToAll();
		return $entity;
	}

	final public function onCommand(CommandSender $sender, Command $command, string $commandLabel, array $args) : bool{
		if(!$sender instanceof Player){
			return false;
		}
		if(!$sender->isOp()){
			return false;
		}
		if(!isset($args[0])){
			return false;
		}
		$this->makeNPC($sender->getPosition(), $sender->yaw, $sender->pitch, $sender->getSkinData(), $sender->getSkinId(), $args[0]);
		$sender->sendMessage('NPC был создан!');
		return true;
	}

	final public function clonePlayer(Player $player, Position $pos, string $nametag, float $yaw = 0.0, float $pitch = 0.0) : void{
		$clone = new PlayerClone($pos, $player->getSkinId(), $player->getSkinData(), $nametag, $yaw, $pitch);
		$this->clones[$player->getName()][] = $clone;
		$clone->spawnTo($player);
	}

	final public function removeClones(Player $player) : void{
		foreach($this->clones[$player->getName()] as $clone){
			$clone->despawnFrom($player);
		}
		unset($this->clones[$player->getName()]);
	}

	final public function handleDamage(EntityDamageEvent $event) : void{
		if(
			$event instanceof EntityDamageByEntityEvent and 
			($player = $event->getDamager()) instanceof Player and 
			($npc = $event->getEntity()) instanceof Human and
			$player->getLevel() === $this->getServer()->getDefaultLevel()
		){
			$event->setCancelled();
			if(isset(Loader::COMMANDS[$npc->getNameTag()])){
				$this->getServer()->dispatchCommand($player, Loader::COMMANDS[$npc->getNameTag()]);
			}
		}
	}

	final public function handleLevelChange(EntityLevelChangeEvent $event) : void{
		$player = $event->getEntity();
		if($player instanceof Player){
		    if(isset($this->clones[$player->getName()])){
			    foreach($this->clones[$player->getName()] as $clone){
				    if($clone->getWorld() === $event->getTarget()){
					    $clone->spawnTo($player);
				    }else{
                        $clone->despawnFrom($player);
                    }
				}
			}
		}
	}
}
