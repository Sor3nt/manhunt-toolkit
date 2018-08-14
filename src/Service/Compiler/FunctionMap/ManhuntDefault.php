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

    ];

    public static $constants = [

    ];

    public static $functions = [

    ];



}
