<?php

declare(strict_types=1);


namespace Authorization\commands;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

# Путь к главному классу

use Authorization\Loader;

class ChangePassword extends Command
{

	private $loader;

	public function __construct(Loader $loader) {
		$this->loader = $loader;
		parent::__construct("chp", "chp");
	}


	function execute(CommandSender $sender, $alias, array $args) : bool {
		$old_password = $this->loader->getPassword($sender);

		if(isset($args[0])) {
			if(isset($args[1])) {
				if($old_password === $args[0]) {
					if(!isset($args[2])) {
						if(strlen($args[1]) > 5) {
							
							$this->loader->setPassword($sender, $args[1]);

							$sender->sendMessage(Loader::Prefix."§fВы §aуспешно §fизменили пароль на§8 {$args[1]}§f! Запишите его...");
						} else {
							$sender->sendMessage(Loader::Prefix."§fДлина нового пароля должна быть §cбольше 6-ти символов§f, для вашей же §7безопасно§fсти!");
						}

					} else {
						$sender->sendMessage(Loader::Prefix."§fВ новом пароле §cне должно быть §fпробелов!");
					}

				} else {
					$sender->sendMessage(Loader::Prefix."§fСтарый пароль §cне соответствует тому§f, который вы ввели!");
				}

			} else {
				$sender->sendMessage(Loader::Prefix."§fВерное использование: /chp <старый пароль> <новый пароль>");
			}

		} else {
			$sender->sendMessage(Loader::Prefix."§fВерное использование: /chp <старый пароль> <новый пароль>");
		}
		return true;
	}

}