<?php

declare(strict_types=1);

namespace duels\handler;

use pocketmine\event\Listener;

use pocketmine\event\player\{PlayerQuitEvent, PlayerExhaustEvent, PlayerInteractEvent};

use pocketmine\event\entity\{EntityDamageByChildEntityEvent, EntityDamageEvent, EntityDamageByEntityEvent};
use pocketmine\event\block\{BlockBreakEvent, BlockPlaceEvent};
use pocketmine\event\inventory\InventoryClickEvent;

use pocketmine\Server;

use pocketmine\Player;

use pocketmine\item\Item;

use pocketmine\level\sound\MinecraftSound;
use pocketmine\block\Block;
use pocketmine\lang\Translate;
use duels\manager\ArenaManager;

final class DuelsEventHandler implements Listener
{
    public $lastDamage = [];
    public $pearlCountdown = [];

    public function setLastDamager(Player $player, Player $damager): void
    {
        $this->lastDamage[$player->getName()] = ['damager' => $damager, 'time' => time()];
    }

    public function getLastDamager(Player $player)
    {
        if (isset($this->lastDamage[$player->getName()])) {
            $data = $this->lastDamage[$player->getName()];
            if ((time() - $data['time']) < 10) {
                return $data['damager'];
            }
            return null;
        }
        return null;
    }

    public function getPearlCountdown(Player $player)
    {
        if (isset($this->pearlCountdown[$player->getName()])) {
            return (time() - $this->pearlCountdown[$player->getName()]);
        }
        return null;
    }

    public function onDamage(EntityDamageEvent $event): void
    {
        $api = Server::getInstance()->getPluginManager()->getPlugin('SufixEngine');
        $player = $event->getEntity();
        if ($player instanceof Player) {
            if (ArenaManager::inGame($player)) {
                $game = ArenaManager::getGameByPlayer($player);
                if ($game->getGamemode() === 'sw') {
                    if ($event->getCause() === EntityDamageEvent::CAUSE_VOID && !$player->isSpectator()) {
                        $event->setCancelled();
                        $game->kill($player);
                        if ($this->getLastDamager($player) === NULL) {
                            foreach ($game->getArenaLevel()->getPlayers() as $players) {
                                $players->sendMessage(Translate::tr($players->getLocale(), 'saintpvp.duels.death', [$api->getRankColor($player) . $player->getName(true)]));
                            }
                        } else {
                            $damager = $this->getLastDamager($player);
                            foreach ($game->getArenaLevel()->getPlayers() as $players) {
                                $players->sendMessage(Translate::tr($players->getLocale(), 'saintpvp.duels.kill', [$api->getRankColor($player) . $player->getName(true), $api->getRankColor($damager) . $damager->getName(true)]));
                            }
                            $damager->getLevel()->addSound(new MinecraftSound($damager->asVector3(), 'mob.bat.death'));
                        }
                    }
                }
                if ($game->getGamemode() === 'bow') {
                    if (!$event instanceof EntityDamageByChildEntityEvent) $event->setCancelled();
                }
                if ($game->getState() < 2 || $game->getState() === 3 || $game->getState() === 4) {
                    $event->setCancelled();
                    if ($event->getCause() == EntityDamageEvent::CAUSE_VOID || $event->getCause() == EntityDamageEvent::CAUSE_FALL) {
                        $player->teleport($game->getSpawn($player));
                    }
                } else {
                    $message = '';
                    if ($event instanceof EntityDamageByEntityEvent && ($damager = $event->getDamager()) instanceof Player) {
                        if ($damager->getInventory()->getItemInHand()->getId() === Item::DIAMOND_PICKAXE) {
                            $event->setDamage(0);
                        }
                        $event->setGamemode($game->getGamemode());
                        $this->setLastDamager($player, $event->getDamager());
                        if ($game->getGamemode() == 'sumo') {
                            $player->setHealth(20);
                        }
                    }
                    if (($player->getHealth() - $event->getFinalDamage()) <= 2 && ($player->isAdventure() or $player->isSurvival())) {
                        if ($game->getGamemode() === 'sw' && $event instanceof EntityDamageByEntityEvent) {
                            $damager = $event->getDamager();
                            if ($damager instanceof Player) {
                                $damager->getLevel()->addSound(new MinecraftSound($damager->asVector3(), 'mob.bat.death'));
                                foreach ($game->getArenaLevel()->getPlayers() as $players) {
                                    $players->sendMessage(Translate::tr($players->getLocale(), 'saintpvp.duels.kill', [$api->getRankColor($player) . $player->getName(true), $api->getRankColor($damager) . $damager->getName(true)]));
                                }
                            }
                            $game->kill($player);
                        }
                        if ($game->getGamemode() === 'mlgrush') {
                            $player->getLevel()->addSound(new MinecraftSound($player->asVector3(), 'mob.bat.death'));
                        }
                        $event->setCancelled();
                        if ($event instanceof EntityDamageByEntityEvent) {
                            $damager = $event->getDamager();
                            if ($damager instanceof Player) {
                                $damager->getLevel()->addSound(new MinecraftSound($damager->asVector3(), 'mob.bat.death'));
                                foreach ($game->getArenaLevel()->getPlayers() as $players) {
                                    $players->sendMessage(Translate::tr($players->getLocale(), 'saintpvp.duels.kill', [$api->getRankColor($player) . $player->getName(true), $api->getRankColor($damager) . $damager->getName(true)]));
                                }
                            }
                        }
                        $game->kill($player);
                    }
                    if ($event->getCause() === EntityDamageEvent::CAUSE_VOID && !$player->isSpectator()) {
                        $event->setCancelled();
                        $game->kill($player);
                        if ($this->getLastDamager($player) == null) {
                            foreach ($game->getArenaLevel()->getPlayers() as $players) {
                                $players->sendMessage(Translate::tr($players->getLocale(), 'saintpvp.duels.death', [$api->getRankColor($player) . $player->getName(true)]));
                            }
                        } else {
                            $damager = $this->getLastDamager($player);
                            foreach ($game->getArenaLevel()->getPlayers() as $players) {
                                $players->sendMessage(Translate::tr($players->getLocale(), 'saintpvp.duels.kill', [$api->getRankColor($player) . $player->getName(true), $api->getRankColor($damager) . $damager->getName(true)]));
                            }
                            $damager->getLevel()->addSound(new MinecraftSound($damager->asVector3(), 'mob.bat.death'));
                        }
                    }
                }
            }
        }
    }

    final public function onExhaust(PlayerExhaustEvent $event): void
    {
        $player = $event->getPlayer();
        if (ArenaManager::inGame($player)) {
            $game = ArenaManager::getGameByPlayer($player);
            if ($game->getGamemode() == 'sumo' || $game->getState() < 2 || $game->getState() == 3 || $game->getGamemode() == 'combo' || $game->getGamemode() === 'mlgrush' || $game->getGamemode() === 'resistance') {
                $player->setFood(20);
                $event->setCancelled();
            }
        }
    }

    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        if (ArenaManager::inGame($player)) {
            $game = ArenaManager::getGameByPlayer($player);
            $game->quitGame($player, true);
        }
    }

    final public function handleBreak(BlockBreakEvent $event) : void {
        $player = $event->getPlayer();
        if (ArenaManager::inGame($player)) {
            $game = ArenaManager::getGameByPlayer($player);
            if ($game->getGamemode() !== 'sw') {
                $event->setCancel();
            }
        } else {
            //$event->setCancel();
        }
    }
    /*final public function onBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $event->setCancel();
        if (ArenaManager::inGame($player)) {
            $game = ArenaManager::getGameByPlayer($player);
            if ($game->canBlockBreak() and $game->getState() === 2) {
                if ($block->getId() !== Block::SANDSTONE or $block->getId() === Block::BED_BLOCK) {
                    $event->setCancelled();
                }
                if ($block->getId() === Block::BED_BLOCK) {
                    $next = $block->getOtherHalf();
                    $block->getLevel()->setBlockIdAt($block->getX(), $block->getY(), $block->getZ(), 26);
                    $block->getLevel()->setBlockIdAt($next->getX(), $next->getY(), $next->getZ(), 26);
                    if ($player->distance($game->getSpawn($player)) <= 14) {
                        $player->sendMessage(Translate::tr($player->getLocale(), 'saintpvp.duels.destroy_bed_team'));
                    } else {
                        $game->addPoint($player);
                    }
                }
            } else {
                $event->setCancelled();
            }
        }
    }
*/
    public function onPlace(BlockPlaceEvent $event): void {
        $player = $event->getPlayer();
        if (ArenaManager::inGame($player)) {
            $game = ArenaManager::getGameByPlayer($player);
            if ($game->getGamemode() !== 'sw') {
                //$event->setCancel();
            }
        }
    }

    public function handleInventoryClick(InventoryClickEvent $event): void
    {
        if ($event->getItem()->getId() === Item::DYE) {
            $event->setCancelled();
        }
    }

    public function onInteract(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        if (ArenaManager::inGame($player)) {
            $item = $player->getInventory()->getItemInHand();
            $game = ArenaManager::getGameByPlayer($player);
            if ($item->getId() == Item::PAPER) {
                foreach (ArenaManager::getArenas() as $ogame) {
                    if ($ogame->getGamemode() === $game->getGamemode() and $ogame->canJoin()) {
                        $game->quitGame($player, false);
                        $ogame->joinGame($player);
                        return true;
                    }
                }
                $player->sendMessage(Translate::tr($player->getLocale(), 'saintpvp.duels.no_free_arenas'));
                return true;
            } elseif ($item->getId() == Item::BED) {
                $game->quitGame($player);
            } elseif ($item->getId() === Item::DYE) {
                $game->addReady($player);
            }
        }
    }
}
