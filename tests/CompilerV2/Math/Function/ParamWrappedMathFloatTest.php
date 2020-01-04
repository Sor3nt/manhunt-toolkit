<?php
namespace App\Tests\CompilerV2\Functions;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ParamWrappedMathFloatTest extends KernelTestCase
{

    public function test()
    {


        $script = "
            scriptmain LevelScript;
                        
            entity
                A01_Escape_Asylum : et_level;

            VAR
                pos : vec3d;

            script OnCreate;
                begin
                    SetVector(pos, (pos.x - 0.6), pos.y + 0.2, (pos.z - 0.5));

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


            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte
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
            '9a99193f', //value 1058642330
            '10000000', //nested call return result
            '01000000', //nested call return result
            '51000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte
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
            'cdcc4c3e', //value 1045220557
            '10000000', //nested call return result
            '01000000', //nested call return result
            '50000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '01000000', //unknown
            '32000000', //unknown
            '01000000', //unknown
            '08000000', //unknown
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
            '0000003f', //value 1056964608
            '10000000', //nested call return result
            '01000000', //nested call return result
            '51000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '84010000', //SetVector Call
            
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