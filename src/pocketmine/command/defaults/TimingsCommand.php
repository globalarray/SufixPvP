<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\utils\Utils;
use pocketmine\utils\InternetException;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\timings\TimingsHandler;
use pocketmine\event\TranslationContainer;
use pocketmine\Player;
use pocketmine\scheduler\BulkCurlTask;
use pocketmine\scheduler\BulkCurlTaskOperation;
use pocketmine\Server;

use RuntimeException;

class TimingsCommand extends VanillaCommand{

	public static $timingStart = 0;

	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.timings.description",
			"%pocketmine.command.timings.usage"
		);
		$this->setPermission("pocketmine.command.timings");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) !== 1){
			throw new InvalidCommandSyntaxException();
		}

		$mode = strtolower($args[0]);

		if($mode === "on"){
			//$sender->getServer()->getPluginManager()->setUseTimings(true);
			TimingsHandler::setEnabled();
			$sender->sendMessage(new TranslationContainer("pocketmine.command.timings.enable"));

			return true;
		}elseif($mode === "off"){
			//$sender->getServer()->getPluginManager()->setUseTimings(false);
			TimingsHandler::setEnabled(false);
			$sender->sendMessage(new TranslationContainer("pocketmine.command.timings.disable"));
			return true;
		}

		if(!TimingsHandler::isEnabled()){
			$sender->sendMessage(new TranslationContainer("pocketmine.command.timings.timingsDisabled"));

			return true;
		}

		$paste = $mode === "paste";

		if($mode === "reset"){
			TimingsHandler::reload();
			$sender->sendMessage(new TranslationContainer("pocketmine.command.timings.reset"));
		}elseif($mode === "merged" || $mode === "report" || $paste){
			$timings = "";
			if($paste){
				$fileTimings = Utils::assumeNotFalse(fopen("php://temp", "r+b"), "Opening php://temp should never fail");
			}else{
				$index = 0;
				$timingFolder = $sender->getServer()->getDataPath() . "timings/";

				if(!file_exists($timingFolder)){
					mkdir($timingFolder, 0777);
				}
				$timings = Path::join($timingFolder, "timings.txt");
				while(file_exists($timings)){
					$timings = $timingFolder . "timings" . (++$index) . ".txt";
				}

				$fileTimings = fopen($timings, "a+b");
			}
			$lines = TimingsHandler::printTimings();
			foreach($lines as $line){
				fwrite($fileTimings, $line . PHP_EOL);
			}

			if($paste){
				fseek($fileTimings, 0);
				$data = [
					"browser" => $agent = $sender->getServer()->getName() . " " . $sender->getServer()->getPocketMineVersion(),
					"data" => $content = stream_get_contents($fileTimings)
				];
				fclose($fileTimings);

				$host = $sender->getServer()->getProperty("timings.host", "timings.pmmp.io");

                $sender->getServer()->getScheduler()->scheduleAsyncTask(new class($sender, $host, $agent, $data) extends BulkCurlTask{
					/** @var string */
					private $host;

					/**
					 * @param CommandSender $sender
					 * @param string                            $host
					 * @param string                            $agent
					 * @param string[]                          $data
					 *
					 * @phpstan-param array<string, string> $data
					 */
					public function __construct(CommandSender $sender, string $host, string $agent, array $data){
						parent::__construct([
							new BulkCurlTaskOperation(
								"https://$host?upload=true",
								10,
								[],
								[
									CURLOPT_HTTPHEADER => [
										"User-Agent: $agent",
										"Content-Type: application/x-www-form-urlencoded"
									],
									CURLOPT_POST => true,
									CURLOPT_POSTFIELDS => http_build_query($data),
									CURLOPT_AUTOREFERER => false,
									CURLOPT_FOLLOWLOCATION => false
								]
							)
						], $sender);
						$this->host = $host;
					}

					public function onCompletion(Server $server) {
						$sender = $this->fetchLocal();

						if($sender instanceof Player && !$sender->isOnline()){ // TODO replace with a more generic API method for checking availability of CommandSender
							return;
						}
						$result = $this->getResult()[0];

						if($result instanceof InternetException) {
							$server->getLogger()->logException($result);
							return;
						}

						$response = json_decode($result->getBody(), true);
						if(is_array($response) && isset($response["id"])) {
							$sender->sendMessage(new TranslationContainer("pocketmine.command.timings.timingsRead",
								["https://" . $this->host . "/?id=" . $response["id"]]));
						}else{
							$sender->sendMessage(new TranslationContainer("pocketmine.command.timings.pasteError"));
						}
					}
				});
			}else{
				fclose($fileTimings);
				$sender->sendMessage(new TranslationContainer("pocketmine.command.timings.timingsWrite", [$timings]));
			}
		}

		return true;
	}
}