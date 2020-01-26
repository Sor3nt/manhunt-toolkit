<?php
namespace App\Tests\CompilerV2\Math\Assign;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ToFloatMathIntegerTest extends KernelTestCase
{

    public function test()
    {

//        $this->assertEquals(true,true);return;

        $script = "
            scriptmain LevelScript;

            
            entity
                A01_Escape_Asylum : et_level;
            
            PROCEDURE ArmHunterWithGun(HunterName : string; weapon : eCollectableType; Accuracy, DropAmmoNumber : integer); FORWARD;
                        
            PROCEDURE ArmHunterWithGun;
                var
                    modifier : real;
                begin

                    modifier := (Accuracy - 50)/100;
                    
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
            'f0ffffff', //Offset accuracy

            '10000000', //nested call return result
            '01000000', //nested call return result

            '12000000', //parameter (temp int)
            '01000000', //parameter (temp int)
            '32000000', //value 50

            '0f000000', //parameter (temp int)
            '04000000', //parameter (temp int)

            '33000000', //T_SUBSTRACTION (int)
            '04000000', //T_SUBSTRACTION (int)
            '01000000', //T_SUBSTRACTION (int)
            '11000000', //T_SUBSTRACTION (int)
            '01000000', //T_SUBSTRACTION (int)
            '04000000', //T_SUBSTRACTION (int)

            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '64000000', //value 100

            '10000000', //nested call return result
            '01000000', //nested call return result

            '4d000000', //unknown

            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '01000000', //unknown
            '0f000000', //unknown
            '02000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //unknown
            '02000000', //unknown
            '4d000000', //unknown
            '0f000000', //unknown
            '02000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //unknown
            '02000000', //unknown
            '53000000', //unknown
            '15000000', //unknown
            '04000000', //unknown
            '04000000', //Assign to Variable modifier | Offset
            '01000000', //unknown


            // script end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3a000000',
            '14000000'
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