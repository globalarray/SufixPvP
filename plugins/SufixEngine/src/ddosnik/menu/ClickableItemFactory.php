<?php

declare(strict_types=1);

namespace ddosnik\menu;

use pocketmine\utils\CloningRegistryTrait;

 /**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static JoinArenaItem JOIN_ARENA()
 * @method static RebornItem REBORN()
 * @method static JoinArenaGappleItem FFA_GAPPLE()
 * @method static JoinArenaFistItem FFA_FIST()
 * @method static JoinArenaResistanceItem FFA_RESISTANCE()
 * @method static BackToMenuItem BACK_MENU()
 * @method static CloaksListItem CLOAKS()
 * @method static DragonCloakItem DRAGON_CLOAK()
 * @method static GolemCloakItem GOLEM_CLOAK()
 * @method static PistonCloakItem PISTON_CLOAK()
 * @method static PickaxeCloakItem PICKAXE_CLOAK()
 * @method static CreeperCloakItem CREEPER_CLOAK()
 * @method static QuitToLobbyItem QUIT_LOBBY()
 * @method static ChangeTimeItem CHANGE_TIME()
 * @method static SetMorningTimeItem TIME_MORNING()
 * @method static SetDayTimeItem TIME_DAY()
 * @method static SetEveningTimeItem TIME_EVENING()
 * @method static CustomizationItem CUSTOMIZATION()
 * @method static ChangeColorNicknameItem CHANGE_COLOR()
 * @method static YellowColorNameItem YELLOW_COLOR()
 * @method static BlueColorNameItem BLUE_COLOR()
 * @method static RedColorNameItem RED_COLOR()
 * @method static GreenColorNameItem GREEN_COLOR()
 * @method static ParticlesListItem PARTICLES()
 * @method stasic HeartParticleItem HEART_PARTICLE()
 * @method static HappyParticleItem HAPPY_PARTICLE()
 * @method static RainParticleItem RAIN_PARTICLE()
 * @method static FlameParticleItem FLAME_PARTICLE()
 */
 

final class ClickableItemFactory {
	use CloningRegistryTrait;

	private function __construct(){
		//NOOP
	}

	protected static function register(string $name, $item) : void{
		self::_registryRegister($name, $item);
	}

	/**
	 * @return ClickableItem[]
	 */
	public static function getAll() : array{
		/** @var ClickableItem[] $result */
		$result = self::_registryGetAll();
		return $result;
	}

	protected static function setup() : void{
		self::register('join_arena', new JoinArenaItem());
		self::register('reborn', new RebornItem());
		self::register('ffa_gapple', new JoinArenaGappleItem());
		self::register('ffa_fist', new JoinArenaFistItem());
		self::register('ffa_resistance', new JoinArenaResistanceItem());
		self::register('back_menu', new BackToMenuItem());
		self::register('cloaks', new CloaksListItem());
		self::register('dragon_cloak', new DragonCloakItem());
		self::register('golem_cloak', new GolemCloakItem());
		self::register('piston_cloak', new PistonCloakItem());
		self::register('pickaxe_cloak', new PickaxeCloakItem());
		self::register('creeper_cloak', new CreeperCloakItem());
		self::register('quit_lobby', new QuitToLobbyItem());
		self::register('change_time', new ChangeTimeItem());
		self::register('time_morning', new SetMorningTimeItem());
		self::register('time_day', new SetDayTimeItem());
		self::register('time_evening', new SetEveningTimeItem());
		self::register('customization', new CustomizationItem());
		self::register('change_color', new ChangeColorNicknameItem());
		self::register('yellow_color', new YellowColorNameItem());
		self::register('blue_color', new BlueColorNameItem());
		self::register('red_color', new RedColorNameItem());
		self::register('green_color', new GreenColorNameItem());
		self::register('particles', new ParticlesListItem());
		self::register('heart_particle', new HeartParticleItem());
		self::register('happy_particle', new HappyParticleItem());
		self::register('rain_particle', new RainParticleItem());
		self::register('flame_particle', new FlameParticleItem());
	}
}