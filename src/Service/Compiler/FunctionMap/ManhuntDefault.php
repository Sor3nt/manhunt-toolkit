<?php
namespace App\Service\Compiler\FunctionMap;

class ManhuntDefault {


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
        'setentityfade' => [false, true, true],
        'setcolourramp' => [false, false, true],
        'setpedorientation' => [false, true],
        'setvector' => [false, true, true, true],
        'aisethunteridleactionminmaxradius' => [false, false, false, false, false, true]
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
        'onveryhighsighting' => '06000000',
        'onmediumsighting' => '08000000',
        'onhunterenterarea' => '09000000',
        'onpickupinventoryitem' => '0b000000',
        'onhunterreachednode' => '1b000000',
        'onhighsightingorabove' => '1c000000',
        'onmediumsightingorabove' => '1d000000',
        'ondeath' => '1e000000',
        'onhunterlooklisten' => '1f000000',
        'ondeadbodyfound' => '2e000000',
        'onstartexecution' => '3a000000',
        'onpickupdeadbody' => '4f000000',
        'onstartnonfatalattack' => '5b000000',
        'onhelireachedlookat' => '5e000000',
        'onhelispottedentity' => '5f000000',
        'onbeingshot' => '13000000',
        'onhunteridle' => '18000000',
        'onhunterwalktoinvestigate' => '20000000',
        'onhunterruntoinvestigate' => '21000000',
        'onhunterwalkruntoinvestigate' => '22000000',
        'onhunterlookwalkruntoinvestigate' => '23000000',
        'onlowsightingorabove' => '27000000',
        'onverylowsightingorabove' => '29000000',
        'onhighhearingorabove' => '31000000',
        'onmediumhearing' => '32000000',
        'onmediumhearingorabove' => '33000000',
        'onlowhearing' => '34000000',
        'onlowhearingorabove' => '35000000',
        'onverylowhearingorabove' => '37000000',
        'onplayerspotted' => '42000000',
        'onenteredsafezone' => '43000000',
        'onuseableused' => '46000000',
        'onstartenvexecution' => '54000000',
        'onfocus' => '58000000',
        'onshotweapon' => '60000000',
        'onhelireachednode' => '64000000',
        'onqtmfailed' => '67000000',
        
        'onhighsighting' => '07000000',
        'onplayerenterarea' => '0a000000',
        'onlevelsave' => '0d000000',
        'onstartbeinggrappled' => '3f000000',
        'onentityusedswitch' => '4b000000',
        'onguardidle' => '4c000000',
        'ontimerended' => '4d000000',
        'onstartexploding' => '10000000',
        'onfriendleavingbuddyradius' => '17000000',
        'onuseableanimfinished' => '41000000',
        'onreloadweapon' => '47000000',
        'onbuddybeingtoldtostop' => '52000000',
        'ondropinventoryitem' => '53000000',
        'acting' => '54000000',

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


    ];

    public static $functions = [

        'sleep' => [
            'name' => 'Sleep',
            'offset' => "6a000000"
        ],

        'runscript' => [
            'name' => 'RunScript',
            'offset' => "e4000000"
        ],

    ];



}
