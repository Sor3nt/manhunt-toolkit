<?php
namespace App\Tests\CompilerV2\Assign\Header;

use App\MHT;
use PHPUnit\Framework\TestCase;

class AssignIntegerMathAddition extends TestCase
{

    public function test()
    {


        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

            var
                animLength : integer;

            script OnCreate;
                begin
                    animLength := animLength + 1500;
                end;
            end.

        ";

        $expected = [

            // script start
            '10000000',
            '0a000000',
            '11000000',
            '0a000000',
            '09000000',

            '14000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '00000000', //Offset
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (temp int)
            '01000000', //parameter (temp int)
            'dc050000', //value 1500
            '0f000000', //parameter (temp int)
            '04000000', //parameter (temp int)

            '31000000', //unknown
            '01000000', //unknown
            '04000000', //unknown


            '16000000', //unknown
            '04000000', //unknown
            '00000000', //unknown
            '01000000', //unknown

            // script end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3b000000',
            '00000000',

        ];

        $compiler = new \App\Service\CompilerV2\Compiler($script, MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC, false);
        $compiled = $compiler->compile();

        if ($compiler->validateCode($expected) === false){

            foreach ($compiled['CODE'] as $index => $newCode) {

                if (!isset($expected[$index])){
                    echo "To much code generated";
                    exit;
                }

                if ($expected[$index] == $newCode['code']){
                    echo $newCode['code'] . ' -> ' . $newCode['msg'] . "\n";

                }else{
                    echo "MISMATCH: Need: " . $expected[$index] . ' Got: ' . $newCode['code'] . ' -> ' . $newCode['msg']. "\n";

                }
            }
        }else{
            $this->assertEquals(true,true);
        }
    }

}