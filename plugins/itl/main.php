<?php
$lib = FFI::cdef("
extern const char** getTypes();
extern int getCountTypes();
extern double getWalkingSpeed();
extern double getMaxDiff();
extern int getLegitEatings();
extern double getMaxHitDistance();
extern void sendlog(int type, const char* text);
", "./libsufix.so");
for($i=0; $i < $lib->getCountTypes(); $i++) {
    var_dump($lib->getTypes()[$i]);
    var_dump($lib->getMaxDiff());
}
$lib->sendlog(-1, 'script terminated.');
?>