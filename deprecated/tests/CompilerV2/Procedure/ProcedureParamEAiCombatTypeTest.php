<?php
namespace App\Tests\CompilerV2\Procedure;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProcedureParamEAiCombatTypeTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

            PROCEDURE InitSubpack(SubpackName : string; CombatType : eAICombatType; HuntPlayer : boolean); FORWARD;

            script OnCreate;

                begin
                end;
            
                                                
            PROCEDURE InitSubpack;
                begin
                    AIaddSubpackForLeader('leader(leader)',  SubpackName);
                    AIsetSubpackCombatType('leader(leader)',  SubpackName, CombatType);
                    if HuntPlayer then AIAddGoalForSubPack('leader(leader)', SubpackName, 'huntPlayer');
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
            '0f000000', //value 15

            '10000000', //nested call return result
            '01000000', //nested call return result

            '10000000', //nested string return result
            '02000000', //nested string return result


            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            'ecffffff', //Offset

            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '00000000', //value 0

            '10000000', //nested call return result
            '01000000', //nested call return result

            '10000000', //nested string return result
            '02000000', //nested string return result

            '50010000', //aiaddsubpackforleader Call



            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte

            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0f000000', //value 15

            '10000000', //nested call return result
            '01000000', //nested call return result

            '10000000', //nested string return result
            '02000000', //nested string return result

            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            'ecffffff', //Offset

            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '00000000', //value 0

            '10000000', //nested call return result
            '01000000', //nested call return result

            '10000000', //nested string return result
            '02000000', //nested string return result

            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            'f0ffffff', //Offset

            '10000000', //nested call return result
            '01000000', //nested call return result

            '82010000', //aisetsubpackcombattype Call



            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            'f4ffffff', //Offset

            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '90010000', //Offset (line number 322)

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte

            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0f000000', //value 15

            '10000000', //nested call return result
            '01000000', //nested call return result

            '10000000', //nested string return result
            '02000000', //nested string return result

            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            'ecffffff', //Offset

            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '00000000', //value 0

            '10000000', //nested call return result
            '01000000', //nested call return result

            '10000000', //nested string return result
            '02000000', //nested string return result

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte

            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0b000000', //value 11

            '10000000', //nested call return result
            '01000000', //nested call return result

            '10000000', //nested string return result
            '02000000', //nested string return result

            '56010000', //aiaddgoalforsubpack Call





            '11000000', //unknown
            '09000000', //unknown
            '0a000000', //unknown
            '0f000000', //unknown
            '0a000000', //unknown
            '3a000000', //unknown
            '10000000', //unknown

            
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