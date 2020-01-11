<?php
namespace App\Tests\CompilerV2\Functions;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FunctionParamArrayObjectFirstEntryTest extends KernelTestCase
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


            procedure SetSpotlightTargetTransition(index : integer);
                var name, num : string[32];
                begin
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
            '34000000', //reserve bytes
            '09000000', //reserve bytes
            '48000000', //Offset in byte


            '21000000', //Prepare string read (DATA table)          readFromArrayIndex
            '04000000', //Prepare string read (DATA table)          readFromArrayIndex
            '01000000', //Prepare string read (DATA table)          readFromArrayIndex
            '00000000', //Strobes                                   readFromArrayIndex

            '10000000', //nested call return result                 readFromArrayIndex
            '01000000', //nested call return result                 readFromArrayIndex

            '13000000', //read from script var                      readFromArrayIndex $association->forIndex != null
            '01000000', //read from script var                      readFromArrayIndex $association->forIndex != null
            '04000000', //read from script var                      readFromArrayIndex $association->forIndex != null
            'f4ffffff', //index                                     readFromArrayIndex $association->forIndex != null

            '34000000', //Read array                                readFromArrayIndex
            '01000000', //Read array                                readFromArrayIndex
            '01000000', //Read array                                readFromArrayIndex
            '12000000', //Read array                                readFromArrayIndex
            '04000000', //Read array                                readFromArrayIndex
            '08000000', //Read array                                readFromArrayIndex
            '35000000', //Read array                                readFromArrayIndex
            '04000000', //Read array                                readFromArrayIndex
            '0f000000', //Read array                                readFromArrayIndex
            '04000000', //Read array                                readFromArrayIndex
            '31000000', //Read array                                readFromArrayIndex
            '04000000', //Read array                                readFromArrayIndex
            '01000000', //Read array                                readFromArrayIndex
            '10000000', //Read array                                readFromArrayIndex
            '04000000', //Read array                                readFromArrayIndex


            '0f000000', //readAttribute
            '02000000', //readAttribute
            '18000000', //readAttribute
            '01000000', //readAttribute
            '04000000', //readAttribute
            '02000000', //readAttribute

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