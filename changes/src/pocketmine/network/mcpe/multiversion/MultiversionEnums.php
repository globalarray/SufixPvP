<?php

/**
 *
 *  ____       _                          _
 * |  _ \ _ __(_)___ _ __ ___   __ _ _ __(_)_ __   ___
 * | |_) | '__| / __| '_ ` _ \ / _` | '__| | '_ \ / _ \
 * |  __/| |  | \__ \ | | | | | (_| | |  | | | | |  __/
 * |_|   |_|  |_|___/_| |_| |_|\__,_|_|  |_|_| |_|\___|
 *
 * Prismarine is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Prismarine Team
 * @link   https://github.com/PrismarineMC/Prismarine
 *
 *
 */

namespace pocketmine\network\mcpe\multiversion;

use pocketmine\network\mcpe\protocol\ProtocolInfo;

class MultiversionEnums{
	
	protected static $textPacket = [
		ProtocolInfo::CURRENT_PROTOCOL => [
			0 => "TYPE_RAW",
			1 => "TYPE_CHAT",
			2 => "TYPE_TRANSLATION",
			3 => "TYPE_POPUP",
			4 => "TYPE_TIP",
			5 => "TYPE_SYSTEM",
			6 => "TYPE_WHISPER",
			7 => "TYPE_ANNOUNCEMENT"
		],
		ProtocolInfo::MULTIVERSON_PROTOCOL => [
			0 => "TYPE_RAW",
			1 => "TYPE_CHAT",
			2 => "TYPE_TRANSLATION",
			3 => "TYPE_POPUP",
			4 => "TYPE_JUKEBOX_POPUP",
			5 => "TYPE_TIP",
			6 => "TYPE_SYSTEM",
			7 => "TYPE_WHISPER",
			8 => "TYPE_ANNOUNCEMENT"
		]
	];

	protected static $levelSoundEventPacket = [
		ProtocolInfo::CURRENT_PROTOCOL => [
			0 => "SOUND_ITEM_USE_ON",
			1 => "SOUND_HIT",
			2 => "SOUND_STEP",
			3 => "SOUND_JUMP",
			4 => "SOUND_BREAK",
			5 => "SOUND_PLACE",
			6 => "SOUND_HEAVY_STEP",
			7 => "SOUND_GALLOP",
			8 => "SOUND_FALL",
			9 => "SOUND_AMBIENT",
			10 => "SOUND_AMBIENT_BABY",
			11 => "SOUND_AMBIENT_IN_WATER",
			12 => "SOUND_BREATHE",
			13 => "SOUND_DEATH",
			14 => "SOUND_DEATH_IN_WATER",
			15 => "SOUND_DEATH_TO_ZOMBIE",
			16 => "SOUND_HURT",
			17 => "SOUND_HURT_IN_WATER",
			18 => "SOUND_MAD",
			19 => "SOUND_BOOST",
			20 => "SOUND_BOW",
			21 => "SOUND_SQUISH_BIG",
			22 => "SOUND_SQUISH_SMALL",
			23 => "SOUND_FALL_BIG",
			24 => "SOUND_FALL_SMALL",
			25 => "SOUND_SPLASH",
			26 => "SOUND_FIZZ",
			27 => "SOUND_FLAP",
			28 => "SOUND_SWIM",
			29 => "SOUND_DRINK",
			30 => "SOUND_EAT",
			31 => "SOUND_TAKEOFF",
			32 => "SOUND_SHAKE",
			33 => "SOUND_PLOP",
			34 => "SOUND_LAND",
			35 => "SOUND_SADDLE",
			36 => "SOUND_ARMOR",
			37 => "SOUND_ADD_CHEST",
			38 => "SOUND_THROW",
			39 => "SOUND_ATTACK",
			40 => "SOUND_ATTACK_NODAMAGE",
			41 => "SOUND_WARN",
			42 => "SOUND_SHEAR",
			43 => "SOUND_MILK",
			44 => "SOUND_THUNDER",
			45 => "SOUND_EXPLODE",
			46 => "SOUND_FIRE",
			47 => "SOUND_IGNITE",
			48 => "SOUND_FUSE",
			49 => "SOUND_STARE",
			50 => "SOUND_SPAWN",
			51 => "SOUND_SHOOT",
			52 => "SOUND_BREAK_BLOCK",
			53 => "SOUND_REMEDY",
			54 => "SOUND_UNFECT",
			55 => "SOUND_LEVELUP",
			56 => "SOUND_BOW_HIT",
			57 => "SOUND_BULLET_HIT",
			58 => "SOUND_EXTINGUISH_FIRE",
			59 => "SOUND_ITEM_FIZZ",
			60 => "SOUND_CHEST_OPEN",
			61 => "SOUND_CHEST_CLOSED",
			62 => "SOUND_SHULKERBOX_OPEN",
			63 => "SOUND_SHULKERBOX_CLOSED",
			64 => "SOUND_POWER_ON",
			65 => "SOUND_POWER_OFF",
			66 => "SOUND_ATTACH",
			67 => "SOUND_DETACH",
			68 => "SOUND_DENY",
			69 => "SOUND_TRIPOD",
			70 => "SOUND_POP",
			71 => "SOUND_DROP_SLOT",
			72 => "SOUND_NOTE",
			73 => "SOUND_THORNS",
			74 => "SOUND_PISTON_IN",
			75 => "SOUND_PISTON_OUT",
			76 => "SOUND_PORTAL",
			77 => "SOUND_WATER",
			78 => "SOUND_LAVA_POP",
			79 => "SOUND_LAVA",
			80 => "SOUND_BURP",
			81 => "SOUND_BUCKET_FILL_WATER",
			82 => "SOUND_BUCKET_FILL_LAVA",
			83 => "SOUND_BUCKET_EMPTY_WATER",
			84 => "SOUND_BUCKET_EMPTY_LAVA",
			85 => "SOUND_GUARDIAN_FLOP",
			86 => "SOUND_ELDERGUARDIAN_CURSE",
			87 => "SOUND_MOB_WARNING",
			88 => "SOUND_MOB_WARNING_BABY",
			89 => "SOUND_TELEPORT",
			90 => "SOUND_SHULKER_OPEN",
			91 => "SOUND_SHULKER_CLOSE",
			92 => "SOUND_HAGGLE",
			93 => "SOUND_HAGGLE_YES",
			94 => "SOUND_HAGGLE_NO",
			95 => "SOUND_HAGGLE_IDLE",
			96 => "SOUND_CHORUSGROW",
			97 => "SOUND_CHORUSDEATH",
			98 => "SOUND_GLASS",
			99 => "SOUND_CAST_SPELL",
			100 => "SOUND_PREPARE_ATTACK",
			101 => "SOUND_PREPARE_SUMMON",
			102 => "SOUND_PREPARE_WOLOLO",
			103 => "SOUND_FANG",
			104 => "SOUND_CHARGE",
			105 => "SOUND_CAMERA_TAKE_PICTURE",
			106 => "SOUND_DEFAULT",
			107 => "SOUND_UNDEFINED"
		],
		ProtocolInfo::MULTIVERSON_PROTOCOL => [
			0 => "SOUND_ITEM_USE_ON",
			1 => "SOUND_HIT",
			2 => "SOUND_STEP",
			3 => "SOUND_FLY",
			4 => "SOUND_JUMP",
			5 => "SOUND_BREAK",
			6 => "SOUND_PLACE",
			7 => "SOUND_HEAVY_STEP",
			8 => "SOUND_GALLOP",
			9 => "SOUND_FALL",
			10 => "SOUND_AMBIENT",
			11 => "SOUND_AMBIENT_BABY",
			12 => "SOUND_AMBIENT_IN_WATER",
			13 => "SOUND_BREATHE",
			14 => "SOUND_DEATH",
			15 => "SOUND_DEATH_IN_WATER",
			16 => "SOUND_DEATH_TO_ZOMBIE",
			17 => "SOUND_HURT",
			18 => "SOUND_HURT_IN_WATER",
			19 => "SOUND_MAD",
			20 => "SOUND_BOOST",
			21 => "SOUND_BOW",
			22 => "SOUND_SQUISH_BIG",
			23 => "SOUND_SQUISH_SMALL",
			24 => "SOUND_FALL_BIG",
			25 => "SOUND_FALL_SMALL",
			26 => "SOUND_SPLASH",
			27 => "SOUND_FIZZ",
			28 => "SOUND_FLAP",
			29 => "SOUND_SWIM",
			30 => "SOUND_DRINK",
			31 => "SOUND_EAT",
			32 => "SOUND_TAKEOFF",
			33 => "SOUND_SHAKE",
			34 => "SOUND_PLOP",
			35 => "SOUND_LAND",
			36 => "SOUND_SADDLE",
			37 => "SOUND_ARMOR",
			38 => "SOUND_ADD_CHEST",
			39 => "SOUND_THROW",
			40 => "SOUND_ATTACK",
			41 => "SOUND_ATTACK_NODAMAGE",
			42 => "SOUND_ATTACK_STRONG",
			43 => "SOUND_WARN",
			44 => "SOUND_SHEAR",
			45 => "SOUND_MILK",
			46 => "SOUND_THUNDER",
			47 => "SOUND_EXPLODE",
			48 => "SOUND_FIRE",
			49 => "SOUND_IGNITE",
			50 => "SOUND_FUSE",
			51 => "SOUND_STARE",
			52 => "SOUND_SPAWN",
			53 => "SOUND_SHOOT",
			54 => "SOUND_BREAK_BLOCK",
			55 => "SOUND_LAUNCH",
			56 => "SOUND_BLAST",
			57 => "SOUND_LARGE_BLAST",
			58 => "SOUND_TWINKLE",
			59 => "SOUND_REMEDY",
			60 => "SOUND_UNFECT",
			61 => "SOUND_LEVELUP",
			62 => "SOUND_BOW_HIT",
			63 => "SOUND_BULLET_HIT",
			64 => "SOUND_EXTINGUISH_FIRE",
			65 => "SOUND_ITEM_FIZZ",
			66 => "SOUND_CHEST_OPEN",
			67 => "SOUND_CHEST_CLOSED",
			68 => "SOUND_SHULKERBOX_OPEN",
			69 => "SOUND_SHULKERBOX_CLOSED",
			70 => "SOUND_POWER_ON",
			71 => "SOUND_POWER_OFF",
			72 => "SOUND_ATTACH",
			73 => "SOUND_DETACH",
			74 => "SOUND_DENY",
			75 => "SOUND_TRIPOD",
			76 => "SOUND_POP",
			77 => "SOUND_DROP_SLOT",
			78 => "SOUND_NOTE",
			79 => "SOUND_THORNS",
			80 => "SOUND_PISTON_IN",
			81 => "SOUND_PISTON_OUT",
			82 => "SOUND_PORTAL",
			83 => "SOUND_WATER",
			84 => "SOUND_LAVA_POP",
			85 => "SOUND_LAVA",
			86 => "SOUND_BURP",
			87 => "SOUND_BUCKET_FILL_WATER",
			88 => "SOUND_BUCKET_FILL_LAVA",
			89 => "SOUND_BUCKET_EMPTY_WATER",
			90 => "SOUND_BUCKET_EMPTY_LAVA",
			91 => "SOUND_RECORD_13",
			92 => "SOUND_RECORD_CAT",
			93 => "SOUND_RECORD_BLOCKS",
			94 => "SOUND_RECORD_CHIRP",
			95 => "SOUND_RECORD_FAR",
			96 => "SOUND_RECORD_MALL",
			97 => "SOUND_RECORD_MELLOHI",
			98 => "SOUND_RECORD_STAL",
			99 => "SOUND_RECORD_STRAD",
			100 => "SOUND_RECORD_WARD",
			101 => "SOUND_RECORD_11",
			102 => "SOUND_RECORD_WAIT",
			104 => "SOUND_GUARDIAN_FLOP",
			105 => "SOUND_ELDERGUARDIAN_CURSE",
			106 => "SOUND_MOB_WARNING",
			107 => "SOUND_MOB_WARNING_BABY",
			108 => "SOUND_TELEPORT",
			109 => "SOUND_SHULKER_OPEN",
			110 => "SOUND_SHULKER_CLOSE",
			111 => "SOUND_HAGGLE",
			112 => "SOUND_HAGGLE_YES",
			113 => "SOUND_HAGGLE_NO",
			114 => "SOUND_HAGGLE_IDLE",
			115 => "SOUND_CHORUSGROW",
			116 => "SOUND_CHORUSDEATH",
			117 => "SOUND_GLASS",
			118 => "SOUND_CAST_SPELL",
			119 => "SOUND_PREPARE_ATTACK",
			120 => "SOUND_PREPARE_SUMMON",
			121 => "SOUND_PREPARE_WOLOLO",
			122 => "SOUND_FANG",
			123 => "SOUND_CHARGE",
			124 => "SOUND_CAMERA_TAKE_PICTURE",
			125 => "SOUND_LEASHKNOT_PLACE",
			126 => "SOUND_LEASHKNOT_BREAK",
			127 => "SOUND_GROWL",
			128 => "SOUND_WHINE",
			129 => "SOUND_PANT",
			130 => "SOUND_PURR",
			131 => "SOUND_PURREOW",
			132 => "SOUND_DEATH_MIN_VOLUME",
			133 => "SOUND_DEATH_MID_VOLUME",
			134 => "SOUND_IMITATE_BLAZE",
			135 => "SOUND_IMITATE_CAVE_SPIDER",
			136 => "SOUND_IMITATE_CREEPER",
			137 => "SOUND_IMITATE_ELDER_GUARDIAN",
			138 => "SOUND_IMITATE_ENDER_DRAGON",
			139 => "SOUND_IMITATE_ENDERMAN",
			141 => "SOUND_IMITATE_EVOCATION_ILLAGER",
			142 => "SOUND_IMITATE_GHAST",
			143 => "SOUND_IMITATE_HUSK",
			144 => "SOUND_IMITATE_ILLUSION_ILLAGER",
			145 => "SOUND_IMITATE_MAGMA_CUBE",
			146 => "SOUND_IMITATE_POLAR_BEAR",
			147 => "SOUND_IMITATE_SHULKER",
			148 => "SOUND_IMITATE_SILVERFISH",
			149 => "SOUND_IMITATE_SKELETON",
			150 => "SOUND_IMITATE_SLIME",
			151 => "SOUND_IMITATE_SPIDER",
			152 => "SOUND_IMITATE_STRAY",
			153 => "SOUND_IMITATE_VEX",
			154 => "SOUND_IMITATE_VINDICATION_ILLAGER",
			155 => "SOUND_IMITATE_WITCH",
			156 => "SOUND_IMITATE_WITHER",
			157 => "SOUND_IMITATE_WITHER_SKELETON",
			158 => "SOUND_IMITATE_WOLF",
			159 => "SOUND_IMITATE_ZOMBIE",
			160 => "SOUND_IMITATE_ZOMBIE_PIGMAN",
			161 => "SOUND_IMITATE_ZOMBIE_VILLAGER",
			162 => "SOUND_BLOCK_END_PORTAL_FRAME_FILL",
			163 => "SOUND_BLOCK_END_PORTAL_SPAWN",
			164 => "SOUND_RANDOM_ANVIL_USE",
			165 => "SOUND_BOTTLE_DRAGONBREATH",
			166 => "SOUND_DEFAULT",
			167 => "SOUND_UNDEFINED"
		]
	];

	/**
	 *
	 * @param int $id
	 * @param int $protocol
	 *
	 * @return string|null
	 */
	public static function getTextPacketTypeId(int $id, int $protocol) : ?string{
		$key = $protocol;
		if(!isset(self::$textPacket[$key])){
			$key = "default";
		}
		if(!isset(self::$textPacket[$key][$id])){
			return null;
		}
		return self::$textPacket[$key][$id];
	}

	/**
	 *
	 * @param string $id
	 * @param int $protocol
	 *
	 * @return int|null
	 */
	public static function getTextPacketType(string $id, int $protocol) : ?int{
		$key = $protocol;
		if(!isset(self::$textPacket[$key])){
			$key = "default";
		}
		if(!in_array($id, self::$textPacket[$key])){
			return null;
		}
		return array_search($id, self::$textPacket[$key]);
	}

	/**
	 *
	 * @param int $id
	 * @param int $protocol
	 *
	 * @return string|null
	 */
	public static function getLevelSoundEventPacketTypeId(int $id, int $protocol) : ?string{
		$key = $protocol;
		if(!isset(self::$levelSoundEventPacket[$key])){
			$key = "default";
		}
		if(!isset(self::$levelSoundEventPacket[$key][$id])){
			return null;
		}
		return self::$levelSoundEventPacket[$key][$id];
	}

	/**
	 *
	 * @param string $id
	 * @param int $protocol
	 *
	 * @return int|null
	 */
	public static function getLevelSoundEventPacketType(string $id, int $protocol) : ?int{
		$key = $protocol;
		if(!isset(self::$levelSoundEventPacket[$key])){
			$key = "default";
		}
		if(!in_array($id, self::$levelSoundEventPacket[$key])){
			return null;
		}
		return array_search($id, self::$levelSoundEventPacket[$key]);
	}

}