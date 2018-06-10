<?php

namespace App\Bytecode\Mls\Struct\Script;


use App\Service\Binary;
use Symfony\Component\Config\Definition\Exception\Exception;

class ScriptFunction extends ScriptAbstract{

    private $functionMapping = [

        'mh2' => [
            "GetEntityName"                                     => "\x86",
            "WriteDebug"                                        => "\x74",
            "SetSwitchState"                                    => "\x95",
            "RunScript"                                         => "\xe4",
            "GetEntity"                                         => "\x77",
            "SetSlideDoorSpeed"                                 => "\xae\x01",
            "GetDoorState"                                      => "\x96",
            "SetDoorState"                                      => "\x97",
            "KillScript"                                        => "\xe5",
            "sleep"                                             => "\x6a",
            "SetCurrentLOD"                                     => "\x2d\x01",
            "SetShowHudInCutscene"                              => "\x86\x03",
            "CutsceneStart"                                     => "\x48\x01",
            "CutsceneRegisterSkipScript"                        => "\x20\x03",
            "ToggleHudFlag"                                     => "\x7f\x02",
            "Call AICutsceneEntityEnable"                       => "\xa9\x02",
            "SetVector"                                         => "\x84\x01",
            "SetCameraPosition"                                 => "\x92\x01",
            "SetCameraView"                                     => "\x8f\x01",
            "SetZoomLerp"                                       => "\xb5\x02",
            "IsWhiteNoiseDisplaying"                            => "\xe7\x02",
            "HUDToggleFlashFlags"                               => "\xb2\x02",
            "DisplayGameText"                                   => "\x04\x01",
            "cutsceneend"                                       => "\x49\x01",
            "KillGameText"                                      => "\x08\x01",
            "SetLevelGoal"                                      => "\x41\x02",
            "ClearLevelGoal"                                    => "\x42\x02",
            "FrisbeeSpeechPlay"                                 => "\x66\x03",
            "IsEntityAlive"                                     => "\xaa\x01",
            "GraphModifyConnections"                            => "\xe9"
        ]

    ];


    private $functionName;

    private $functionParameters = [];

    private $stringMap = [];


    public function __construct( $functionName, $functionParameters, $stringMap ) {

        $this->functionName = $functionName;
        $this->functionParameters = $functionParameters;
        $this->stringMap = $stringMap;

        $this->parse();
    }

    private function parse(){

        foreach ($this->functionParameters as &$value) {

            if (substr($value, 0, 4) === "str_") {

                $strLen = (int) explode('str_', $value)[1];

                $strValue = $this->pad(dechex($strLen));

                $value = [
                    'type' => 'string',
                    'value' => hex2bin($strValue),
                    'rawValue' => $strLen
                ];

            }else if (strpos($value, ".") !== false) {
                $floValue = (float) $value;
                $negation = $floValue < 0.0;

                if ($negation) $floValue = $floValue * -1;

                $value = [
                    'type' => 'float',
                    'value' => hex2bin(strrev(unpack('h*', pack('f', $floValue))[1])),
                    'rawValue' => $floValue,
                    'negation' => $negation
                ];


            }else if (is_numeric($value) === true){

                $int = (int) $value;
                $negation = $int < 0;

                if ($negation) $int = $int * -1;

                $intValue = $this->pad($this->toBigEndian(dechex($int)));

                $value = [
                    'type' => 'integer',
                    'value' => hex2bin($intValue),
                    'rawValue' => (int) $value,
                    'negation' => $negation

                ];
            }elseif ($value == "this"){
                $value = [
                    'type' => 'this',
                    'value' => "\x49"
                ];

            }else{

                throw new \Exception( sprintf('Unsupported value type received %s', $value) );
            }
        }
    }


    public function toByteCode( $game, &$offset = 0) {

        $functionMapping = $this->functionMapping[ $game ];

        if (!isset($functionMapping[ $this->functionName ])){
            throw new Exception(sprintf('Function address is unknown for %s', $this->functionName));

        }

        $bytecode = [];

        // append every parameter
        foreach ($this->functionParameters as $parameter) {

            /**
             * We want to use a string as parameter
             * we need to reserve us our memory space
             */
            if ($parameter['type'] === "string") {
                $bytecode[] = "\x21";
                $bytecode[] = "\x04";
                $bytecode[] = "\x01";

                /**
                 * Offset
                 *
                 * The offset is calculated by
                 *
                 * Last offset + current str len + padding (if needed) == new offset
                 */
                $bytecode[] = hex2bin($this->pad(dechex($offset)));

                //add the str len to the offset reference
                $offset += $parameter['rawValue'];
            }

            /**
             * initialize parameter
             */
            $bytecode[] = "\x12";

            /**
             * define the next parameter
             * \x01 is for integers, floats and const (const are actual float or int) and also "this" use this
             * \x02 is for strings and string arrays
             */

            if ($parameter['type'] === "string") {
                $bytecode[] = "\x02";
            }else{
                $bytecode[] = "\x01";
            }

            /**
             * Add actual value
             */
            $bytecode[] = $parameter['value'];

            /*
             * terminate parameter
             */
            $bytecode[] = "\x10";
            $bytecode[] = "\x01";


            /**
             * When the input value is a negative float or int
             * we assign the positive value and negate them with this sequence
             *
             * (Ehhh wtf ?!)
             */
            if (
                ($parameter['type'] == 'integer' || $parameter['type'] == 'float') &&
                $parameter['negation'] === true
            ){

                $bytecode[] = "\x4f";
                $bytecode[] = "\x32";
                $bytecode[] = "\x09";
                $bytecode[] = "\x04";
                $bytecode[] = "\x10";
                $bytecode[] = "\x01";

            }

            /**
             * I think this is the string pointer from the DATA section, tell him to move his pointer
             * it occurres only in string parameters
             */
            if ($parameter['type'] === "string"){
                $bytecode[] = "\x10";
                $bytecode[] = "\x02";
            }
        }

        // call the function
        $bytecode[] = $functionMapping[ $this->functionName ];


        //TODO: are we inside a nested call ? we need to tell him this
        // \x10 + \x01

        return $bytecode;
    }



}