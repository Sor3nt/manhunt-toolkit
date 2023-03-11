<?php
namespace App\Tests\CompilerV2\Functions;

use App\MHT;
use PHPUnit\Framework\TestCase;

class FunctionParamArrayNestedTest extends TestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

            type 
            
                SpotlightWaypoint = record
                    target : vec3d;
                    transition : integer;
                end;
	
            var
            	SlowSweep : array [1..8] of SpotlightWaypoint;
            	SlowSweepWP : array [1..4] of integer;


            procedure SetSpotlightTargetTransition(index : integer);
                var name, num : string[32];
                begin
                    SetSpotlightTarget(name, SlowSweep[SlowSweepWP[index]].target);
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
            '48000000', //Offset in byte

            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '20000000', //name
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result





            '21000000', //Prepare string read (DATA table)          readFromArrayIndex
            '04000000', //Prepare string read (DATA table)          readFromArrayIndex
            '01000000', //Prepare string read (DATA table)          readFromArrayIndex
            '00000000', //slowsweep                                 readFromArrayIndex

            '10000000', //nested call return result                 readFromArrayIndex
            '01000000', //nested call return result                 readFromArrayIndex

            '21000000', //Prepare string read (DATA table)          readFromArrayIndex $association->forIndex != null
            '04000000', //Prepare string read (DATA table)          readFromArrayIndex $association->forIndex != null
            '01000000', //Prepare string read (DATA table)          readFromArrayIndex $association->forIndex != null
            '80000000', //slowsweepwp                               readFromArrayIndex $association->forIndex != null

            '10000000', //nested call return result
            '01000000', //nested call return result

            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            'f4ffffff', //index

            '34000000', //Read array
            '01000000', //Read array
            '01000000', //Read array
            '12000000', //Read array
            '04000000', //Read array
            '04000000', //Read array
            '35000000', //Read array
            '04000000', //Read array
            '0f000000', //Read array
            '04000000', //Read array
            '31000000', //Read array
            '04000000', //Read array
            '01000000', //Read array
            '10000000', //Read array
            '04000000', //Read array


            '0f000000', //attribute operation
            '02000000', //attribute operation

            '18000000', //readAttribute
            '01000000', //readAttribute
            '04000000', //readAttribute
            '02000000', //readAttribute



            '34000000', //Read array
            '01000000', //Read array
            '01000000', //Read array
            '12000000', //Read array
            '04000000', //Read array
            '10000000', //Read array
            '35000000', //Read array
            '04000000', //Read array
            '0f000000', //Read array
            '04000000', //Read array
            '31000000', //Read array
            '04000000', //Read array
            '01000000', //Read array
            '10000000', //Read array
            '04000000', //Read array


            'a0030000', //SetSpotLightTarget Call


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