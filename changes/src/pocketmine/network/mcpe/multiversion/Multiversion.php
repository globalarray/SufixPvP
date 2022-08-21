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

use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\v120\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\v120\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\v120\PlayerHotbarPacket;
use pocketmine\network\mcpe\protocol\v120\ProtocolInfo as ProtocolInfo120;
use pocketmine\Player;

class Multiversion{

	private static $packetTranslations = [
		ProtocolInfo::LOGIN_PACKET => ProtocolInfo120::LOGIN_PACKET,
		ProtocolInfo::PLAY_STATUS_PACKET => ProtocolInfo120::PLAY_STATUS_PACKET,
		ProtocolInfo::SERVER_TO_CLIENT_HANDSHAKE_PACKET => ProtocolInfo120::SERVER_TO_CLIENT_HANDSHAKE_PACKET,
		ProtocolInfo::CLIENT_TO_SERVER_HANDSHAKE_PACKET => ProtocolInfo120::CLIENT_TO_SERVER_HANDSHAKE_PACKET,
		ProtocolInfo::DISCONNECT_PACKET => ProtocolInfo120::DISCONNECT_PACKET,
		ProtocolInfo::RESOURCE_PACKS_INFO_PACKET => ProtocolInfo120::RESOURCE_PACKS_INFO_PACKET,
		ProtocolInfo::RESOURCE_PACK_STACK_PACKET => ProtocolInfo120::RESOURCE_PACK_STACK_PACKET,
		ProtocolInfo::RESOURCE_PACK_CLIENT_RESPONSE_PACKET => ProtocolInfo120::RESOURCE_PACK_CLIENT_RESPONSE_PACKET,
		ProtocolInfo::TEXT_PACKET => ProtocolInfo120::TEXT_PACKET,
		ProtocolInfo::SET_TIME_PACKET => ProtocolInfo120::SET_TIME_PACKET,
		ProtocolInfo::START_GAME_PACKET => ProtocolInfo120::START_GAME_PACKET,
		ProtocolInfo::ADD_PLAYER_PACKET => ProtocolInfo120::ADD_PLAYER_PACKET,
		ProtocolInfo::ADD_ENTITY_PACKET => ProtocolInfo120::ADD_ENTITY_PACKET,
		ProtocolInfo::REMOVE_ENTITY_PACKET => ProtocolInfo120::REMOVE_ENTITY_PACKET,
		ProtocolInfo::ADD_ITEM_ENTITY_PACKET => ProtocolInfo120::ADD_ITEM_ENTITY_PACKET,
		ProtocolInfo::ADD_HANGING_ENTITY_PACKET => ProtocolInfo120::ADD_HANGING_ENTITY_PACKET,
		ProtocolInfo::TAKE_ITEM_ENTITY_PACKET => ProtocolInfo120::TAKE_ITEM_ENTITY_PACKET,
		ProtocolInfo::MOVE_ENTITY_PACKET => ProtocolInfo120::MOVE_ENTITY_PACKET,
		ProtocolInfo::MOVE_PLAYER_PACKET => ProtocolInfo120::MOVE_PLAYER_PACKET,
		ProtocolInfo::RIDER_JUMP_PACKET => ProtocolInfo120::RIDER_JUMP_PACKET,
		ProtocolInfo::UPDATE_BLOCK_PACKET => ProtocolInfo120::UPDATE_BLOCK_PACKET,
		ProtocolInfo::ADD_PAINTING_PACKET => ProtocolInfo120::ADD_PAINTING_PACKET,
		ProtocolInfo::EXPLODE_PACKET => ProtocolInfo120::EXPLODE_PACKET,
		ProtocolInfo::LEVEL_SOUND_EVENT_PACKET => ProtocolInfo120::LEVEL_SOUND_EVENT_PACKET,
		ProtocolInfo::LEVEL_EVENT_PACKET => ProtocolInfo120::LEVEL_EVENT_PACKET,
		ProtocolInfo::BLOCK_EVENT_PACKET => ProtocolInfo120::BLOCK_EVENT_PACKET,
		ProtocolInfo::ENTITY_EVENT_PACKET => ProtocolInfo120::ENTITY_EVENT_PACKET,
		ProtocolInfo::MOB_EFFECT_PACKET => ProtocolInfo120::MOB_EFFECT_PACKET,
		ProtocolInfo::UPDATE_ATTRIBUTES_PACKET => ProtocolInfo120::UPDATE_ATTRIBUTES_PACKET,
		ProtocolInfo::MOB_EQUIPMENT_PACKET => ProtocolInfo120::MOB_EQUIPMENT_PACKET,
		ProtocolInfo::MOB_ARMOR_EQUIPMENT_PACKET => ProtocolInfo120::MOB_ARMOR_EQUIPMENT_PACKET,
		ProtocolInfo::INTERACT_PACKET => ProtocolInfo120::INTERACT_PACKET,
		ProtocolInfo::BLOCK_PICK_REQUEST_PACKET => ProtocolInfo120::BLOCK_PICK_REQUEST_PACKET,
		ProtocolInfo::PLAYER_ACTION_PACKET => ProtocolInfo120::PLAYER_ACTION_PACKET,
		ProtocolInfo::ENTITY_FALL_PACKET => ProtocolInfo120::ENTITY_FALL_PACKET,
		ProtocolInfo::HURT_ARMOR_PACKET => ProtocolInfo120::HURT_ARMOR_PACKET,
		ProtocolInfo::SET_ENTITY_DATA_PACKET => ProtocolInfo120::SET_ENTITY_DATA_PACKET,
		ProtocolInfo::SET_ENTITY_MOTION_PACKET => ProtocolInfo120::SET_ENTITY_MOTION_PACKET,
		ProtocolInfo::SET_ENTITY_LINK_PACKET => ProtocolInfo120::SET_ENTITY_LINK_PACKET,
		ProtocolInfo::SET_HEALTH_PACKET => ProtocolInfo120::SET_HEALTH_PACKET,
		ProtocolInfo::SET_SPAWN_POSITION_PACKET => ProtocolInfo120::SET_SPAWN_POSITION_PACKET,
		ProtocolInfo::ANIMATE_PACKET => ProtocolInfo120::ANIMATE_PACKET,
		ProtocolInfo::RESPAWN_PACKET => ProtocolInfo120::RESPAWN_PACKET,
		ProtocolInfo::CONTAINER_OPEN_PACKET => ProtocolInfo120::CONTAINER_OPEN_PACKET,
		ProtocolInfo::CONTAINER_CLOSE_PACKET => ProtocolInfo120::CONTAINER_CLOSE_PACKET,
		ProtocolInfo::CONTAINER_SET_DATA_PACKET => ProtocolInfo120::CONTAINER_SET_DATA_PACKET,
		ProtocolInfo::CRAFTING_DATA_PACKET => ProtocolInfo120::CRAFTING_DATA_PACKET,
		ProtocolInfo::CRAFTING_EVENT_PACKET => ProtocolInfo120::CRAFTING_EVENT_PACKET,
		ProtocolInfo::ADVENTURE_SETTINGS_PACKET => ProtocolInfo120::ADVENTURE_SETTINGS_PACKET,
		ProtocolInfo::BLOCK_ENTITY_DATA_PACKET => ProtocolInfo120::BLOCK_ENTITY_DATA_PACKET,
		ProtocolInfo::PLAYER_INPUT_PACKET => ProtocolInfo120::PLAYER_INPUT_PACKET,
		ProtocolInfo::FULL_CHUNK_DATA_PACKET => ProtocolInfo120::FULL_CHUNK_DATA_PACKET,
		ProtocolInfo::SET_COMMANDS_ENABLED_PACKET => ProtocolInfo120::SET_COMMANDS_ENABLED_PACKET,
		ProtocolInfo::SET_DIFFICULTY_PACKET => ProtocolInfo120::SET_DIFFICULTY_PACKET,
		ProtocolInfo::CHANGE_DIMENSION_PACKET => ProtocolInfo120::CHANGE_DIMENSION_PACKET,
		ProtocolInfo::SET_PLAYER_GAME_TYPE_PACKET => ProtocolInfo120::SET_PLAYER_GAME_TYPE_PACKET,
		ProtocolInfo::PLAYER_LIST_PACKET => ProtocolInfo120::PLAYER_LIST_PACKET,
		ProtocolInfo::SIMPLE_EVENT_PACKET => ProtocolInfo120::SIMPLE_EVENT_PACKET,
		ProtocolInfo::EVENT_PACKET => ProtocolInfo120::EVENT_PACKET,
		ProtocolInfo::SPAWN_EXPERIENCE_ORB_PACKET => ProtocolInfo120::SPAWN_EXPERIENCE_ORB_PACKET,
		ProtocolInfo::CLIENTBOUND_MAP_ITEM_DATA_PACKET => ProtocolInfo120::CLIENTBOUND_MAP_ITEM_DATA_PACKET,
		ProtocolInfo::MAP_INFO_REQUEST_PACKET => ProtocolInfo120::MAP_INFO_REQUEST_PACKET,
		ProtocolInfo::REQUEST_CHUNK_RADIUS_PACKET => ProtocolInfo120::REQUEST_CHUNK_RADIUS_PACKET,
		ProtocolInfo::CHUNK_RADIUS_UPDATED_PACKET => ProtocolInfo120::CHUNK_RADIUS_UPDATED_PACKET,
		ProtocolInfo::ITEM_FRAME_DROP_ITEM_PACKET => ProtocolInfo120::ITEM_FRAME_DROP_ITEM_PACKET,
		ProtocolInfo::GAME_RULES_CHANGED_PACKET => ProtocolInfo120::GAME_RULES_CHANGED_PACKET,
		ProtocolInfo::CAMERA_PACKET => ProtocolInfo120::CAMERA_PACKET,
		ProtocolInfo::BOSS_EVENT_PACKET => ProtocolInfo120::BOSS_EVENT_PACKET,
		ProtocolInfo::SHOW_CREDITS_PACKET => ProtocolInfo120::SHOW_CREDITS_PACKET,
		ProtocolInfo::AVAILABLE_COMMANDS_PACKET => ProtocolInfo120::AVAILABLE_COMMANDS_PACKET,
		ProtocolInfo::COMMAND_BLOCK_UPDATE_PACKET => ProtocolInfo120::COMMAND_BLOCK_UPDATE_PACKET,
		ProtocolInfo::UPDATE_TRADE_PACKET => ProtocolInfo120::UPDATE_TRADE_PACKET,
		ProtocolInfo::UPDATE_EQUIP_PACKET => ProtocolInfo120::UPDATE_EQUIP_PACKET,
		ProtocolInfo::RESOURCE_PACK_DATA_INFO_PACKET => ProtocolInfo120::RESOURCE_PACK_DATA_INFO_PACKET,
		ProtocolInfo::RESOURCE_PACK_CHUNK_DATA_PACKET => ProtocolInfo120::RESOURCE_PACK_CHUNK_DATA_PACKET,
		ProtocolInfo::RESOURCE_PACK_CHUNK_REQUEST_PACKET => ProtocolInfo120::RESOURCE_PACK_CHUNK_REQUEST_PACKET,
		ProtocolInfo::TRANSFER_PACKET => ProtocolInfo120::TRANSFER_PACKET,
		ProtocolInfo::PLAY_SOUND_PACKET => ProtocolInfo120::PLAY_SOUND_PACKET,
		ProtocolInfo::STOP_SOUND_PACKET => ProtocolInfo120::STOP_SOUND_PACKET,
		ProtocolInfo::SET_TITLE_PACKET => ProtocolInfo120::SET_TITLE_PACKET,
		ProtocolInfo::ADD_BEHAVIOR_TREE_PACKET => ProtocolInfo120::ADD_BEHAVIOR_TREE_PACKET,
		ProtocolInfo::STRUCTURE_BLOCK_UPDATE_PACKET => ProtocolInfo120::STRUCTURE_BLOCK_UPDATE_PACKET,
		ProtocolInfo::SHOW_STORE_OFFER_PACKET => ProtocolInfo120::SHOW_STORE_OFFER_PACKET,
		ProtocolInfo::PURCHASE_RECEIPT_PACKET => ProtocolInfo120::PURCHASE_RECEIPT_PACKET,
		0xfe => 0xfe //BatchPacket
	];

	/**
	 * @param int $pid
	 *
	 * @return int|null
	 */
	public static function convertPID(int $pid) : ?int{
		if(isset(self::$packetTranslations[$pid])){
			return self::$packetTranslations[$pid];
		}
		return null;
	}

	/**
	 * @param int $pid
	 *
	 * @return int|null
	 */
	public static function convertPIDBack(int $pid) : ?int{
		if(in_array($pid, self::$packetTranslations)){
			return array_search($pid, self::$packetTranslations);
		}
		return null;
	}

	/**
	 * @param DataPacket $packet
	 * @param Player $player
	 *
	 * @return DataPacket[]
	 */
	public static function convertTo120(DataPacket $packet, Player $player) : array{
		switch($packet->pid()){
			case ProtocolInfo::CONTAINER_SET_CONTENT_PACKET:
				$pk = new InventoryContentPacket();
				$pk->protocol = ProtocolInfo::MULTIVERSION_PROTOCOL;
				$pk->windowId = $packet->windowid;
				$pk->items = $packet->slots;
				if(count($packet->hotbar) > 0){
					$pk1 = new PlayerHotbarPacket();
					$pk1->protocol = ProtocolInfo::MULTIVERSION_PROTOCOL;
					$pk1->windowId = $packet->windowid;
					$pk1->selectedHotbarSlot = $player->getInventory()->getHeldItemIndex();
					$pk1->slots = $packet->hotbar;
					return [$pk, $pk1];
				}
				return [$pk];
			case ProtocolInfo::CONTAINER_SET_SLOT_PACKET:
				$pk = new InventorySlotPacket();
				$pk->protocol = ProtocolInfo::MULTIVERSION_PROTOCOL;
				$pk->windowId = $packet->windowid;
				$pk->inventorySlot = $packet->slot;
				$pk->item = $packet->item;
				return [$pk];
		}
		if(self::convertPID($packet->pid()) !== null){
			$pk = clone $packet;
			$pk->protocol = ProtocolInfo::MULTIVERSION_PROTOCOL;
			$pk->reset();
			$pk->isEncoded = false;
			return [$pk];
		}
		return [];
	}

}
