<?php

declare(strict_types=1);

namespace ddosnik\anticheat;

use ddosnik\anticheat\handler\EventHandler;
use ddosnik\anticheat\ffi\FFIClass;
use pocketmine\plugin\PluginBase;

class Loader extends PluginBase{
    const UNHANDLING_BLOCKS = [85, 113, 183, 184, 185, 186, 187, 212, 139];

    public array $words = [];
    public string $regex;

    public function onEnable() : void{
        $this->getServer()->getPluginManager()->registerEvents(new EventHandler($this), $this);
        $this->api = $this->getServer()->getPluginManager()->getPlugin('SufixEngine');
        $this->ffi = new FFIClass($this, $this->getDataFolder().'libsufix.so');
        if (file_exists($this->getDataFolder().'black_words.list')) {
            $this->words = file($this->getDataFolder() . "black_words.list", FILE_IGNORE_NEW_LINES);
            $this->ffi->lib->sendlog(1, 'Black words list successful loaded.');
        } else {
            $this->ffi->lib->sendlog(-1, 'Black words list not found.');
        }
        $this->regex = '/.*?(' . implode('|', array_map('preg_quote', $this->words)) . ').*?/iu';
    }

    public function getLibrary() : \FFI {
        return $this->ffi->lib;
    }
}