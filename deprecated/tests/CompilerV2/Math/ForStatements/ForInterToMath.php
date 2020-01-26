<?php
namespace App\Tests\CompilerV2\Math\ForStatements;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ForInterToMath extends KernelTestCase
{

    public function test()
    {


        $script = "
            scriptmain LevelScript;
                        
            entity
                A01_Escape_Asylum : et_level;
                
            var
                lightBottomFloor : boolean;

            script FlickerBottomFloorLight;
                var i : integer;
                begin

                    for i := 0 to 2 + randnum(4) do begin
                       
                        Sleep(5 + randnum(5));
                    end;
                end;
                
            end.
            
        ";

        $expected = [

            '10000000', //Script start block
            '0a000000', //Script start block
            '11000000', //Script start block
            '0a000000', //Script start block
            '09000000', //Script start block
            '34000000', //reserve bytes
            '09000000', //reserve bytes
            '04000000', //Offset in byte

            '12000000', //unknown
            '01000000', //unknown
            '00000000', //nil Call
            '15000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '02000000', //value 2
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '04000000', //value 4
            '10000000', //nested call return result
            '01000000', //nested call return result
            '69000000', //RandNum Call
            '0f000000', //unknown
            '04000000', //unknown
            '31000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '13000000', //unknown
            '02000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '23000000', //unknown
            '01000000', //unknown
            '02000000', //unknown
            '41000000', //unknown
            'a8000000', //unknown
            '3c000000', //statement (init statement start offset)
            '08010000', //Offset (line number 2512)

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '05000000', //value 5
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '05000000', //value 5
            '10000000', //nested call return result
            '01000000', //nested call return result
            '69000000', //RandNum Call
            '0f000000', //unknown
            '04000000', //unknown
            '31000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //Sleep Call
            '2f000000', //unknown
            '04000000', //unknown
            '00000000', //nil Call
            '3c000000', //statement (init statement start offset)
            '3c000000', //Offset (line number 2398)
            '30000000', //unknown
            '04000000', //unknown
            '00000000', //nil Call
            '11000000', //Script end block
            '09000000', //Script end block
            '0a000000', //Script end block
            '0f000000', //Script end block
            '0a000000', //Script end block
            '3b000000', //Script end block
            '00000000', //nil Call

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