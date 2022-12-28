<?php

declare(strict_types=1);

namespace ddosnik\commands;

use pocketmine\command\CommandSender;
use ddosnik\Loader;
use ddosnik\player\SufixPlayer;

final class PosCommand extends SufixCommand {

    private Loader $loader;

	public function __construct(Loader $loader) {
		parent::__construct($loader, 'pos', 'Узнать позицию сущности', '/pos');
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if ($sender->isOp() && $sender instanceof SufixPlayer) {
			$sender->sendMessage((string)$sender->getLocation());
		}
	}
}