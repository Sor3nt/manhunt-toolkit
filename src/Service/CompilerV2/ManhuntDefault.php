<?php

namespace App\Service\CompilerV2;

class ManhuntDefault
{

    public function getFunction( $name ){
        $name = strtolower($name);

        if (isset($this->functions[$name])){
            $function = $this->functions[$name];

            //hack
            if (!isset($function['name'])) $function['name'] = $name;

            if (isset($this->functionForceFloat[$name])){
                $function['forceFloat'] = $this->functionForceFloat[$name];
            }

            return $function;
        }

        return false;
    }

    public function getConstant( $name ){
        if (isset($this->constants[strtolower($name)])){
            return $this->constants[strtolower($name)];
        }

        return false;
    }

    public function getType( $name, $type ){
        die("todo");
        if (isset($this->types[strtolower($name)])){
            return $this->types[strtolower($name)];
        }

        return false;
    }

    public $types = [];
    public $constants = [];
    public $functions = [];

    /**
     * some functions need explicit float parameters
     * when someone give a 10 instead of 10.0 we need to
     * tell te engine to convert the value
     *
     * Sample Input: SetVector(pos, 1, 23.45, 67.89);
     * the function require 3 floats but the first value is a int.
     * convert it to a float with 0x4d 0x10 0x01
     */
    public $functionForceFloat = [
        'setmoverspeed' => [false, true],
        'setslidedoorspeed' => [false, true],
        'aidefinegoalguard' => [false, false, false, true, true, false],
        'huntersetgunaccuracynear' => [false, true, true],
        'huntersetgunaccuracymid' => [false, true, true],
        'huntersetgunaccuracyfar' => [false, true, true],
        'createspheretrigger' => [false, true, false],
        'setentityfade' => [false, true, true],
        'setcolourramp' => [false, false, true],
        'setpedorientation' => [false, true],
        'setvector' => [false, true, true, true],
        'aisethunteridleactionminmaxradius' => [false, false, false, false, false, true],
        'aidefinegoalguarddirection' => [false, false, false, true, true, false, false],
        'sethunterruntime' => [false, true],
        'setdooropenanglein' => [false, true],
        'setdooropenangleout' => [false, true],
        'setzoomlerp' => [true, true, true],
        'setdooroverrideangle' => [false, true],
        'camerastoplookatentity' => [false, true],
        'aidefinegoalhidenamedhunter' => [false, false, false, false, true, false],
        'setplayerheading' => [true],
        'aientityplayanimlooped' => [false, false, true],
        'aidefinegoalbebuddy' => [false, false, false, true],
        'handcamsetall' => [false, true, true, true, true],
        'cameraforcelookatentity' => [false, false, false, false, true],

    ];

    public $functionEventDefinition = [
        'oncreate' => '00000000',
        'ondestroy' => '01000000',
        'ondamage' => '02000000',
        'onusebyplayer' => '03000000',
        'onentertrigger' => '04000000',
        'onleavetrigger' => '05000000',
        'onveryhighsighting' => '06000000',
        'onmediumsighting' => '08000000',
        'onhunterenterarea' => '09000000',
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


        'onplayerenterarea' => '0a000000', // int 10
        'onpickupinventoryitem' => '0b000000', //int 11
        'onstartburning' => '0c000000', //int 12
        'onstopburning' => '0d000000', //int 13


        'onstartexploding' => '10000000', // int 16
        'onstartignitin' => '11000000', // int 17
        'onstopigniting' => '12000000', // int 18
        //onbeingshot
        //onstartelectrocuting
        //onstopelectrocuting
        //onfriendenteringbuddyradius
        //onfriendleavingbuddyradius


        //Onstartstunned
        //Onstopstunned


        'onlevelsave' => '0d000000',
        'onstartbeinggrappled' => '3f000000',
        'onentityusedswitch' => '4b000000',
        'onguardidle' => '4c000000',
        'ontimerended' => '4d000000',
        'onfriendleavingbuddyradius' => '17000000',
        'onuseableanimfinished' => '41000000',
        'onreloadweapon' => '47000000',
        'onbuddybeingtoldtostop' => '52000000',
        'ondropinventoryitem' => '53000000',
        'acting' => '54000000',

    ];


}
