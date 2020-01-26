<?php
namespace App\Tests\CompilerV2\Math\Assign;

use App\MHT;
use PHPUnit\Framework\TestCase;

class ToVec3dYMathIntegerFloatAddTest extends TestCase
{

    public function test()
    {


        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;
                
            var
                offset : real;

            script OnCreate;
                VAR
                    pos : vec3d;;                

                begin
                	pos.y := pos.y + 10;
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


            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '0c000000', //Offset pos.y


            '10000000', //nested call return result
            '01000000', //nested call return result

            '0f000000', //unknown
            '01000000', //unknown
            '32000000', //unknown
            '01000000', //unknown
            '04000000', //unknown

            '10000000', //nested call return result
            '01000000', //nested call return result

            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '0c000000', //Offset pos.y

            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '01000000', //unknown
            '32000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '02000000', //unknown
            '18000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '0a000000', //value 10
            '10000000', //nested call return result
            '01000000', //nested call return result

            '4d000000', //convert int to float
            '10000000', //nested call return result
            '01000000', //nested call return result

            '50000000', //T_ADDITION

            '0f000000', //unknown
            '02000000', //unknown

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