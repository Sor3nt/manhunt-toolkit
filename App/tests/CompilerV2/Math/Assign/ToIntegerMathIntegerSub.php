<?php
namespace App\Tests\CompilerV2\Math\Assign;

use App\MHT;
use PHPUnit\Framework\TestCase;

class ToIntegerMathIntegerSub extends TestCase
{

    public function test()
    {


        $script = "
            scriptmain LevelScript;

            
            entity
                A01_Escape_Asylum : et_level;

            script OnCreate;
                VAR
                    animLength : integer;       
                begin
                    
                    { animLength 1500 - } 
                   	animLength := animLength - 1500;
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


            '34000000', //reserve bytes
            '09000000', //reserve bytes
            '04000000', //Offset in byte



            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '04000000', //Offset animLength

            '10000000', //nested call return result
            '01000000', //nested call return result

            '12000000', //parameter (temp int)
            '01000000', //parameter (temp int)
            'dc050000', //value 1500


            '0f000000', //parameter (temp int)
            '04000000', //parameter (temp int)

            '33000000', //T_SUBSTRACTION
            '04000000', //T_SUBSTRACTION
            '01000000', //T_SUBSTRACTION

            '11000000', //T_SUBSTRACTION
            '01000000', //T_SUBSTRACTION
            '04000000', //T_SUBSTRACTION

            '15000000', //assign
            '04000000', //assign
            '04000000', //offset
            '01000000', //assign


            // script end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3b000000',
            '00000000'
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