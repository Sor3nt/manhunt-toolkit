<?php
namespace App\Tests\CompilerV2\Math\Assign;

use App\MHT;
use PHPUnit\Framework\TestCase;

class ToVec3dYMathFloatSubTest extends TestCase
{

    public function test()
    {


        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

            script OnCreate;
                VAR
                    pos : vec3d;                

                begin
    				pos.y := pos.y - 0.6;
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




            //move ptr to vec3d variable pos
            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '0c000000', //Offset pos

            '10000000', //nested call return result
            '01000000', //nested call return result



            //read a specific attribute Y
            '0f000000', //unknown
            '01000000', //unknown

            '32000000', //unknown
            '01000000', //unknown
            '04000000', //object inner offset 4 (y)

            '10000000', //nested call return result
            '01000000', //nested call return result



            //move ptr to vec3d variable pos
            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '0c000000', //Offset pos

            '10000000', //nested call return result
            '01000000', //nested call return result



            //read a specific attribute Y
            '0f000000', //unknown
            '01000000', //unknown

            '32000000', //unknown
            '01000000', //unknown
            '04000000', //object inner offset 4 (y)

            '10000000', //nested call return result
            '01000000', //nested call return result


            //unknown operation
            '0f000000', //unknown
            '02000000', //unknown

            '18000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '02000000', //unknown

            '10000000', //nested call return result
            '01000000', //nested call return result

            //apply float 0.6
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '9a99193f', //value 0.6

            '10000000', //nested call return result
            '01000000', //nested call return result


            '51000000', //T_SUBSTRACTION

            '0f000000', //assign to object
            '02000000', //assign to object

            '17000000', //assign to object
            '04000000', //assign to object
            '02000000', //assign to object
            '01000000', //assign to object

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