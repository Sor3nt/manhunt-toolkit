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
        '__default__' => '68000000'
    ];

    public static $constants = [


    ];

    public static $functions = [

	
	        'sleep'  => [
            'name'      =>  'Sleep',	
            'offset' => "6a000000"
        ],     
        
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
		
		"displaygametext"  => [
			'name'      =>  'DisplayGameText',
            'offset' => "03010000",
            'params'    =>  [ 'String' ]

        ],
		
		'setnumberofkillablehuntersinlevel'  => [
            'name'      =>  'SetNumberOfKillableHuntersInLevel',
            'offset' => "e6020000",
            /**
             * Parameters
             * 1:  to kill
             * 2:  to execute
             */
            'params'    =>  [ 'Integer','Integer' ],
            'return'    =>  'Void',
            'desc'      =>  ''
        ],
		'handcamsetvideoeffecttimecode'  => [
            'name'      =>  'HandCamSetVideoEffectTimeCode',
            'offset' => "5f020000",
            'params'    =>  [ 'Integer'],
            'desc'      =>  ''
        ],
		'handcamsetvideoeffectrecorddot'  => [
            'name'      =>  'HandCamSetVideoEffectRecordDot',
            'offset' => "60020000",
            'params'    =>  [ 'Integer'],
            'desc'      =>  ''
        ],
		'handcamsetvideoeffectfuzz'  => [
            'name'      =>  'HandCamSetVideoEffectFuzz',
            'offset' => "61020000",
            'params'    =>  [ 'Integer'],
            'desc'      =>  ''
        ],
		'handcamsetvideoeffectscrollbar'  => [
            'name'      =>  'HandCamSetVideoEffectScrollBar',
            'offset' => "62020000",
            'params'    =>  [ 'Integer'],
            'desc'      =>  ''
        ],
		'aiaddplayer'  => [
            'name'      =>  'AIAddPlayer',
            'offset' => "5a010000",
            'params'    =>  [ 'String'],
            'desc'      =>  ''
        ],
        'setlevelgoal'  => [
            'name'      =>  'SetLevelGoal',
            'offset' => "3e020000",
            'params'    =>  [ 'String'],
            'desc'      =>  ''
        ],
            'createinventoryitem'  => [
            'name'      =>  'CreateInventoryItem',
            'offset' => "b9000000",
        ],
    ];



}
