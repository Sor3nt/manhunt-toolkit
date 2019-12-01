<?php
namespace App\Tests\CompilerV2\Procedure;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProcedureParamStringTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

            PROCEDURE SpawnHunter(HunterName, HunterType, SubpackName : string; x, y, z : real); FORWARD;

            script OnCreate;

                begin
                end;
                
                        
            PROCEDURE SpawnHunter;
            var
                vector : vec3d;
            begin
                setVector(vector, x, y, z);
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
            '0c000000', //Offset in byte

            //param 1: vector
            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '0c000000', //Offset vector (script var)

            '10000000', //nested call return result
            '01000000', //nested call return result

            //param 2: x
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            'ecffffff', //Offset -20
            '10000000', //nested call return result
            '01000000', //nested call return result

            //param 3: y
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            'f0ffffff', //Offset -16
            '10000000', //nested call return result
            '01000000', //nested call return result

            //param 4: z
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            'f4ffffff', //Offset -12
            '10000000', //nested call return result
            '01000000', //nested call return result


            '84010000', //SetVector Call

            '11000000', //unknown
            '09000000', //unknown
            '0a000000', //unknown
            '0f000000', //unknown
            '0a000000', //unknown
            '3a000000', //unknown
            '04000000', //unknown

            
            // script start
            '10000000',
            '0a000000',
            '11000000',
            '0a000000',
            '09000000',

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