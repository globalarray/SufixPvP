<?php

namespace ddosnik\anticheat\ffi;

use ddosnik\anticheat\Loader;
use pocketmine\plugin\PluginException;

final class FFIClass {
    public \FFI $ffi;
    
    public function __construct($loader, $lib) {
        ini_set('memory_limit', '-1');
        set_time_limit(-1);
        if (!extension_loaded('FFI')) {
            throw new PluginException('FFI not found, please install libffi in your php binary');
            return;
        }

        if (!file_exists($lib)) {
            throw new PluginException('Sufix Library not found... Stopping plugin.'); //if you remove the call to this function, a RuntimeError will appear during the game.
            return;
        }

        $this->lib = \FFI::cdef("
        extern const char** getTypes();
        extern int getCountTypes();
        extern void starting();
        double maxDiff;
        int legitEatings;
        double walkingSpeed;
        extern void sendlog(int type, const char* text);
        ", $lib);
        $this->lib->starting();
    }
}