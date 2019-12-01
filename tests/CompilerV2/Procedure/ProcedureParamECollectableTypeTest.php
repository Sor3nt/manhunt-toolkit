<?php
namespace App\Tests\CompilerV2\Procedure;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProcedureParamECollectableTypeTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

            PROCEDURE ArmHunterWithMelee(HunterName : string; weapon : eCollectableType); FORWARD;

            script OnCreate;

                begin
                end;
            
                                    
            PROCEDURE ArmHunterWithMelee;
                var
                    Hunter : EntityPtr;
                begin
                    Hunter := getEntity(HunterName);
                    CreateInventoryItem(weapon, Hunter, true);
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



            //HunterName
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            'f0ffffff', //Offset

            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '00000000', //value 0

            '10000000', //nested call return result
            '01000000', //nested call return result

            '10000000', //nested string return result
            '02000000', //nested string return result

            '77000000', //GetEntity Call

            //Hunter :=
            '15000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '01000000', //unknown


            //weapon
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            'f4ffffff', //Offset

            '10000000', //nested call return result
            '01000000', //nested call return result

            //Hunter
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '04000000', //Offset

            '10000000', //nested call return result
            '01000000', //nested call return result

            //true
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '01000000', //value 1

            '10000000', //nested call return result
            '01000000', //nested call return result

            'ba000000', //CreateInventoryItem Call





            '11000000', //unknown
            '09000000', //unknown
            '0a000000', //unknown
            '0f000000', //unknown
            '0a000000', //unknown
            '3a000000', //unknown
            '0c000000', //unknown

            
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