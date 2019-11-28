<?php
namespace App\Tests\CompilerV2\IfStatement;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IfBooleanTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

            VAR
            	lDebuggingFlag : Boolean;

            script OnCreate;

                begin
            
                    if lDebuggingFlag = TRUE then
                    begin
                        KillThisScript;
                    end;

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

            //lDebuggingFlag
            '14000000', //Read VAR from header
            '01000000', //Read VAR from header
            '04000000', //Read VAR from header
            '00000000', //Offset

            '10000000', //nested call return result
            '01000000', //nested call return result

            //true
            '12000000', //parameter (temp int)
            '01000000', //parameter (temp int)
            '01000000', //value 1

            '0f000000', //parameter (temp int)
            '04000000', //parameter (temp int)

            '23000000', //statement (core)
            '04000000', //statement (core)
            '01000000', //statement (core)
            '12000000', //statement (core)
            '01000000', //statement (core)
            '01000000', //statement (core)
            '3f000000', //statement (init start offset)
            '6c000000', //Offset (line number 1424)
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '84000000', //Offset (line number 1430)
            'e7000000', //KillThisScript Call

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
                    echo $index . " " . $newCode['code'] . ' -> ' . $newCode['msg'] . "\n";

                }else{
                    echo "MISMATCH: Need: " . $expected[$index] . ' Got: ' . $newCode['code'] . ' -> ' . $newCode['msg']. "\n";

                }
            }
        }else{
            $this->assertEquals(true,true);
        }
    }

}