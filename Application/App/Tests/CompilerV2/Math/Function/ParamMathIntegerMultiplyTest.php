<?php
namespace App\Tests\CompilerV2\Functions;

use App\MHT;
use PHPUnit\Framework\TestCase;

class ParamMathIntegerMultiplyTest extends TestCase
{

    public function test()
    {


        $script = "
            scriptmain LevelScript;
                        
            entity
                A01_Escape_Asylum : et_level;

            script OnCreate;
                VAR
            		killableHunters : integer;
                begin
                
                    { 2x return } 
            		SetMaxScoreForLevel(killableHunters * 4);
                end;
            end.

        ";

        /*

//Sleep(1100 * 2);
12000000,parameter (read simple type (int/float...))
01000000,parameter (read simple type (int/float...))
4c040000,value 1100

10000000,nested call return result
01000000,nested call return result

12000000,parameter (temp int)
01000000,parameter (temp int)
02000000,value 2

0f000000,parameter (temp int)
04000000,parameter (temp int)

35000000,T_MULTIPLY
04000000,T_MULTIPLY

10000000,nested call return result
01000000,nested call return result

10000000,nested call return result
01000000,nested call return result
6a000000,Sleep Call

         */

        $expected = [

            // script start
            '10000000',
            '0a000000',
            '11000000',
            '0a000000',
            '09000000',



            '34000000',
            '09000000',
            '04000000',



                '13000000', //read from script var
                '01000000', //read from script var
                '04000000', //read from script var
                '04000000', //Offset

                '10000000', //nested call return result
                '01000000', //nested call return result

                '12000000', //parameter (temp int)
                '01000000', //parameter (temp int)
                '04000000', //value 4

                //multiply
                '0f000000', //parameter (temp int)
                '04000000', //parameter (temp int)
                '35000000', //T_MULTIPLY
                '04000000', //T_MULTIPLY

                '10000000', //nested call return result
                '01000000', //nested call return result

            '10000000', //nested call return result
            '01000000', //nested call return result

            '59030000', //SetMaxScoreForLevel Call

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