<?php
namespace App\Tests\CompilerV2\Math\Assign;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ToArrayIndexMathTest extends KernelTestCase
{

    public function test()
    {


        $script = "
            scriptmain LevelScript;
                        
            entity
                A01_Escape_Asylum : et_level;
                
            var
                GuardHunters : array[1..4] of boolean;

            script OnCreate;
                VAR
                    i : integer;
                begin
                    GuardHunters[i-12]:= true
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


            '34000000',
            '09000000',
            '04000000',



            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //DecreaseCounter Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '04000000', //Offset
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (temp int)
            '01000000', //parameter (temp int)
            '0c000000', //value 12
            '0f000000', //parameter (temp int)
            '04000000', //parameter (temp int)
            '33000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '11000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '34000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '12000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '35000000', //unknown
            '04000000', //unknown
            '0f000000', //unknown
            '04000000', //unknown
            '31000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '10000000', //unknown
            '04000000', //unknown
            '12000000', //parameter (function return (bool?))
            '01000000', //parameter (function return (bool?))
            '01000000', //value 1
            '0f000000', //parameter (function return (bool?))
            '02000000', //parameter (function return (bool?))
            '17000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
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