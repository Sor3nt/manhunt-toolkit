<?php
namespace App\Tests\CompilerV2\IfStatement;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IfAndOrNestedTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

            VAR
            	leaveCutText : boolean;
            	test : vec3d;
            	str : string[32];

            script OnCreate;

                begin
                
                    if (
                        (not leaveCutText) and 
                        (
                            (IsCutSceneInProgress) or 
                            (IsExecutionInProgress)
                        )
                    ) then KillGameText;            


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


            //leaveCutText
            '14000000', //Read VAR from header
            '01000000', //Read VAR from header
            '04000000', //Read VAR from header
            '00000000', //Offset leaveCutText

            //not
            '29000000', //NOT
            '01000000', //NOT
            '01000000', //NOT

            '10000000', //nested call return result
            '01000000', //nested call return result



            'f5020000', //IsCutSceneInProgress Call

            '10000000', //nested call return result
            '01000000', //nested call return result

            '51020000', //IsExecutionInProgress Call



            '0f000000', //apply to operator
            '04000000', //apply to operator

            '27000000', //statement (OR operator)
            '01000000', //statement (OR operator)
            '04000000', //statement (OR operator)

            '0f000000', //apply operator
            '04000000', //apply operator



            '25000000', //statement (AND operator)
            '01000000', //statement (AND operator)
            '04000000', //statement (AND operator)

            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '88000000', //Offset (line number 6772)

            '08010000', //KillGameText Call



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