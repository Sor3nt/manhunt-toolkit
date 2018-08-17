<?php
namespace App\Service\Compiler\FunctionMap;

class Manhunt
{


    /**
     * some functions need explicit float parameters
     * when someone give a 10 instead of 10.0 we need to
     * tell te engine to convert the value
     *
     * Sample Input: SetVector(pos, 1, 23.45, 67.89);
     * the function require 3 floats but the first value is a int.
     * convert it to a float with 0x4d 0x10 0x01
     */
    public static $functionForceFloar = [
    ];

    public static $functionNoReturn = [
        'getentityname'
    ];

    public static $functionEventDefinition = [
        '__default__' => '54000000'
    ];

    public static $constants = [
        'CT_TRIPWIRE' => [
            'offset' => "01000000"
        ],
        'CT_GASOLINE' => [
            'offset' => "02000000"
        ],
        'CT_WATER' => [
            'offset' => "03000000"
        ],
        'CT_LIGHTER' => [
            'offset' => "04000000"
        ],
        'CT_CASH' => [
            'offset' => "05000000"
        ],
        'CT_TORCH' => [
            'offset' => "06000000"
        ],
        'CT_N_NIGHTVISION' => [
            'offset' => "07000000"
        ],
        'CT_PAINKILLERS' => [
            'offset' => "08000000"
        ],
        'CT_G_FIRST_AID' => [
            'offset' => "09000000"
        ],
        'CT_Y_FIRST_AID' => [
            'offset' => "0A000000"
        ],
        'CT_SPEED_BOOST' => [
            'offset' => "0B000000"
        ],
        'CT_STRENGHT_BOOST' => [
            'offset' => "0C000000"
        ],
        'CT_SHOOTING_BOOST' => [
            'offset' => "0D000000"
        ],
        'CT_REFLEXES_BOOST' => [
            'offset' => "0E000000"
        ],
        'CT_HEALTH_BOOST' => [
            'offset' => "0F000000"
        ],
        'CT_FISTS' => [
            'offset' => "12000000"
        ],
        'CT_KNIFE' => [
            'offset' => "13000000"
        ],
        'CT_SHARD' => [
            'offset' => "13000000"
        ],
        'CT_BROKEN_BOTTLE' => [
            'offset' => "14000000"
        ],
        'CT_JURYBLADES' => [
            'offset' => "15000000"
        ],
        'CT_BOTTLE' => [
            'offset' => "16000000"
        ],
        'CT_PIPE' => [
            'offset' => "17000000"
        ],
        'CT_CLEAVER' => [
            'offset' => "18000000"
        ],
        'CT_WOODEN_BAR' => [
            'offset' => "19000000"
        ],
        'CT_CROWBAR' => [
            'offset' => "1A000000"
        ],
        'CT_AXE' => [
            'offset' => "1E000000"
        ],
        'CT_ICEPICK' => [
            'offset' => "1F000000"
        ],
        'CT_MACHETE' => [
            'offset' => "20000000"
        ],
        'CT_SMALL_BAT' => [
            'offset' => "21000000"
        ],
        'CT_BASEBALL_BAT' => [
            'offset' => "22000000"
        ],
        'CT_W_BASEBALL_BAT' => [
            'offset' => "23000000"
        ],
        'CT_FIRE_AXE' => [
            'offset' => "24000000"
        ],
        'CT_BASEBALL_BAT_BLADES' => [
            'offset' => "26000000"
        ],
        'CT_6SHOOTER' => [
            'offset' => "27000000"
        ],
        'CT_GLOCK' => [
            'offset' => "28000000"
        ],
        'CT_GLOCK_SILENCED' => [
            'offset' => "29000000"
        ],
        'CT_GLOCK_TORCH' => [
            'offset' => "2A000000"
        ],
        'CT_UZI' => [
            'offset' => "2B000000"
        ],
        'CT_SHOTGUN' => [
            'offset' => "2C000000"
        ],
        'CT_SHOTGUN_TORCH' => [
            'offset' => "2D000000"
        ],
        'CT_COLT_COMMANDO' => [
            'offset' => "2F000000"
        ],
        'CT_DESERT_EAGLE' => [
            'offset' => "2E000000"
        ],
        'CT_SNIPER_RIFLE' => [
            'offset' => "30000000"
        ],
        'CT_SNIPER_RIFLE_SILENCED' => [
            'offset' => "30000000"
        ],
        'CT_TRANQ_RIFLE' => [
            'offset' => "31000000"
        ],
        'CT_SAWNOFF' => [
            'offset' => "32000000"
        ],
        'CT_GRENADE' => [
            'offset' => "33000000"
        ],
        'CT_MOLOTOV' => [
            'offset' => "34000000"
        ],
        'CT_EXPMOLOTOV' => [
            'offset' => "35000000"
        ],
        'CT_TEAR_GAS' => [
            'offset' => "36000000"
        ],
        'CT_FLASH' => [
            'offset' => "37000000"
        ],
        'CT_BRICK_HALF' => [
            'offset' => "38000000"
        ],
        'CT_FIREWORK' => [
            'offset' => "39000000"
        ],
        'CT_CAN' => [
            'offset' => "5B000000"
        ],
        'CT_RAG' => [
            'offset' => "3B000000"
        ],
        'CT_CHLORINE' => [
            'offset' => "3B000000"
        ],
        'CT_METHS' => [
            'offset' => "3B000000"
        ],
        'CT_HCC' => [
            'offset' => "3E000000"
        ],
        'CT_D_BEER_GUY' => [
            'offset' => "3F000000"
        ],
        'CT_D_MERC_LEAD' => [
            'offset' => "40000000"
        ],
        'CT_D_SMILEY' => [
            'offset' => "41000000"
        ],
        'CT_D_HUNTLORD' => [
            'offset' => "42000000"
        ],
        'CT_CANE' => [
            'offset' => "1D000000"
        ],
        'CT_NIGHTSTICK' => [
            'offset' => "1C000000"
        ],
        'CT_K_DUST' => [
            'offset' => "11000000"
        ],
        'CT_E_L_SIGHT' => [
            'offset' => "43000000"
        ],
        'CT_S_SILENCER' => [
            'offset' => "44000000"
        ],
        'CT_RADIO' => [
            'offset' => "45000000"
        ],
        'CT_BAR_KEY' => [
            'offset' => "46000000"
        ],
        'CT_SYARD_COMB' => [
            'offset' => "47000000"
        ],
        'CT_CAMERA' => [
            'offset' => "48000000"
        ],
        'CT_BODY_P1' => [
            'offset' => "49000000"
        ],
        'CT_BODY_P2' => [
            'offset' => "4A000000"
        ],
        'CT_PREC_KEY' => [
            'offset' => "4B000000"
        ],
        'CT_PREC_DOCS' => [
            'offset' => "4C000000"
        ],
        //  for hunters!
        'CT_CHAINSAW' => [
            'offset' => "58000000"
        ],
        'CT_CHAINSAW_PLAYER' => [
            'offset' => "6C000000"
        ],
        'CT_BAG' => [
            'offset' => "3A000000"
        ],
        'CT_WIRE' => [
            'offset' => "5A000000"
        ],
        'CT_WOODEN_SPIKE' => [
            'offset' => "5C000000"
        ],
        'CT_PIGSY_WIRE' => [
            'offset' => "5F000000"
        ],
        'CT_PIGSY_SHARD' => [
            'offset' => "5E000000"
        ],
        'CT_PIGSY_SPIKE' => [
            'offset' => "60000000"
        ],
        'CT_HAMMER' => [
            'offset' => "61000000"
        ],
        'CT_KEY' => [
            'offset' => "53000000"
        ],
        'CT_NAILGUN' => [
            'offset' => "59000000"
        ],
        'CT_HANDYCAM' => [
            'offset' => "6E000000"
        ],
        // Ammo
        'CT_AMMO_NAILS' => [
            'offset' => "66000000"
        ],
        'CT_AMMO_SHOTGUN' => [
            'offset' => "67000000"
        ],
        'CT_AMMO_PISTOL' => [
            'offset' => "68000000"
        ],
        'CT_AMMO_MGUN' => [
            'offset' => "69000000"
        ],
        'CT_AMMO_TRANQ' => [
            'offset' => "6A000000"
        ],

        'CT_NO_ITEM' => [
            'offset' => "6F000000"
        ],

    ];

    public static $functions = [



        'settimer' => [
            'name' => 'SetTimer',
            'offset' => "ce020000",
            /**
             * Parameters
             * 1: Minutes
             * 2: Seconds
             */
            'params' => ['Integer', 'Integer'],
            'return' => 'Void',
            'desc' => ''
        ],

        'starttimer' => [
            'name' => 'StartTimer',
            'offset' => "cf020000"
        ],

        'stoptimer' => [
            'name' => 'StopTimer',
            'offset' => "d0020000"
        ],

        'showtimer' => [
            'name' => 'ShowTimer',
            'offset' => "d2020000"
        ],

        'hidetimer' => [
            'name' => 'HideTimer',
            'offset' => "d3020000"
        ],

        'setlevelfailed' => [
            'name' => 'SetLevelFailed',
            'offset' => "8b020000"
        ],

        'hudtoggleflashflags' => [
            'name' => 'HUDToggleFlashFlags',
            'offset' => "af020000",
            /**
             * Parameters
             * 1:  Hud elem
             * 2:  on/off
             */
            'params' => ['Integer', 'Integer'],
            'return' => 'Void',
            'desc' => ''
        ],

        "displaygametext" => [
            'name' => 'DisplayGameText',
            'offset' => "03010000",
            'params' => ['String']

        ],

        'setnumberofkillablehuntersinlevel' => [
            'name' => 'SetNumberOfKillableHuntersInLevel',
            'offset' => "e6020000",
            /**
             * Parameters
             * 1:  to kill
             * 2:  to execute
             */
            'params' => ['Integer', 'Integer'],
            'return' => 'Void',
            'desc' => ''
        ],
        'handcamsetvideoeffecttimecode' => [
            'name' => 'HandCamSetVideoEffectTimeCode',
            'offset' => "5f020000",
            'params' => ['Integer'],
            'desc' => ''
        ],
        'handcamsetvideoeffectrecorddot' => [
            'name' => 'HandCamSetVideoEffectRecordDot',
            'offset' => "60020000",
            'params' => ['Integer'],
            'desc' => ''
        ],
        'handcamsetvideoeffectfuzz' => [
            'name' => 'HandCamSetVideoEffectFuzz',
            'offset' => "61020000",
            'params' => ['Integer'],
            'desc' => ''
        ],
        'handcamsetvideoeffectscrollbar' => [
            'name' => 'HandCamSetVideoEffectScrollBar',
            'offset' => "62020000",
            'params' => ['Integer'],
            'desc' => ''
        ],
        'aiaddplayer' => [
            'name' => 'AIAddPlayer',
            'offset' => "5a010000",
            'params' => ['String'],
            'desc' => ''
        ],
        'setlevelgoal' => [
            'name' => 'SetLevelGoal',
            'offset' => "3e020000",
            'params' => ['String'],
            'desc' => ''
        ],
        'createinventoryitem' => [
            'name' => 'CreateInventoryItem',
            'offset' => "b9000000",
        ],

        'ispadbuttonpressed' => [
            'name' => 'IsPadButtonPressed',
            'offset' => "f9000000",
            'params' => ['Integer']
        ],

        'aiaddentity' => [
            'name' => 'AIAddEntity',
            'offset' => "4c010000"
        ],
        'aisetentityasleader' => [
            'name' => 'AISetEntityAsLeader',
            'offset' => "4e010000"
        ],
        'aiaddleaderenemy' => [
            'name' => 'AIAddLeaderEnemy',
            'offset' => "53010000"
        ],
        'aientityalwaysenabled' => [
            'name' => 'AIEntityAlwaysEnabled',
            'offset' => "be010000"
        ],
        'aisethunteronradar' => [
            'name' => 'AISetHunterOnRadar',
            'offset' => "a7010000"
        ],
        'getentity' => [
            'name' => 'GetEntity',
            'offset' => "76000000"
        ],

        "getplayerposition" => [
            'name' => 'GetPlayerPosition',
            'offset' => '8a000000',

            'params' => [],
            'return' => 'Void',
            'desc' => ''
        ],
        "setvector" => [
            'name' => 'SetVector',
            'offset' => '83010000'
        ],
        "moveentity" => [
            'name' => 'MoveEntity',
            'offset' => '7c000000',
            /**
             * Parameters
             * 1: result of GetEntity
             * 2: ref to vec3d (3x float)
             * 3: integer
             */
            'params' => ['Entity', 'Vec3D', 'Integer'],
            'desc' => ''
        ],
    ];

//

}
