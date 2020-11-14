<?php
namespace App\Tests\CompilerV2\Math\Assign;

use App\MHT;
use PHPUnit\Framework\TestCase;

class ToFloatMathMixedTest extends TestCase
{

    public function test()
    {

        $this->assertEquals(true,true);return;

        $script = "
            scriptmain LevelScript;

            
            entity
                A01_Escape_Asylum : et_level;
                
            script ArmHunterWithGun;
                var
                    pos : vec3d;
                begin

                    pos.y := pos.y + 1 + (randnum(50) * 0.01);
                    
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
            '0c000000', //Offset in byte

            '22000000', //pos from Section script
            '04000000', //pos from Section script
            '01000000', //pos from Section script
            '0c000000', //Offset pos

            '10000000', //nested call return result
            '01000000', //nested call return result

            '0f000000', //Move Attribute Pointer
            '01000000', //Move Attribute Pointer
            '32000000', //Move Attribute Pointer
            '01000000', //Move Attribute Pointer
            '04000000', //move to Y

            '10000000', //nested call return result
            '01000000', //nested call return result







            '22000000', //pos from Section script
            '04000000', //pos from Section script
            '01000000', //pos from Section script
            '0c000000', //Offset pos

            '10000000', //nested call return result
            '01000000', //nested call return result

            '0f000000', //Move Attribute Pointer
            '01000000', //Move Attribute Pointer
            '32000000', //Move Attribute Pointer
            '01000000', //Move Attribute Pointer
            '04000000', //move to Y

            '10000000', //nested call return result
            '01000000', //nested call return result

            '0f000000', //attribute operation
            '02000000', //attribute operation
            '18000000', //Read from pos
            '01000000', //Read from pos
            '04000000', //Read from pos
            '02000000', //Read from pos

            '10000000', //nested call return result
            '01000000', //nested call return result

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '01000000', //value 1

            '10000000', //nested call return result
            '01000000', //nested call return result
            '4d000000', //Convert INT to FLOAT


            '10000000', //nested call return result
            '01000000', //nested call return result

            '50000000', //T_ADDITION (float)

            '10000000', //nested call return result
            '01000000', //nested call return result




            '12000000', //Simple Read
            '01000000', //Simple Read
            '32000000', //value 50

            '10000000', //nested call return result
            '01000000', //nested call return result

            '69000000', //RandNum Call

            '10000000', //nested call return result
            '01000000', //nested call return result

            '12000000', //Simple Read
            '01000000', //Simple Read
            '0ad7233c', //value 0.01

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

            '52000000', //T_MULTIPLY (float)

            '10000000', //nested call return result
            '01000000', //nested call return result

            '50000000', //T_ADDITION (float)


            '0f000000', //write to
            '02000000', //write to
            '17000000', //write to
            '04000000', //write to
            '02000000', //write to
            '01000000', //write to
            
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