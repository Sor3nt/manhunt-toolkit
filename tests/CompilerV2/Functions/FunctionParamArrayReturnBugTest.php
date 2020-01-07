<?php
namespace App\Tests\CompilerV2\Functions;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FunctionParamArrayReturnBugTest extends KernelTestCase
{

    public function test()
    {


        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

            type 
            
                Strobe = record
                    light : entityptr;
                    floor : integer;
                end;
	
            var
            	Strobes : array [1..3] of Strobe;


            procedure TurnOnStrobe(index : integer);
                begin
                    SetCurrentLod(GetEntity('SM_DancefloorLOD'), Strobes[index].floor);
                end;

            end.
        ";

        $expected = [
            '10000000', //Script start block
            '0a000000', //Script start block
            '11000000', //Script start block
            '0a000000', //Script start block
            '09000000', //Script start block
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '11000000', //value 17
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '77000000', //GetEntity Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '14000000', //AISetHunterIdleActionMinMaxRadius Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            'f4ffffff', //Offset
            '34000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '12000000', //unknown
            '04000000', //unknown
            '08000000', //unknown
            '35000000', //unknown
            '04000000', //unknown
            '0f000000', //unknown
            '04000000', //unknown
            '31000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '10000000', //unknown
            '04000000', //unknown
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
            '2d010000', //setcurrentlod Call



            '11000000', //unknown
            '09000000', //unknown
            '0a000000', //unknown
            '0f000000', //unknown
            '0a000000', //unknown
            '3a000000', //unknown
            '08000000', //unknown
            
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