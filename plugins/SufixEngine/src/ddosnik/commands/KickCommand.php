<?php

declare(strict_types=1);

namespace ddosnik\commands;

use ddosnik\Loader;
use pocketmine\Player;
use pocketmine\command\CommandSender;

final class KickCommand extends SufixCommand {

	public function __construct(Loader $loader) {
		parent::__construct($loader, 'kick', 'Выгнать игрока с сервера', '/kick <ник игрока ...>', '/kick', ['kick', 'кик']);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if ($sender instanceof Player) {
			if (Loader::FRANCHISES[$sender->getRank()] < 4 && !$sender->isOp()) {
				$sender->sendMessage(Loader::Prefix . '§cДанная команда доступна игрокам с привилегией §l§6Ｍｏｄｅｒａｔｏｒ§r' . PHP_EOL . Loader::Prefix . 'Повысить свой §aранг§r можно в нашем магазине §8- §epay.sufixpvp.su');
				return;
			}
		}

		if (!isset($args[0])) {
			$sender->sendMessage(Loader::Prefix.'§cУкажите никнейм и причину кика');
			$sender->sendMessage(Loader::Prefix.'Используйте - §a/кик §7<§fникнейм игрока§7> §7<§fпричина§7>');
			return;
		}

		$reason = implode($args);

		$reason = str_replace($args[0], '', $reason);

		if (!(($player = $this->getMain()->getServer()->getPlayer($args[0])) instanceof Player)) {
			$sender->sendMessage(Loader::Prefix.'§cИгрока нету на сервере');
			return;
		}

		if (empty($reason)) $reason = 'не указана.';

		$sender->sendMessage(Loader::Prefix. '§fИгрок §c'.$player->getName().'§r был кикнут с сервера, по причине: §e'.$reason);
		$this->getMain()->getServer()->broadcastMessage(Loader::Prefix.'Администратор §a'. $sender->getName() . '§f выгнал с сервера §a'. $player->getName() .'§f, по причине: §e'. $reason);
		$player->close($player->getLeaveMessage(), Loader::Prefix.'Вы были §cкикнуты§f с сервера'. PHP_EOL . Loader::Prefix.'Причина:§c '. $reason . PHP_EOL . Loader::Prefix . 'Вас кикнул: §l§e'.$sender->getName());
	}
}
