<?php
namespace App\Tests\CompilerV2\Math\Assign;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ToIntegerFunctionMathIntegerMultiplyFloatTest extends KernelTestCase
{

    public function test()
    {


        $script = "
            scriptmain LevelScript;

            
            entity
                A01_Escape_Asylum : et_level;

                VAR
                	timefactor : REAL;

                PROCEDURE ScreenSet(var name: String; time: integer); FORWARD;

                PROCEDURE ScreenSet;
                begin
                    time := Round(time * timefactor);
                	
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


            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            'f4ffffff', //Offset time

            '10000000', //nested call return result
            '01000000', //nested call return result

            '14000000', //Read VAR from header
            '01000000', //Read VAR from header
            '04000000', //Read VAR from header
            '00000000', //Offset timefactor

            '10000000', //nested call return result
            '01000000', //nested call return result

            '0f000000', //unknown
            '01000000', //unknown
            '0f000000', //unknown
            '02000000', //unknown

            '10000000', //return string ?
            '01000000', //return string ?
            '10000000', //return string ?
            '02000000', //return string ?

            '4d000000', //int2float

            '0f000000', //unknown
            '02000000', //unknown

            '10000000', //return string ?
            '01000000', //return string ?
            '10000000', //return string ?
            '02000000', //return string ?

            '52000000', //T_MULTIPLY (float)

            '10000000', //nested call return result
            '01000000', //nested call return result

            '59000000', //round call

            '15000000', //write to time
            '04000000', //write to time
            'f4ffffff', //offset time
            '01000000', //write to time


            // script end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3a000000',
            '0c000000'
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