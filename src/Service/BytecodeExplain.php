<?php
namespace App\Service;

use App\Bytecode\Helper;
use App\Service\Compiler\FunctionMap\Manhunt;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\FunctionMap\ManhuntDefault;

class BytecodeExplain {


    private $mapping = [

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
                'desc' => 'parameter (read simple type (int/float...))'
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

                'desc' => 'parameter (Read String var)'
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

                'desc' => 'parameter (read string array? assign?)'
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

                'desc' => 'parameter (access script var)'
            ],

            'variante_5' => [

                'start' => [
                    "\x12\x00\x00\x00",
                    "\x01\x00\x00\x00"
                ],
                'end' => [
                    "\x1a\x00\x00\x00" ,
                    "\x01\x00\x00\x00"
                ],

                'desc' => 'parameter (access level_var)'
            ],

            'variante_6' => [

                'start' => [
                    "\x12\x00\x00\x00",
                    "\x01\x00\x00\x00"
                ],
                'end' => [
                    "\x0f\x00\x00\x00" ,
                    "\x04\x00\x00\x00"
                ],

                'desc' => 'parameter (temp int)'
            ],

            'variante_7' => [

                'start' => [
                    "\x12\x00\x00\x00",
                    "\x01\x00\x00\x00"
                ],
                'end' => [
                    "\x0f\x00\x00\x00" ,
                    "\x02\x00\x00\x00"
                ],

                'desc' => 'parameter (function return (bool?))'
            ],


        ],



        'statement_not' => [
            'hex' => [
                "\x29\x00\x00\x00" ,
                "\x01\x00\x00\x00" ,
                "\x01\x00\x00\x00" ,
            ],

            'desc' => 'NOT'
        ],

        'read_from_script_var' => [
            'hex' => [
                "\x13\x00\x00\x00" ,
                "\x01\x00\x00\x00" ,
                "\x04\x00\x00\x00" ,
            ],

            'desc' => 'read from script var'
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

        'nested_return' => [
            'hex' => [
                "\x10\x00\x00\x00" ,
                "\x01\x00\x00\x00" ,
            ],

            'desc' => 'nested call return result'
        ],

        'read_header_var' => [
            'hex' => [
                "\x14\x00\x00\x00" ,
                "\x01\x00\x00\x00" ,
                "\x04\x00\x00\x00" ,
            ],

            'desc' => 'Read VAR from header'
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


        'set_str_offset_1' => [
            'hex' => [
                "\x21\x00\x00\x00",
                "\x04\x00\x00\x00",
                "\x01\x00\x00\x00"
            ],

            'desc' => 'Prepare string read (DATA table)'
        ],


        'set_str_offset_1_a' => [
            'hex' => [
                "\x22\x00\x00\x00",
                "\x04\x00\x00\x00",
                "\x01\x00\x00\x00"
            ],

            'desc' => 'Prepare vec3d read'
        ],

        'set_str_offset_2' => [
            'hex' => [
                "\x21\x00\x00\x00",
                "\x04\x00\x00\x00",
                "\x04\x00\x00\x00"
            ],

            'desc' => 'Prepare string read (header)'
        ],

        'set_str_offset_3' => [
            'hex' => [
                "\x22\x00\x00\x00",
                "\x04\x00\x00\x00",
                "\x01\x00\x00\x00"
            ],

            'desc' => 'Prepare string read (3)'
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

        'reserve_bytes' => [

            'hex' => [
                "\x34\x00\x00\x00",
                "\x09\x00\x00\x00",
            ],

            'desc' => 'reserve bytes'

        ],

        'if_statement_1' => [
            'hex' => [
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

            ],

            'desc' => 'statement (core)'
        ],

        'if_statement_2' => [
            'hex' => [
                "\x24\x00\x00\x00",
                "\x01\x00\x00\x00",
                "\x00\x00\x00\x00",
            ],

            'desc' => 'statement (end sequence)'
        ],

        'statement_line_offset' => [
            'hex' => [
                "\x3f\x00\x00\x00"
            ],

            'desc' => 'statement (init start offset)'
        ],


        'statement_repeat_offset' => [
            'hex' => [
                "\x3c\x00\x00\x00"
            ],

            'desc' => 'statement (init statement start offset)'
        ],

        'statement_compare' => [
            'hex' => [
                "\x33\x00\x00\x00",
                "\x01\x00\x00\x00",
                "\x01\x00\x00\x00"
            ],

            'desc' => 'statement (compare mode INT/FLOAT)'
        ],

        'statement_or' => [
            'hex' => [
                "\x27\x00\x00\x00",
                "\x01\x00\x00\x00",
                "\x04\x00\x00\x00"
            ],

            'desc' => 'statement (OR operator)'
        ],

        'statement_and' => [
            'hex' => [
                "\x25\x00\x00\x00",
                "\x01\x00\x00\x00",
                "\x04\x00\x00\x00"
            ],

            'desc' => 'statement (AND operator)'
        ],

        'assign_script_var' => [
            'hex' => [
                "\x12\x00\x00\x00",
                "\x03\x00\x00\x00",

                "\x0f\x00\x00\x00",
                "\x01\x00\x00\x00",
            ],

            'desc' => 'assign (to script var)'
        ]

    ];

    public function explain( $content ){

        $content = new Binary( implode("", explode("\n", $content)), true);
        $lines = $content->split(4);

        $result = [];

        $this->mapIfStatement1( $lines, $result);
        $this->mapiÍnverseNumber( $lines, $result);
        $this->mapStringOffset( $lines, $result);
        $this->mapStringOffset2( $lines, $result);
        $this->mapReserveBytes( $lines, $result);
        $this->mapStringOffset3( $lines, $result);
        $this->mapScriptStarts( $lines, $result);
        $this->mapScriptEnd( $lines, $result);
        $this->mapStatementNot( $lines, $result);

        $this->mapLevelVarsBoolean( $lines, $result);
        $this->mapFunctionCalls( $lines, $result);
        $this->mapParameterCalls( $lines, $result);
        $this->mapReadHeaderVar( $lines, $result);
        $this->mapNestedReturn( $lines, $result);
        $this->mapStatementLineOffset( $lines, $result);
        $this->mapStatementCompare( $lines, $result);
        $this->mapStatementOr( $lines, $result);
        $this->mapStatementAnd( $lines, $result);
        $this->mapIfStatement2( $lines, $result);
        $this->mapReadFromScriptVar( $lines, $result);
        $this->mapAssign( $lines, $result);

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
                isset($lines[ $lineIndex + 4]) && $lines[ $lineIndex + 4]->toBinary() == $this->mapping['if_statement_1']['hex'][4] &&
                isset($lines[ $lineIndex + 5]) && $lines[ $lineIndex + 5]->toBinary() == $this->mapping['if_statement_1']['hex'][5] &&

                isset($lines[ $lineIndex + 8]) && $lines[ $lineIndex + 8]->toBinary() == $this->mapping['if_statement_1']['hex'][8] &&
                isset($lines[ $lineIndex + 9]) && $lines[ $lineIndex + 9]->toBinary() == $this->mapping['if_statement_1']['hex'][9] &&
                isset($lines[ $lineIndex + 10]) && $lines[ $lineIndex + 10]->toBinary() == $this->mapping['if_statement_1']['hex'][10]

            ){

                for($i = 0; $i <= 5; $i++){
                    $result[$lineIndex + $i] = [
                        $lines[ $lineIndex + $i]->toHex(),
                        $this->mapping['if_statement_1']['desc']
                    ];
                }


                $operationText = "unknwon";
                if ($lines[ $lineIndex + 6]->toHex() == "40000000") {
                    $operationText = "un-equal";
                }else if ($lines[ $lineIndex + 6]->toHex() == "3f000000"){
                    $operationText = "equal";
                }else if ($lines[ $lineIndex + 6]->toHex() == "3d000000"){
                    $operationText = "smaller";
                }else if ($lines[ $lineIndex + 6]->toHex() == "42000000"){
                    $operationText = "greater";
                }

                $result[$lineIndex + 6] = [
                    $lines[ $lineIndex + 6]->toHex(),
                    $this->mapping['if_statement_1']['desc'] . "(operator " . $operationText . ")"
                ];

                $result[$lineIndex + 7] = [
                    $lines[ $lineIndex + 7]->toHex(),
                    $this->mapping['if_statement_1']['desc'] . "( Offset )"
                ];

//
//
//                $operation = $lines[ $lineIndex + 13]->toHex();
//
//                $operationText = "unknown operator";
//
//                if ($operation == "40000000") {
//                    $operationText = "un-equal";
//                }else if ($operation == "3f000000"){
//                    $operationText = "equal";
//                }
//
//                $result[$lineIndex + 13] = [
//                    $operation,
//                    $operationText
//                ];
//
//
//                $result[$lineIndex + 14] = [
//                    $lines[ $lineIndex + 14]->toHex(),
//                    $this->mapping['if_statement_1']['desc'] . " Start Offset"
//                ];
//
//                for($i = 15; $i <= 20; $i++){
//                    $result[$lineIndex + $i] = [
//                        $lines[ $lineIndex + $i]->toHex(),
//                        $this->mapping['if_statement_1']['desc']
//                    ];
//                }
//
//                $result[$lineIndex + 21] = [
//                    $lines[ $lineIndex + 21]->toHex(),
//                    'if statement'
//                ];
//
//
//                $result[$lineIndex + 22] = [
//                    $lines[ $lineIndex + 22]->toHex(),
//                    'If statement Length Offset'
//                ];
//


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

    private function mapAssign(array $lines, &$result ){
        /** @var Binary[] $lines */


        foreach ($lines as $lineIndex => $line) {

            if (
                $line->toBinary() == $this->mapping['assign_script_var']['hex'][0] &&
                isset($lines[ $lineIndex + 1]) && $lines[ $lineIndex + 1]->toBinary() == $this->mapping['assign_script_var']['hex'][1] &&
                isset($lines[ $lineIndex + 3]) && $lines[ $lineIndex + 3]->toBinary() == $this->mapping['assign_script_var']['hex'][2] &&
                isset($lines[ $lineIndex + 4]) && $lines[ $lineIndex + 4]->toBinary() == $this->mapping['assign_script_var']['hex'][3]
            ){


                $result[$lineIndex] = [
                    $lines[ $lineIndex]->toHex(),
                    $this->mapping['assign_script_var']['desc']
                ];

                $result[$lineIndex + 1] = [
                    $lines[ $lineIndex + 1]->toHex(),
                    $this->mapping['assign_script_var']['desc']
                ];

                $result[$lineIndex + 2] = [
                    $lines[ $lineIndex + 2]->toHex(),
                    'value'
                ];

                $result[$lineIndex + 3] = [
                    $lines[ $lineIndex + 3]->toHex(),
                    $this->mapping['assign_script_var']['desc']
                ];

                $result[$lineIndex + 4] = [
                    $lines[ $lineIndex + 4]->toHex(),
                    $this->mapping['assign_script_var']['desc']
                ];

            }

        }

    }

    private function mapStatementCompare(array $lines, &$result ){
        /** @var Binary[] $lines */

        foreach ($lines as $lineIndex => $line) {

            if (
                $line->toBinary() == $this->mapping['statement_compare']['hex'][0] &&
                isset($lines[ $lineIndex + 1]) && $lines[ $lineIndex + 1]->toBinary() == $this->mapping['statement_compare']['hex'][1] &&
                isset($lines[ $lineIndex + 2]) && $lines[ $lineIndex + 2]->toBinary() == $this->mapping['statement_compare']['hex'][2]
            ){

                for($i = 0; $i <= 2; $i++){
                    $result[$lineIndex + $i] = [
                        $lines[ $lineIndex + $i]->toHex(),
                        $this->mapping['statement_compare']['desc']
                    ];

                }

            }

        }

    }

    private function mapStatementOr(array $lines, &$result ){
        /** @var Binary[] $lines */

        foreach ($lines as $lineIndex => $line) {

            if (
                $line->toBinary() == $this->mapping['statement_or']['hex'][0] &&
                isset($lines[ $lineIndex + 1]) && $lines[ $lineIndex + 1]->toBinary() == $this->mapping['statement_or']['hex'][1] &&
                isset($lines[ $lineIndex + 2]) && $lines[ $lineIndex + 2]->toBinary() == $this->mapping['statement_or']['hex'][2]
            ){

                for($i = 0; $i <= 2; $i++){
                    $result[$lineIndex + $i] = [
                        $lines[ $lineIndex + $i]->toHex(),
                        $this->mapping['statement_or']['desc']
                    ];

                }

            }

        }

    }
    private function mapStatementAnd(array $lines, &$result ){
        /** @var Binary[] $lines */

        foreach ($lines as $lineIndex => $line) {

            if (
                $line->toBinary() == $this->mapping['statement_and']['hex'][0] &&
                isset($lines[ $lineIndex + 1]) && $lines[ $lineIndex + 1]->toBinary() == $this->mapping['statement_and']['hex'][1] &&
                isset($lines[ $lineIndex + 2]) && $lines[ $lineIndex + 2]->toBinary() == $this->mapping['statement_and']['hex'][2]
            ){

                for($i = 0; $i <= 2; $i++){
                    $result[$lineIndex + $i] = [
                        $lines[ $lineIndex + $i]->toHex(),
                        $this->mapping['statement_and']['desc']
                    ];

                }

            }

        }

    }

    private function mapIfStatement2(array $lines, &$result ){
        /** @var Binary[] $lines */

        foreach ($lines as $lineIndex => $line) {

            if (
                $line->toBinary() == $this->mapping['if_statement_2']['hex'][0] &&
                isset($lines[ $lineIndex + 1]) && $lines[ $lineIndex + 1]->toBinary() == $this->mapping['if_statement_2']['hex'][1] &&
                isset($lines[ $lineIndex + 2]) && $lines[ $lineIndex + 2]->toBinary() == $this->mapping['if_statement_2']['hex'][2]
            ){

                for($i = 0; $i <= 2; $i++){
                    $result[$lineIndex + $i] = [
                        $lines[ $lineIndex + $i]->toHex(),
                        $this->mapping['if_statement_2']['desc']
                    ];

                }

            }

        }

    }

    private function mapReadFromScriptVar(array $lines, &$result ){
        /** @var Binary[] $lines */

        foreach ($lines as $lineIndex => $line) {

            if (
                $line->toBinary() == $this->mapping['read_from_script_var']['hex'][0] &&
                isset($lines[ $lineIndex + 1]) && $lines[ $lineIndex + 1]->toBinary() == $this->mapping['read_from_script_var']['hex'][1] &&
                isset($lines[ $lineIndex + 2]) && $lines[ $lineIndex + 2]->toBinary() == $this->mapping['read_from_script_var']['hex'][2]
            ){

                for($i = 0; $i <= 2; $i++){
                    $result[$lineIndex + $i] = [
                        $lines[ $lineIndex + $i]->toHex(),
                        $this->mapping['read_from_script_var']['desc']
                    ];

                }




                $result[$lineIndex + 3] = [
                    $lines[ $lineIndex + 3]->toHex(),
                    'Offset'
                ];

            }

        }

    }


    private function mapStringOffset(array $lines, &$result ){
        /** @var Binary[] $lines */

        foreach ($lines as $lineIndex => $line) {

            if (
                $line->toBinary() == $this->mapping['set_str_offset_1']['hex'][0] &&
                isset($lines[ $lineIndex + 1]) && $lines[ $lineIndex + 1]->toBinary() == $this->mapping['set_str_offset_1']['hex'][1] &&
                isset($lines[ $lineIndex + 2]) && $lines[ $lineIndex + 2]->toBinary() == $this->mapping['set_str_offset_1']['hex'][2]
            ){

                for($i = 0; $i <= 2; $i++){
                    $result[$lineIndex + $i] = [
                        $lines[ $lineIndex + $i]->toHex(),
                        $this->mapping['set_str_offset_1']['desc']
                    ];
                }




                $result[$lineIndex + 3] = [
                    $lines[ $lineIndex + 3]->toHex(),
                    'Offset in byte'
                ];
            }

            if (
                $line->toBinary() == $this->mapping['set_str_offset_1_a']['hex'][0] &&
                isset($lines[ $lineIndex + 1]) && $lines[ $lineIndex + 1]->toBinary() == $this->mapping['set_str_offset_1_a']['hex'][1] &&
                isset($lines[ $lineIndex + 2]) && $lines[ $lineIndex + 2]->toBinary() == $this->mapping['set_str_offset_1_a']['hex'][2]
            ){

                for($i = 0; $i <= 2; $i++){
                    $result[$lineIndex + $i] = [
                        $lines[ $lineIndex + $i]->toHex(),
                        $this->mapping['set_str_offset_1_a']['desc']
                    ];
                }

                $result[$lineIndex + 3] = [
                    $lines[ $lineIndex + 3]->toHex(),
                    'Offset in byte'
                ];
            }

        }

    }

    private function mapStatementLineOffset(array $lines, &$result ){
        /** @var Binary[] $lines */

        foreach ($lines as $lineIndex => $line) {

            if (
                $line->toBinary() == $this->mapping['statement_line_offset']['hex'][0]
            ){

                $result[$lineIndex] = [
                    $lines[ $lineIndex]->toHex(),
                    $this->mapping['statement_line_offset']['desc']
                ];

                $result[$lineIndex + 1] = [
                    $lines[ $lineIndex + 1]->toHex(),
                    sprintf('Offset (line number %s)', $lines[ $lineIndex + 1]->toInt() / 4)
                ];
            }

        }

        foreach ($lines as $lineIndex => $line) {

            if (
                $line->toBinary() == $this->mapping['statement_repeat_offset']['hex'][0]
            ){

                $result[$lineIndex] = [
                    $lines[ $lineIndex]->toHex(),
                    $this->mapping['statement_repeat_offset']['desc']
                ];

                $result[$lineIndex + 1] = [
                    $lines[ $lineIndex + 1]->toHex(),
                    sprintf('Offset (line number %s)', $lines[ $lineIndex + 1]->toInt() / 4)
                ];
            }

        }

    }

    private function mapStringOffset2(array $lines, &$result ){
        /** @var Binary[] $lines */

        foreach ($lines as $lineIndex => $line) {

            if (
                $line->toBinary() == $this->mapping['set_str_offset_2']['hex'][0] &&
                isset($lines[ $lineIndex + 1]) && $lines[ $lineIndex + 1]->toBinary() == $this->mapping['set_str_offset_2']['hex'][1] &&
                isset($lines[ $lineIndex + 2]) && $lines[ $lineIndex + 2]->toBinary() == $this->mapping['set_str_offset_2']['hex'][2]
            ){

                for($i = 0; $i <= 2; $i++){
                    $result[$lineIndex + $i] = [
                        $lines[ $lineIndex + $i]->toHex(),
                        $this->mapping['set_str_offset_2']['desc']
                    ];

                }


                $result[$lineIndex + 3] = [
                    $lines[ $lineIndex + 3]->toHex(),
                    'Offset in byte'
                ];


            }

        }

    }

    private function mapStringOffset3(array $lines, &$result ){
        /** @var Binary[] $lines */

        foreach ($lines as $lineIndex => $line) {

            if (
                $line->toBinary() == $this->mapping['set_str_offset_3']['hex'][0] &&
                isset($lines[ $lineIndex + 1]) && $lines[ $lineIndex + 1]->toBinary() == $this->mapping['set_str_offset_3']['hex'][1] &&
                isset($lines[ $lineIndex + 2]) && $lines[ $lineIndex + 2]->toBinary() == $this->mapping['set_str_offset_3']['hex'][2]
            ){

                for($i = 0; $i <= 2; $i++){
                    $result[$lineIndex + $i] = [
                        $lines[ $lineIndex + $i]->toHex(),
                        $this->mapping['set_str_offset_3']['desc']
                    ];

                }


                $result[$lineIndex + 3] = [
                    $lines[ $lineIndex + 3]->toHex(),
                    'Offset in byte'
                ];

                $result[$lineIndex + 4] = [
                    $lines[ $lineIndex + 4]->toHex(),
                    'Move str pointer'
                ];

                $result[$lineIndex + 5] = [
                    $lines[ $lineIndex + 5]->toHex(),
                    'Move str pointer'
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

    private function mapStatementNot(array $lines, &$result ){
        /** @var Binary[] $lines */

        foreach ($lines as $lineIndex => $line) {

            if (
                $line->toBinary() == $this->mapping['statement_not']['hex'][0] &&
                isset($lines[ $lineIndex + 1]) && $lines[ $lineIndex + 1]->toBinary() == $this->mapping['statement_not']['hex'][1] &&
                isset($lines[ $lineIndex + 2]) && $lines[ $lineIndex + 2]->toBinary() == $this->mapping['statement_not']['hex'][2]
            ){

                for($i = 0; $i <= 2; $i++){
                    $result[$lineIndex + $i] = [
                        $lines[ $lineIndex + $i]->toHex(),
                        $this->mapping['statement_not']['desc']
                    ];

                }

            }

        }

    }


    private function mapReserveBytes(array $lines, &$result ){
        /** @var Binary[] $lines */

        foreach ($lines as $lineIndex => $line) {

            if (
                $line->toBinary() == $this->mapping['reserve_bytes']['hex'][0] &&
                isset($lines[ $lineIndex + 1]) && $lines[ $lineIndex + 1]->toBinary() == $this->mapping['reserve_bytes']['hex'][1]
            ){

                for($i = 0; $i <= 1; $i++){
                    $result[$lineIndex + $i] = [
                        $lines[ $lineIndex + $i]->toHex(),
                        $this->mapping['reserve_bytes']['desc']
                    ];

                }

                $result[$lineIndex + 2] = [
                    $lines[ $lineIndex + 2]->toHex(),
                    'Offset in byte'
                ];
            }

        }

    }


    private function mapReadHeaderVar(array $lines, &$result ){
        /** @var Binary[] $lines */

        foreach ($lines as $lineIndex => $line) {

            if (
                $line->toBinary() == $this->mapping['read_header_var']['hex'][0] &&
                isset($lines[ $lineIndex + 1]) && $lines[ $lineIndex + 1]->toBinary() == $this->mapping['read_header_var']['hex'][1] &&
                isset($lines[ $lineIndex + 2]) && $lines[ $lineIndex + 2]->toBinary() == $this->mapping['read_header_var']['hex'][2]
            ){

                for($i = 0; $i <= 2; $i++){
                    $result[$lineIndex + $i] = [
                        $lines[ $lineIndex + $i]->toHex(),
                        $this->mapping['read_header_var']['desc']
                    ];

                }

                $result[$lineIndex + 3] = [
                    $lines[ $lineIndex + 3]->toHex(),
                    'Offset'
                ];
            }

        }

    }
    private function mapNestedReturn(array $lines, &$result ){
        /** @var Binary[] $lines */

        foreach ($lines as $lineIndex => $line) {

            if (
                $line->toBinary() == $this->mapping['nested_return']['hex'][0] &&
                isset($lines[ $lineIndex + 1]) && $lines[ $lineIndex + 1]->toBinary() == $this->mapping['nested_return']['hex'][1]
            ){

                for($i = 0; $i <= 1; $i++){
                    $result[$lineIndex + $i] = [
                        $lines[ $lineIndex + $i]->toHex(),
                        $this->mapping['nested_return']['desc']
                    ];

                }

            }

        }

    }

    private function mapParameterCalls(array $lines, &$result ){
        /** @var Binary[] $lines */

        foreach ($this->mapping['parameters'] as $paramName => $paramVariante){

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

//                    $valueHex = $lines[ $lineIndex + 2]->toHex();

//                    $value = "value";
//                    if ($valueHex == "49000000") $value = "Reference to THIS";
//                    if ($valueHex == "00000000") $value = "Bool false / int 0";
//                    if ($valueHex == "01000000") $value = "Bool true / int 1";
//
//                    if (
//                        isset($result[$lineIndex + 5]) && $lines[ $lineIndex + 5]->toBinary() == "\x10\x00\x00\x00" &&
//                        isset($result[$lineIndex + 6]) && $lines[ $lineIndex + 6]->toBinary() == "\x02\x00\x00\x00"
//                    ){
//                        $value = "Reference to a string";
//
//                    }


                    $value = $lines[ $lineIndex + 2]->toInt();

                    $result[$lineIndex + 2] = [
                        $lines[ $lineIndex + 2]->toHex(),
                        'value ' . $value
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
                            'nested string return result'
                        ];
                        $result[$lineIndex + 6] = [
                            $lines[ $lineIndex + 6]->toHex(),
                            'nested string return result'
                        ];

                    }

                }

            }

        }

    }


    private function mapLevelVarsBoolean(array $lines, &$result ){
//        foreach (Manhunt2::$levelVarBoolean as $levelVarName => $levelVarOffset){
//
//            $levelVarOffset = $levelVarOffset['offset'];
//
//            foreach ($lines as $lineIndex => $line) {
//
//                if ($line->toBinary() == hex2bin($levelVarOffset)){
//
//                    $result[$lineIndex] = [
//                        $line->toHex(),
//                        'LevelVar ' . $levelVarName
//                    ];
//
//                    if ($lines[ $lineIndex - 1]->toHex() == "1b000000"){
//                        $result[$lineIndex - 1] = [
//                            $lines[ $lineIndex - 1]->toHex(),
//                            'read LevelVar '
//                        ];
//
//                    }
//                }
//            }
//        }

    }

    private function mapFunctionCalls(array $lines, &$result ){
        /** @var Binary[] $lines */

        $funtions = Manhunt2::$functions;
        if (GAME == "mh1") $funtions = Manhunt::$functions;

        $funtions = array_merge($funtions, ManhuntDefault::$functions);

        foreach ($funtions as $functionName => $functionBinary){

            if (is_array($functionBinary)){
                if(isset($functionBinary['name'])) $functionName = $functionBinary['name'];
                $functionBinary = $functionBinary['offset'];
            }

            foreach ($lines as $lineIndex => $line) {

                if ($line->toBinary() == hex2bin($functionBinary)){


//
//                    if (
//                    !(
//                        ($lines[ $lineIndex - 1]->toHex() == "01000000") &&
//                        ($lines[ $lineIndex - 2]->toHex() == "04000000") &&
//                        ($lines[ $lineIndex - 3]->toHex() == "21000000")
//                    )
//                    ){
//
//                        if (
//                        ($lines[ $lineIndex - 1]->toHex() == "04000000") &&
//                        ($lines[ $lineIndex - 2]->toHex() == "16000000")
//                        ){
//                            continue;
//                        }
//

                        $result[$lineIndex] = [
                            $line->toHex(),
                            $functionName . ' Call'
                        ];
//
//                        if ($result[$lineIndex][0] == '73000000'){
//                            $lineIndex++;
//
//                            $result[$lineIndex] = [
//                                $result[$lineIndex + 1][0],
//                                'WriteDebug flush Call'
//                            ];
//                        }

//
//                        if (
//                            ($lines[ $lineIndex + 1]->toHex() == "10000000") &&
//                            ($lines[ $lineIndex + 2]->toHex() == "01000000")
//                        ){
//                            $result[$lineIndex + 1] = [
//                                $lines[ $lineIndex + 1]->toHex(),
//                                'nested call return result'
//                            ];
//
//                            $result[$lineIndex + 2] = [
//                                $lines[ $lineIndex + 2]->toHex(),
//                                'nested call return result'
//                            ];
//
//
//                        }
//                    }

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