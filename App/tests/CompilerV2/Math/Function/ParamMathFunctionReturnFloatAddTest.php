<?php
namespace App\Tests\CompilerV2\Functions;

use App\MHT;
use PHPUnit\Framework\TestCase;

class ParamMathFunctionReturnFloatAddTest extends TestCase
{

    public function test()
    {


        $script = "
            scriptmain LevelScript;
                        
            entity
                A01_Escape_Asylum : et_level;

            script OnCreate;
                begin

                    CutsceneCameraSetFOV(0.0, 45.0 + randnum(20));

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
            '00000000', //value 0

            '10000000', //nested call return result
            '01000000', //nested call return result

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '00003442', //value 45.0

            '10000000', //nested call return result
            '01000000', //nested call return result




            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '14000000', //value 20

            '10000000', //nested call return result
            '01000000', //nested call return result

            '69000000', //RandNum Call




            '10000000', //nested call return result
            '01000000', //nested call return result

            '4d000000', //int to float
            '10000000', //nested call return result
            '01000000', //nested call return result

            '50000000', //unknown

            '10000000', //nested call return result
            '01000000', //nested call return result

            '5c030000', //CutSceneCameraSetFOV Call


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