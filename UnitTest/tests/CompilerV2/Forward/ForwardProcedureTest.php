<?php
namespace App\Tests\CompilerV2\Forward;

use App\MHT;
use PHPUnit\Framework\TestCase;

class ForwardProcedureTest extends TestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
    
                entity
                    A01_Escape_Asylum : et_level;
    
                VAR
                    alreadyDone : boolean;
    
                procedure InitAI; FORWARD;
    
                script OnCreate;
                    begin
                        alreadyDone := FALSE;
                    end;
                    
                    
                
                procedure InitAI;
                    begin
                            alreadyDone := TRUE;
                    end;

            end.
            
        ";

        $expected = [
            // procedure start
            '10000000',
            '0a000000',
            '11000000',
            '0a000000',
            '09000000',

            '12000000', // init parameter
            '01000000', // init parameter
            '01000000', // value int 1
            '16000000', // assign to script var
            '04000000', // assign to script var
            '00000000', // save into alreadyDone
            '01000000', // assign


            // procedure end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3a000000',
            '04000000',


            // script start
            '10000000',
            '0a000000',
            '11000000',
            '0a000000',
            '09000000',

            '12000000', // init parameter
            '01000000', // init parameter
            '00000000', // value int 0
            '16000000', // assign to script var
            '04000000', // assign to script var
            '00000000', // save into alreadyDone
            '01000000', // assign


            // script end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3b000000',
            '00000000',

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