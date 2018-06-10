<?php
namespace App\Service;

class BytecodeExplain {


    private $mapping = [


        'functions' => [

            /**
             * Any functions use always the same address
             */

            "AICutsceneEntityEnable"            => "\xa9\x02\x00\x00",

            "cutsceneend"                       => "\x49\x01\x00\x00",
            "ClearLevelGoal"                    => "\x42\x02\x00\x00",
            "CutsceneStart"                     => "\x48\x01\x00\x00",
            "CutsceneRegisterSkipScript"        => "\x20\x03\x00\x00",

            "DisplayGameText"                   => "\x04\x01\x00\x00",

            "FrisbeeSpeechPlay"                 => "\x66\x03\x00\x00",

            'GetEntityName'                     => "\x86\x00\x00\x00",
            "GetDoorState"                      => "\x96\x00\x00\x00",
            "GetEntity"                         => "\x77\x00\x00\x00",

            "HUDToggleFlashFlags"               => "\xb2\x02\x00\x00",

            "IsWhiteNoiseDisplaying"            => "\xe7\x02\x00\x00",

            "KillGameText"                      => "\x08\x01\x00\x00",
            "KillScript"                        => "\xe5\x00\x00\x00",

            "RunScript"                         => "\xe4\x00\x00\x00",

            "SetDoorState"                      => "\x97\x00\x00\x00",
            "SetSwitchState"                    => "\x95\x00\x00\x00",
            "SetCurrentLOD"                     => "\x2d\x01\x00\x00",
            "SetShowHudInCutscene"              => "\x86\x03\x00\x00",
            "SetVector"                         => "\x84\x01\x00\x00",
            "SetCameraPosition"                 => "\x92\x01\x00\x00",
            "SetCameraView"                     => "\x8f\x01\x00\x00",
            "SetZoomLerp"                       => "\xb5\x02\x00\x00",
            "SetLevelGoal"                      => "\x41\x02\x00\x00",
            "SetSlideDoorSpeed"                 => "\xae\x01\x00\x00",
            "sleep"                             => "\x6a\x00\x00\x00",

            "ToggleHudFlag"                     => "\x7f\x02\x00\x00",

            "WriteDebug"                        => "\x74\x00\x00\x00"

        ],

        'parameters' => [

            /**
             * a parameter block start always with 8-bytes followed by 4-byte param value and finalized with 8-bytes
             */

            'variante_1' => [
                'start' => [
                    "\x12\x00\x00\x00",
                    "\x01\x00\x00\x00"
                ],
                'end' => [
                    "\x10\x00\x00\x00" ,
                    "\x01\x00\x00\x00"
                ],
                'desc' => 'parameter (1)'
            ],

            'variante_2' => [
                'start' => [
                    "\x12\x00\x00\x00",
                    "\x02\x00\x00\x00"
                ],
                'end' => [
                    "\x10\x00\x00\x00" ,
                    "\x01\x00\x00\x00"
                ],

                'desc' => 'parameter (2)'
            ],

            'variante_3' => [

                'start' => [
                    "\x12\x00\x00\x00",
                    "\x03\x00\x00\x00"
                ],
                'end' => [
                    "\x10\x00\x00\x00" ,
                    "\x04\x00\x00\x00"
                ],

                'desc' => 'parameter (3)'
            ],

            'variante_4' => [

                'start' => [
                    "\x12\x00\x00\x00",
                    "\x01\x00\x00\x00"
                ],
                'end' => [
                    "\x16\x00\x00\x00" ,
                    "\x04\x00\x00\x00"
                ],

                'desc' => 'parameter (4)'
            ],
        ],



        'script_init' => [
            'hex' => [
                "\x10\x00\x00\x00" ,
                "\x0a\x00\x00\x00" ,
                "\x11\x00\x00\x00" ,
                "\x0a\x00\x00\x00" ,
                "\x09\x00\x00\x00"
            ],

            'desc' => 'Script start block'
        ],


        'script_end' => [
            'hex' => [
                "\x11\x00\x00\x00",
                "\x09\x00\x00\x00",
                "\x0a\x00\x00\x00",
                "\x0f\x00\x00\x00",
                "\x0a\x00\x00\x00",
                "\x3b\x00\x00\x00",
                "\x00\x00\x00\x00"
            ],

            'desc' => 'Script end block'
        ],


        'return_str' => [
            'hex' => [
                "\x21\x00\x00\x00",
                "\x04\x00\x00\x00",
                "\x01\x00\x00\x00"
            ],

            'desc' => 'assign string to'
        ],

        'return_str_arr' => [
            'hex' => [
                "\x21\x00\x00\x00",
                "\x04\x00\x00\x00",
                "\x04\x00\x00\x00"
            ],

            'desc' => 'assign string array to'
        ],

        'inverse_number' => [
            'hex' => [
                "\x4f\x00\x00\x00",
                "\x32\x00\x00\x00",
                "\x09\x00\x00\x00",
                "\x04\x00\x00\x00",
                "\x10\x00\x00\x00",
                "\x01\x00\x00\x00"
            ],

            'desc' => 'turn prev number into negative'
        ],

        'if_statement_1' => [
            'hex' => [
                "\x10\x00\x00\x00",
                "\x01\x00\x00\x00",
                "\x12\x00\x00\x00",
                "\x01\x00\x00\x00",
                "",
                "\x0f\x00\x00\x00",
                "\x04\x00\x00\x00",
                "\x23\x00\x00\x00",
                "\x04\x00\x00\x00",
                "\x01\x00\x00\x00",
                "\x12\x00\x00\x00",
                "\x01\x00\x00\x00",
                "\x01\x00\x00\x00",
                "",
                "",
                "\x33\x00\x00\x00",
                "\x01\x00\x00\x00",
                "\x01\x00\x00\x00",

                "\x24\x00\x00\x00",
                "\x01\x00\x00\x00",
                "\x00\x00\x00\x00",
                "\x3f\x00\x00\x00"
            ],

            'desc' => 'If statement'
        ]

    ];

    public function explain( $content ){

        $content = new Binary( implode("", explode("\n", $content)), true);
        $lines = $content->split(4);

        $result = [];

        $this->mapIfStatement1( $lines, $result);
        $this->mapiÍnverseNumber( $lines, $result);
        $this->mapReturnStr( $lines, $result);
        $this->mapReturnStrArray( $lines, $result);
        $this->mapScriptStarts( $lines, $result);
        $this->mapScriptEnd( $lines, $result);

        $this->mapFunctionCalls( $lines, $result);
        $this->mapParameterCalls( $lines, $result);

        $fillCount = count($result);
        $this->mapUnknown( $lines, $result);
        $fullCount = count($result);

        $missedCount = $fullCount - $fillCount;

        echo sprintf("Sum: %s\n", $fullCount);
        echo sprintf("Explained: %s\n", $fillCount);
        echo sprintf("Unknown: %s\n", $missedCount);
        echo sprintf("Percent: %s%% translated\n", number_format(100 - ($missedCount / $fullCount) * 100), 2);

        ksort($result);
        return $result;

    }

    private function mapIfStatement1(array $lines, &$result ){
        /** @var Binary[] $lines */

        foreach ($lines as $lineIndex => $line) {

            if (
                $line->toBinary() == $this->mapping['if_statement_1']['hex'][0] &&
                isset($lines[ $lineIndex + 1]) && $lines[ $lineIndex + 1]->toBinary() == $this->mapping['if_statement_1']['hex'][1] &&
                isset($lines[ $lineIndex + 2]) && $lines[ $lineIndex + 2]->toBinary() == $this->mapping['if_statement_1']['hex'][2] &&
                isset($lines[ $lineIndex + 3]) && $lines[ $lineIndex + 3]->toBinary() == $this->mapping['if_statement_1']['hex'][3] &&

                isset($lines[ $lineIndex + 5]) && $lines[ $lineIndex + 5]->toBinary() == $this->mapping['if_statement_1']['hex'][5] &&
                isset($lines[ $lineIndex + 6]) && $lines[ $lineIndex + 6]->toBinary() == $this->mapping['if_statement_1']['hex'][6] &&
                isset($lines[ $lineIndex + 7]) && $lines[ $lineIndex + 7]->toBinary() == $this->mapping['if_statement_1']['hex'][7] &&
                isset($lines[ $lineIndex + 8]) && $lines[ $lineIndex + 8]->toBinary() == $this->mapping['if_statement_1']['hex'][8] &&
                isset($lines[ $lineIndex + 9]) && $lines[ $lineIndex + 9]->toBinary() == $this->mapping['if_statement_1']['hex'][9] &&
                isset($lines[ $lineIndex + 10]) && $lines[ $lineIndex + 10]->toBinary() == $this->mapping['if_statement_1']['hex'][10] &&
                isset($lines[ $lineIndex + 11]) && $lines[ $lineIndex + 11]->toBinary() == $this->mapping['if_statement_1']['hex'][11] &&
                isset($lines[ $lineIndex + 12]) && $lines[ $lineIndex + 12]->toBinary() == $this->mapping['if_statement_1']['hex'][12] &&


                isset($lines[ $lineIndex + 15]) && $lines[ $lineIndex + 15]->toBinary() == $this->mapping['if_statement_1']['hex'][15] &&
                isset($lines[ $lineIndex + 16]) && $lines[ $lineIndex + 16]->toBinary() == $this->mapping['if_statement_1']['hex'][16] &&
                isset($lines[ $lineIndex + 17]) && $lines[ $lineIndex + 17]->toBinary() == $this->mapping['if_statement_1']['hex'][17] &&

                isset($lines[ $lineIndex + 18]) && $lines[ $lineIndex + 18]->toBinary() == $this->mapping['if_statement_1']['hex'][18] &&
                isset($lines[ $lineIndex + 19]) && $lines[ $lineIndex + 19]->toBinary() == $this->mapping['if_statement_1']['hex'][19] &&
                isset($lines[ $lineIndex + 20]) && $lines[ $lineIndex + 20]->toBinary() == $this->mapping['if_statement_1']['hex'][20]

            ){

                for($i = 0; $i <= 3; $i++){
                    $result[$lineIndex + $i] = [
                        $lines[ $lineIndex + $i]->toHex(),
                        $this->mapping['if_statement_1']['desc']
                    ];
                }

                $result[$lineIndex + 4] = [
                    $lines[ $lineIndex + 4]->toHex(),
                    $this->mapping['if_statement_1']['desc'] . "(unknown)"
                ];


                for($i = 5; $i <= 12; $i++){
                    $result[$lineIndex + $i] = [
                        $lines[ $lineIndex + $i]->toHex(),
                        $this->mapping['if_statement_1']['desc']
                    ];
                }

                $operation = $lines[ $lineIndex + 13]->toHex();

                $operationText = "unknown operator";

                if ($operation == "40000000") {
                    $operationText = "un-equal";
                }else if ($operation == "3f000000"){
                    $operationText = "equal";
                }

                $result[$lineIndex + 13] = [
                    $operation,
                    $operationText
                ];


                $result[$lineIndex + 14] = [
                    $lines[ $lineIndex + 14]->toHex(),
                    $this->mapping['if_statement_1']['desc'] . "(unknown)"
                ];

                for($i = 15; $i <= 20; $i++){
                    $result[$lineIndex + $i] = [
                        $lines[ $lineIndex + $i]->toHex(),
                        $this->mapping['if_statement_1']['desc']
                    ];
                }

                $result[$lineIndex + 21] = [
                    $lines[ $lineIndex + 21]->toHex(),
                    'store value'
                ];



            }
        }

    }

    private function mapiÍnverseNumber(array $lines, &$result ){
        /** @var Binary[] $lines */

        foreach ($lines as $lineIndex => $line) {

            if (
                $line->toBinary() == $this->mapping['inverse_number']['hex'][0] &&
                isset($lines[ $lineIndex + 1]) && $lines[ $lineIndex + 1]->toBinary() == $this->mapping['inverse_number']['hex'][1] &&
                isset($lines[ $lineIndex + 2]) && $lines[ $lineIndex + 2]->toBinary() == $this->mapping['inverse_number']['hex'][2] &&
                isset($lines[ $lineIndex + 3]) && $lines[ $lineIndex + 3]->toBinary() == $this->mapping['inverse_number']['hex'][3] &&
                isset($lines[ $lineIndex + 4]) && $lines[ $lineIndex + 4]->toBinary() == $this->mapping['inverse_number']['hex'][4] &&
                isset($lines[ $lineIndex + 5]) && $lines[ $lineIndex + 5]->toBinary() == $this->mapping['inverse_number']['hex'][5]
            ){

                for($i = 0; $i <= 5; $i++){
                    $result[$lineIndex + $i] = [
                        $lines[ $lineIndex + $i]->toHex(),
                        $this->mapping['inverse_number']['desc']
                    ];

                }

            }

        }

    }


    private function mapReturnStr(array $lines, &$result ){
        /** @var Binary[] $lines */

        foreach ($lines as $lineIndex => $line) {

            if (
                $line->toBinary() == $this->mapping['return_str']['hex'][0] &&
                isset($lines[ $lineIndex + 1]) && $lines[ $lineIndex + 1]->toBinary() == $this->mapping['return_str']['hex'][1] &&
                isset($lines[ $lineIndex + 2]) && $lines[ $lineIndex + 2]->toBinary() == $this->mapping['return_str']['hex'][2]
            ){

                for($i = 0; $i <= 2; $i++){
                    $result[$lineIndex + $i] = [
                        $lines[ $lineIndex + $i]->toHex(),
                        $this->mapping['return_str']['desc']
                    ];
                }

            }

        }

    }

    private function mapReturnStrArray(array $lines, &$result ){
        /** @var Binary[] $lines */

        foreach ($lines as $lineIndex => $line) {

            if (
                $line->toBinary() == $this->mapping['return_str_arr']['hex'][0] &&
                isset($lines[ $lineIndex + 1]) && $lines[ $lineIndex + 1]->toBinary() == $this->mapping['return_str_arr']['hex'][1] &&
                isset($lines[ $lineIndex + 2]) && $lines[ $lineIndex + 2]->toBinary() == $this->mapping['return_str_arr']['hex'][2]
            ){

                for($i = 0; $i <= 2; $i++){
                    $result[$lineIndex + $i] = [
                        $lines[ $lineIndex + $i]->toHex(),
                        $this->mapping['return_str_arr']['desc']
                    ];

                }


                $result[$lineIndex + 5] = [
                    $lines[ $lineIndex + 5]->toHex(),
                    'assign value to'
                ];


            }

        }

    }


    private function mapScriptStarts(array $lines, &$result ){
        /** @var Binary[] $lines */

        foreach ($lines as $lineIndex => $line) {

            if (
                $line->toBinary() == $this->mapping['script_init']['hex'][0] &&
                isset($lines[ $lineIndex + 1]) && $lines[ $lineIndex + 1]->toBinary() == $this->mapping['script_init']['hex'][1] &&
                isset($lines[ $lineIndex + 2]) && $lines[ $lineIndex + 2]->toBinary() == $this->mapping['script_init']['hex'][2] &&
                isset($lines[ $lineIndex + 3]) && $lines[ $lineIndex + 3]->toBinary() == $this->mapping['script_init']['hex'][3] &&
                isset($lines[ $lineIndex + 4]) && $lines[ $lineIndex + 4]->toBinary() == $this->mapping['script_init']['hex'][4]
            ){

                for($i = 0; $i <= 4; $i++){
                    $result[$lineIndex + $i] = [
                        $lines[ $lineIndex + $i]->toHex(),
                        $this->mapping['script_init']['desc']
                    ];

                }

                $result[$lineIndex + 5] = [
                    $lines[ $lineIndex + 5]->toHex(),
                    'assign value to'
                ];


            }

        }

    }


    private function mapScriptEnd(array $lines, &$result ){
        /** @var Binary[] $lines */

        foreach ($lines as $lineIndex => $line) {

            if (
                $line->toBinary() == $this->mapping['script_end']['hex'][0] &&
                isset($lines[ $lineIndex + 1]) && $lines[ $lineIndex + 1]->toBinary() == $this->mapping['script_end']['hex'][1] &&
                isset($lines[ $lineIndex + 2]) && $lines[ $lineIndex + 2]->toBinary() == $this->mapping['script_end']['hex'][2] &&
                isset($lines[ $lineIndex + 3]) && $lines[ $lineIndex + 3]->toBinary() == $this->mapping['script_end']['hex'][3] &&
                isset($lines[ $lineIndex + 4]) && $lines[ $lineIndex + 4]->toBinary() == $this->mapping['script_end']['hex'][4] &&
                isset($lines[ $lineIndex + 5]) && $lines[ $lineIndex + 5]->toBinary() == $this->mapping['script_end']['hex'][5] &&
                isset($lines[ $lineIndex + 6]) && $lines[ $lineIndex + 6]->toBinary() == $this->mapping['script_end']['hex'][6]
            ){

                for($i = 0; $i <= 6; $i++){
                    $result[$lineIndex + $i] = [
                        $lines[ $lineIndex + $i]->toHex(),
                        $this->mapping['script_end']['desc']
                    ];

                }

            }

        }

    }

    private function mapParameterCalls(array $lines, &$result ){
        /** @var Binary[] $lines */

        foreach ($this->mapping['parameters'] as $paramVariante){

            foreach ($lines as $lineIndex => $line) {

                if (
                    $line->toBinary() == $paramVariante['start'][0] &&
                    isset($lines[ $lineIndex + 1]) && $lines[ $lineIndex + 1]->toBinary() == $paramVariante['start'][1] &&
                    isset($lines[ $lineIndex + 2]) && // the value
                    isset($lines[ $lineIndex + 3]) && $lines[ $lineIndex + 3]->toBinary() == $paramVariante['end'][0] &&
                    isset($lines[ $lineIndex + 4]) && $lines[ $lineIndex + 4]->toBinary() == $paramVariante['end'][1]
                ){


                    $result[$lineIndex] = [
                        $line->toHex(),
                        $paramVariante['desc']
                    ];


                    $result[$lineIndex + 1] = [
                        $lines[ $lineIndex + 1]->toHex(),
                        $paramVariante['desc']
                    ];

                    $valueHex = $lines[ $lineIndex + 2]->toHex();

                    $value = "value";
                    if ($valueHex == "49000000") $value = "Reference to THIS";
                    if ($valueHex == "00000000") $value = "Bool false / int 0";
                    if ($valueHex == "01000000") $value = "Bool true / int 1";

                    if (
                        isset($result[$lineIndex + 5]) && $lines[ $lineIndex + 5]->toBinary() == "\x10\x00\x00\x00" &&
                        isset($result[$lineIndex + 6]) && $lines[ $lineIndex + 6]->toBinary() == "\x02\x00\x00\x00"
                    ){
                        $value = "Reference to a string";

                    }


                    $result[$lineIndex + 2] = [
                        $lines[ $lineIndex + 2]->toHex(),
                        $value
                    ];


                    $result[$lineIndex + 3] = [
                        $lines[ $lineIndex + 3]->toHex(),
                        $paramVariante['desc']
                    ];


                    $result[$lineIndex + 4] = [
                        $lines[ $lineIndex + 4]->toHex(),
                        $paramVariante['desc']
                    ];

                    if (
                        $lines[ $lineIndex + 6]->toBinary() == "\x02\x00\x00\x00" &&
                        $lines[ $lineIndex + 5]->toBinary() == "\x10\x00\x00\x00"
                    ){

                        $result[$lineIndex + 5] = [
                            $lines[ $lineIndex + 5]->toHex(),
                            'string pointer move'
                        ];
                        $result[$lineIndex + 6] = [
                            $lines[ $lineIndex + 6]->toHex(),
                            'string pointer move'
                        ];

                    }

                }

            }

        }

    }


    private function mapFunctionCalls(array $lines, &$result ){
        /** @var Binary[] $lines */

        foreach ($this->mapping['functions'] as $functionName => $functionBinary){

            foreach ($lines as $lineIndex => $line) {

                if ($line->toBinary() == $functionBinary){
                    $result[$lineIndex] = [
                        $line->toHex(),
                        $functionName . ' Call'
                    ];
                }

            }

        }
    }

    private function mapUnknown(array $lines, &$result){
        /** @var Binary[] $lines */

        foreach ($lines as $lineIndex => $line) {
            if (!isset($result[ $lineIndex ])){
                $result[$lineIndex] = [
                    $line->toHex(),
                    'unknown'
                ];
            }
        }
    }

}