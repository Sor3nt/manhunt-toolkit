<?php
namespace App\Service\Archive;

use App\Service\Archive\Inst\Build;
use App\Service\Archive\Inst\Extract;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Inst extends Archive {

    public $name = 'Entity Positions';

    /**
     * Thanks to ERMACCER
     */
    public static $weapon = [
        "pipe" => 0,
        "cleaver" => 1,
        "wooden baseball bat" => 2,
        "knife" => 3,
        "baseball bat 1" => 4,
        "baseball bat 2" => 5,
        "not defined" => 6,
        "crowbar" => 7,
        "small bat" => 8,
        "nightstick" => 9,
        "axe" => 10,
        "icepick" => 11,
        "machete" => 12,
        "sickle" => 13,
        "baseball bat 3" => 14,
        "spiked bat" => 15,
        "chainsaw" => 16,
        "syringe" => 17,
        "shovel" => 18,
        "sledgehammer" => 19,
        "stunprod" => 20,
        "pen" => 21,
        "acid bottle" => 22,
        "1h firearm" => 23,
        "2h firearm" => 24,
        "razor" => 25,
        "blowtorch" => 26,
        "mace" => 27,
        "hedge_trimmer" => 28,
        "metal_hook" => 29,
        "circular_saw" => 30,
        "pliers" => 31,
        "torch" => 32,
        "newspaper" => 33,
        "milkbottle" => 33,
        "dildo" => 35,
        "katana" => 36,
        "hacksaw" => 37,
        "fire_axe" => 38,
    ];

    public static $weapon2 = [
        "glock" => 0,
        "glock 2" => 1,
        "desert_eagle" => 2,
        "shotgun" => 3,
        "uzi" => 4,
        "colt_commando" => 5,
        "sniper_rifle" => 6,
        "not defined" => 7,
        "nailgun" => 8,
        "6shooter" => 9,
        "sawnoff" => 10,
        "tranq_rifle" => 11,
        "shotgun_torch" => 12,
        "sniper_rifle 2" => 13,
        "flaregun" => 14,
        "crossbow" => 15,
        "uzi_torch" => 16,
    ];

    /*
     *
    public static $slots = [
        "BACK" => 1,
        "BELT_LEFT" => 3,
        "BELT_RIGHT" => 2,
        "BELT_REAR" => 4,
        "SPECIAL" => 5,
    ];
     */

    /**
     * Thanks to MAJESTIC_R5
     */
    public static $mh1Map = [
        'Base_Inst' => [
            'Hit Points' => NBinary::INT_32
        ],

        'Player_Inst' => [
            'Hit Points' => NBinary::INT_32
        ],

        'Trigger_Inst' => [
            'Type' => NBinary::INT_32,
            'Size' => NBinary::FLOAT_32,
        ],

        'Door_Inst' => [
            'Hit Points' => NBinary::INT_32,
            'Unknown' => NBinary::INT_32,
            'Flags' => NBinary::INT_32,
        ],

        'Hunter_Inst' => [
            'Hit Points' => NBinary::INT_32,
            'Slot 1' => NBinary::INT_32,
            'Slot 2' => NBinary::INT_32,
            'Slot 3' => NBinary::INT_32,
            'Weapon' => NBinary::INT_32,
            'Weapon2' => NBinary::INT_32,
            'AI Type' => NBinary::INT_32,
            'Drop Ammo' => NBinary::INT_32,
            'Flags' => NBinary::INT_32,
        ],

        'Light_Inst' => [
            'Type' => NBinary::INT_32,
            'Cone Angle' => NBinary::INT_32,
            'Radius' => NBinary::INT_32,
            'Red' => NBinary::FLOAT_32,
            'Green' => NBinary::FLOAT_32,
            'Blue' => NBinary::FLOAT_32,
            'Flags 1' => NBinary::INT_32,
            'Flags 2' => NBinary::INT_32,
            'Flags 3' => NBinary::INT_32,
            'Flags 4' => NBinary::INT_32,
            'Flags 5' => NBinary::INT_32,
            'Flags 6' => NBinary::INT_32,
            'Flags 7' => NBinary::INT_32,
            'Size' => NBinary::INT_32,
            'Intensity' => NBinary::INT_32,
        ]

    ];

    public static $supported = [
        'entity.inst',
        'entity2.inst',
        'entity_pc.inst',
        'entity_wii.inst',
        'entinst.bin',
        'entinst2.bin'
    ];


    /**
     * @param $pathFilename
     * @param $input
     * @param $game
     * @param $platform
     * @return bool
     */
    public static function canPack( $pathFilename, $input, $game, $platform ){

        if (!$input instanceof Finder) return false;

        foreach ($input as $file) {
            $ext = strtolower($file->getExtension());
            if ($ext !== "json") return false;

            $content = $file->getContents();
            return strpos($content, 'record') !== false &&
                strpos($content, 'internalName') !== false &&
                strpos($content, 'entityClass') !== false;
        }

        return false;
    }

    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     */
    public function unpack(NBinary $binary, $game, $platform){
        return (new Extract())->get($binary);
    }

    /**
     * @param Finder $pathFilename
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack( $pathFilename, $game, $platform){
        return (new Build())->build( $pathFilename, $platform );
    }


    public static function getWeaponNameById( $id ){
        foreach (self::$weapon as $name => $weaponId) {
            if ($id == $weaponId) return $name;
        }

        return 0;
    }

    public static function getWeapon2NameById( $id ){
        foreach (self::$weapon2 as $name => $weaponId) {
            if ($id == $weaponId) return $name;
        }

        return 0;
    }

}
