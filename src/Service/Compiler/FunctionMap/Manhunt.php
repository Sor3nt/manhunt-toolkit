<?php
namespace App\Service\Compiler\FunctionMap;

class Manhunt {


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
        'SetEntityFade' => [ false, true, true],
        'SetColourRamp' => [ false, false, true],
        'setpedorientation' => [ false, true],
        'setvector' => [ false, true, true, true ],
        'aisethunteridleactionminmaxradius' => [ false, false, false, false, false, true]
    ];

    public static $functionNoReturn = [
        'getentityname'
    ];

    public static $functionEventDefinition = [

        'oncreate' => '00000000',
        'ondestroy' => '01000000',
        'ondamage' => '02000000',
        'onusebyplayer' => '03000000',
        'onentertrigger' => '04000000',
        'onleavetrigger' => '05000000',
        'onmediumsightingorabove' => '1d000000',
        'onmediumhearingorabove' => '33000000',
        'ondeath' => '1e000000',
        'onlowhearingorabove' => '35000000',
        'onfocus' => '58000000',
        'ontimerended' => '4d000000',

        '__default__' => '68000000'
    ];

    public static $constants = [

        'HUD_MAP'  => [
            'offset' => "01000000"
        ],

        'HUD_MAN'  => [
            'offset' => "02000000"
        ],

        'HUD_HEALTH'  => [
            'offset' => "04000000"
        ],


        'HUD_STAMINA'  => [
            'offset' => "08000000"
        ],

        'HUD_INVENTORY'  => [
            'offset' => "10000000"
        ],



        'AISCRIPT_MEDIUMPRIORITY'  => [
            'offset' => "02000000"
        ],

        'AISCRIPT_IDLE_STANDANIMS'  => [
            'offset' => "05000000"
        ],

        'AISCRIPT_IDLE_WANDERSEARCH'  => [
            'offset' => "00000000"
        ],

        'MTT_HOOD_MEDIUM'  => [
            'offset' => "02000000"
        ],

        'AISCRIPT_WALKMOVESPEED'  => [
            'offset' => "01000000"
        ],


        'COL_PLAYER'  => [
            'offset' => "00020000"
        ],


        'MAP_COLOR_YELLOW'  => [
            'offset' => "04000000"
        ],

        'MAP_COLOR_RED'  => [
            'offset' => "02000000"
        ],

        'HID_RADAR'  => [
            'offset' => "02000000"
        ],

        'MAP_COLOR_LOCATION'  => [
            'offset' => "14000000"
        ],

        'MAP_COLOR_HUNTER_IDLE'  => [
            'offset' => "08000000"
        ],

        'COL_HUNTER'  => [
            'offset' => "10000000"
        ],

        'AISCRIPT_VERYHIGHPRIORITY'  => [
            'offset' => "00000000"
        ],

        'AISCRIPT_LOWPRIORITY'  => [
            'offset' => "03000000"
        ],

        'AISCRIPT_IDLE_STANDSTILL'  => [
            'offset' => "02000000"
        ],

        'COMBATTYPEID_MELEE'  => [
            'offset' => "00000000"
        ],

        'MTT_TRAINING'  => [
            'offset' => "00000000"
        ],

        'DIFFICULTY_NORMAL'  => [
            'offset' => "01000000"
        ],

        'MAP_COLOR_BLUE'  => [
            'offset' => "06000000"
        ],

        'COMBATTYPEID_OPEN_MELEE'  => [
            'offset' => "03000000"
        ],


        'COMBATTYPEID_COVER'  => [
            'offset' => "02000000"
        ],

        'AISCRIPT_GRAPHLINK_ALLOW_NOTHING'  => [
            'offset' => "00000000"
        ],

        'HID_ALL_PLAYER_ITEMS'  => [
            'offset' => "2c010000"
        ],

        'AISCRIPT_GRAPHLINK_ALLOW_EVERYTHING' => [
            "offset" => "03000000"
        ],

        'AISCRIPT_IDLE_PATROL' => [
            "offset" => "01000000"
        ],

        'AISCRIPT_HIGHPRIORITY' => [
            "offset" => "01000000"
        ],

        'MOVER_STOPPED' => [
            "offset" => "00000000"
        ],

        'MOVER_FORWARD' => [
            "offset" => "01000000"
        ],

        'AISCRIPT_RUNMOVESPEED' => [
            "offset" => "00000000"
        ],

        'DOOR_OPEN' => [
            "offset" => "00000000"
        ],

        'DOOR_OPENING' => [
            "offset" => "01000000"
        ],

        'DOOR_CLOSED' => [
            "offset" => "02000000"
        ],

        'DOOR_CLOSING' => [
            "offset" => "03000000"
        ],

        'CT_G_FIRST_AID' => [
            "offset" => "09000000"
        ],

        'CT_SYRINGE' => [
            "offset" => "71000000"
        ],

    ];

    public static $functions = [

         'settimer'  => [
            'name'      =>  'SetTimer',
            'offset' => "ce020000",
             /**
             * Parameters
             * 1: Minutes
             * 2: Seconds
             */
            'params'    =>  [ 'Integer','Integer' ],
            'return'    =>  'Void',
            'desc'      =>  ''
        ],
         
        'starttimer'  => [
            'name'      =>  'StartTimer',	
            'offset' => "cf020000"
        ],     
        
        'stoptimer'  => [
            'name'      =>  'StopTimer',
            'offset' => "d0020000"
        ],   
        
        'showtimer'  => [
            'name'      =>  'ShowTimer',
            'offset' => "d2020000"
        ], 
        
        'hidetimer'  => [
            'name'      =>  'HideTimer',
            'offset' => "d3020000"
        ],

        'setlevelfailed'  => [
            'name'      =>  'SetLevelFailed',
            'offset' => "8b020000"
        ],

        'hudtoggleflashflags'  => [
            'name'      =>  'HUDToggleFlashFlags',
            'offset' => "af020000",
            /**
             * Parameters
             * 1:  Hud elem
             * 2:  on/off
             */
            'params'    =>  [ 'Integer','Integer' ],
            'return'    =>  'Void',
            'desc'      =>  ''
        ],
    ];



}
