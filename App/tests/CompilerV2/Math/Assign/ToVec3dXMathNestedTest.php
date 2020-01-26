<?php
namespace App\Tests\CompilerV2\Math\Assign;

use App\MHT;
use PHPUnit\Framework\TestCase;

class ToVec3dXMathNestedTest extends TestCase
{

    public function test()
    {

//        $this->assertEquals(true,true);
//        return;

        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

            var 
                StashPos, StashView, CamPos1 : vec3d;

            script OnCreate;
             

                begin
    				CamPos1.x := ((StashPos.x) 	+	(StashView.x * (-1.0)));
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
            '18000000', //aiaddleaderenemy Call
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
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '0c000000', //IsNamedItemInInventory Call
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
            '0000803f', //value 1065353216
            '10000000', //nested call return result
            '01000000', //nested call return result
            '4f000000', //turn prev number into negative
            '32000000', //turn prev number into negative
            '09000000', //turn prev number into negative
            '04000000', //turn prev number into negative
            '10000000', //nested call return result
            '01000000', //nested call return result
            '52000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '50000000', //unknown
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