<?php
namespace App\Tests\CompilerV2\Functions;

use App\MHT;
use PHPUnit\Framework\TestCase;

class ParamMathFunctionReturnIntegerAddTest extends TestCase
{

    public function test()
    {


        $script = "
            scriptmain LevelScript;
                        
            entity
                A01_Escape_Asylum : et_level;

            script OnCreate;
                begin

                    { 1x return } 
                    Sleep(7 + randnum(15));

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


            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '07000000', //value 7

            '10000000', //nested call return result
            '01000000', //nested call return result

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '0f000000', //value 15

            '10000000', //nested call return result
            '01000000', //nested call return result

            '69000000', //RandNum Call

            '0f000000', //unknown
            '04000000', //unknown
            '31000000', //unknown
            '01000000', //unknown
            '04000000', //unknown

            '10000000', //nested call return result
            '01000000', //nested call return result

            '6a000000', //Sleep Call

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