<?php
namespace App\Tests\CompilerV2\Forward;

use App\MHT;
use PHPUnit\Framework\TestCase;

class ForwardProcedureMultiVarParamTest extends TestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;
                
            PROCEDURE SpawnHunterWithM16( HunterType, LeaderName, SubPackName, BaseName : String; num : integer; var pos : Vec3D ); FORWARD;

            script Patrol;
            begin

            end;

            procedure SpawnHunterWithM16;
            begin

            end;
            
            end.

        ";

        $expected = [

            "10000000", //procedure start block
            "0a000000", //procedure start block
            "11000000", //procedure start block
            "0a000000", //procedure start block
            "09000000", //procedure start block


            "11000000", //procedure end block
            "09000000", //procedure end block
            "0a000000", //procedure end block
            "0f000000", //procedure end block
            "0a000000", //procedure end block
            "3a000000", //procedure end block (line 34)
            "1c000000", //procedure end block


            "10000000", //Script start block
            "0a000000", //Script start block
            "11000000", //Script start block
            "0a000000", //Script start block
            "09000000", //Script start block


            "11000000", //Script end block
            "09000000", //Script end block
            "0a000000", //Script end block
            "0f000000", //Script end block
            "0a000000", //Script end block
            "3b000000", //Script end block
            "00000000", //Script end block

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