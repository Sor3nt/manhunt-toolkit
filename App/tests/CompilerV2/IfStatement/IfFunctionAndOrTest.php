<?php
namespace App\Tests\CompilerV2\IfStatement;

use App\MHT;
use PHPUnit\Framework\TestCase;

class IfFunctionAndOrTest extends TestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

            var
                alreadyDone : boolean;

            script OnCreate;
                VAR
                    inTriggerCheck : boolean;
                begin


    
                    if 
                        (
                            InsideTrigger(this, GetPlayer)
                        ) and (
                            (IsPlayerRunning) OR 
                            (IsPlayerSprinting) OR 
                            (IsPlayerWalking)
                        ) OR (
                            InsideTriggerType(this, EC_HUNTER)
                        ) then
                        inTriggerCheck := TRUE
                    else
                        inTriggerCheck := FALSE;
    


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


            '34000000', //reserve bytes
            '09000000', //reserve bytes
            '04000000', //Offset in byte

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '49000000', //value 73

            '10000000', //nested call return result
            '01000000', //nested call return result

            '8a000000', //GetPlayer Call

            '10000000', //nested call return result
            '01000000', //nested call return result
            'a5000000', //InsideTrigger Call



            '10000000', //nested call return result
            '01000000', //nested call return result
            'ee020000', //IsPlayerRunning Call


            '10000000', //nested call return result
            '01000000', //nested call return result
            'ef020000', //IsPlayerSprinting Call


            '0f000000', //unknown
            '04000000', //unknown
            '27000000', //statement (OR operator)
            '01000000', //statement (OR operator)
            '04000000', //statement (OR operator)

            '10000000', //nested call return result
            '01000000', //nested call return result

            'ed020000', //IsPlayerWalking Call

            '0f000000', //unknown
            '04000000', //unknown
            '27000000', //statement (OR operator)
            '01000000', //statement (OR operator)
            '04000000', //statement (OR operator)
            '0f000000', //unknown
            '04000000', //unknown
            '25000000', //statement (AND operator)
            '01000000', //statement (AND operator)
            '04000000', //statement (AND operator)
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '49000000', //value 73
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '1f000000', //value 31
            '10000000', //nested call return result
            '01000000', //nested call return result
            'd6010000', //unknown
            '0f000000', //unknown
            '04000000', //unknown
            '27000000', //statement (OR operator)
            '01000000', //statement (OR operator)
            '04000000', //statement (OR operator)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '24010000', //Offset (line number 2300)
            '12000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '15000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '3c000000', //statement (init statement start offset)
            '40010000', //Offset (line number 2307)
            '12000000', //unknown
            '01000000', //unknown
            '00000000', //nil Call
            '15000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '01000000', //unknown



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