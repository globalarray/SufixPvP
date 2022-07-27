<?php

declare(strict_types=1);

namespace pocketmine\lang;

use function str_replace;
use const PHP_EOL;

final class Translate{
	private const LANGUAGES = [
		'ru_RU' => 'rus'
	];

	private static $languages = [];

	public static function init() : void{
		foreach(self::LANGUAGES as $code => $lang){
			self::$languages[$code] = new BaseLang($lang);
		}
	}

	public static function tr(string $locale, string $message, array $params = []) : string{
		if(!isset(self::$languages[$locale])){
			$locale = 'ru_RU';
		}
		return str_replace('%EOL%', PHP_EOL, self::$languages[$locale]->translateString($message, $params));
	}
}