<?php

declare(strict_types=1);

namespace ddosnik\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use ddosnik\Loader;

abstract class SufixCommand extends Command implements PluginIdentifiableCommand
{
    /** @var SufixEngine */
    private Loader $main;
    /** @var bool|string */
    private mixed $consoleUsageMessage;

    public function __construct(Loader $main, string $name, string $description = "", string $usageMessage = "", $consoleUsageMessage = true, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->main = $main;
        $this->consoleUsageMessage = $consoleUsageMessage;
    }

    public function getPlugin(): Plugin{
        return $this->getMain();
    }

    public final function getMain(): Loader{
        return $this->main;
    }

   /**
    * @return bool|null|string
    */
   public function getConsoleUsage() : mixed{
       return $this->consoleUsageMessage;
   }

   /**
    * @return string
    */
   public function getUsage(): string
   {
       return "/" . parent::getName() . " " . parent::getUsage();
   }

   public function sendUsage(CommandSender $sender, string $alias): void{
        $message = TextFormat::RED . "Usage: " . TextFormat::GRAY . "/$alias ";
        if (!$sender instanceof Player) {
            if (is_string($this->consoleUsageMessage)) {
                $message .= $this->consoleUsageMessage;
            } elseif (!$this->consoleUsageMessage) {
                $message = TextFormat::RED . "Please run this command in-game";
            } else {
                $message .= str_replace("[player]", "[player]", parent::getUsage());
            }
        } else {
        $message .= parent::getUsage();
    }
    $sender->sendMessage($message);
    }
}