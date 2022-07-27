<?php

/*
 *
 * ╔═══╗───╔═╗───╔═══╗
 * ║╔═╗║───║╔╝───║╔══╝
 * ║╚══╦╗╔╦╝╚╦╦╗╔╣╚══╦═╗╔══╦╦═╗╔══╗
 * ╚══╗║║║╠╗╔╬╬╬╬╣╔══╣╔╗╣╔╗╠╣╔╗╣║═╣
 * ║╚═╝║╚╝║║║║╠╬╬╣╚══╣║║║╚╝║║║║║║═╣
 * ╚═══╩══╝╚╝╚╩╝╚╩═══╩╝╚╩═╗╠╩╝╚╩══╝
 * ─────────────────────╔═╝║
 * ─────────────────────╚══╝
 *
 * @author David Ratnikov
 * @link https://vk.com/showyouass
 *
 */

declare(strict_types=1);

namespace ddosnik\menu;

class ClickableItemFactory {

	public static $list = null;

	public static function init() : void{
		if (self::$list === null) {
			self::$list = [];
			
			self::$list['join_arena'] = JoinArenaItem::class;
			self::$list['item_reborn'] = RebornItem::class;
			self::$list['ffa_gapple'] = JoinArenaGappleItem::class;
			self::$list['ffa_fist'] = JoinArenaFistItem::class;
			self::$list['ffa_resistance'] = JoinArenaResistanceItem::class;
			self::$list['item_quit'] = QuitItem::class;
		}
	}

	/**
	 *
	 * @param string             $identifier
	 * @param int                $meta
	 * @param int                $count
	 * @param CompoundTag|string $tags
	 *
	 * @return ClickableItem
	 */
	public static function get(string $identifier, int $meta = 0, int $count = 1) : ClickableItem{
		$class = self::$list[$identifier];
		if ($class === null) {
			//return (new Item::get(0, 0, 0));
		} else {
			return (new $class($meta, $count));
		}
	}
}