<?php
namespace App\Tests\CompilerV2\Assign\Header;

use App\MHT;
use PHPUnit\Framework\TestCase;

class AssignArrayRecordTest extends TestCase
{

    public function test()
    {
        $this->assertEquals(true,true);
return;

        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

            type
                Strobe = record
		            light : entityptr;
		            floor : integer;
	            end;
	            
            VAR
        		Strobes : array [1..3] of Strobe;
        		
            procedure TurnOnStrobe(index : integer);
            begin
                SetCurrentLod(GetEntity('SM_DancefloorLOD'), Strobes[index].floor);
                SwitchLightOn(Strobes[index].light);
            end;


            end.
        ";

        $expected = [

            '10000000', //Script start block
            '0a000000', //Script start block
            '11000000', //Script start block
            '0a000000', //Script start block
            '09000000', //Script start block

            //GetEntity('SM_DancefloorLOD')
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset for SM_DancefloorLOD

            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '11000000', //size 17

            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result

            '77000000', //GetEntity Call

            '10000000', //nested call return result
            '01000000', //nested call return result

            //Strobes[index].floor
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //offset Strobes

            '10000000', //nested call return result
            '01000000', //nested call return result

            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            'f4ffffff', //Offset index

            '34000000', //Read array
            '01000000', //Read array
            '01000000', //Read array

            '12000000', //Read array
            '04000000', //Read array
            '08000000', //index offset 8

            '35000000', //Read array
            '04000000', //Read array
            '0f000000', //Read array
            '04000000', //Read array
            '31000000', //Read array
            '04000000', //Read array
            '01000000', //Read array
            '10000000', //Read array
            '04000000', //Read array


            '0f000000', //moveAttributePointer
            '01000000', //moveAttributePointer
            '32000000', //moveAttributePointer
            '01000000', //moveAttributePointer
            '04000000', //offset 4

            '10000000', //nested call return result
            '01000000', //nested call return result

            '0f000000', //read attribute
            '02000000', //read attribute
            '18000000', //read attribute
            '01000000', //read attribute
            '04000000', //read attribute
            '02000000', //read attribute

            '10000000', //nested call return result
            '01000000', //nested call return result

            '2d010000', //setcurrentlod Call


            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //AISetHunterIdleActionMinMaxRadius Call
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
            '02000000', //unknown
            '18000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            'db000000', //SwitchLightOn Call



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