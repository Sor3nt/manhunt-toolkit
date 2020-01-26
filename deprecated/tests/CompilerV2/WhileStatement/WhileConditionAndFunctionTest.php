<?php
namespace App\Tests\CompilerV2\WhileStatement;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WhileConditionAndFunctionTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;
                
            var
                Invul : integer;
            
            script TurnMeInvulnerable;
            begin
                Invul := TRUE;
                while Invul = TRUE AND IsEntityAlive('SobbingWoman(hunter)') do
                begin
                    if GetDamage(GetPlayer) < 30 then
                    begin
                        SetEntityInvulnerable(GetPlayer, TRUE);
                        Invul := FALSE;
                    end;
                end;
            end;

            end.
        ";

        $expected = [
            '10000000', //Script start block
            '0a000000', //Script start block
            '11000000', //Script start block
            '0a000000', //Script start block
            '09000000', //Script start block

            //Invul := TRUE;
            '12000000', //parameter (access script var)
            '01000000', //parameter (access script var)
            '01000000', //value 1 / TRUE

            '16000000', //assign
            '04000000', //assign
            '18000000', //invul offset
            '01000000', //assign


            //Invul = TRUE

            //Invul
            '14000000', //Read VAR from header
            '01000000', //Read VAR from header
            '04000000', //Read VAR from header
            '18000000', // invul

            '10000000', //nested call return result
            '01000000', //nested call return result

            // true
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '01000000', //value 1

            '10000000', //nested call return result
            '01000000', //nested call return result

            // IsEntityAlive('SobbingWoman(hunter)')
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //SobbingWoman(hunter)

            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21

            '10000000', //nested call return result
            '01000000', //nested call return result

            '10000000', //nested string return result
            '02000000', //nested string return result

            'aa010000', //IsEntityAlive Call


            '0f000000', //unknown
            '04000000', //unknown

            '25000000', //statement (AND operator)
            '01000000', //statement (AND operator)
            '04000000', //statement (AND operator)
            '0f000000', //unknown
            '04000000', //unknown

            '23000000', //statement (core)
            '04000000', //statement (core)
            '01000000', //statement (core)
            '12000000', //statement (core)
            '01000000', //statement (core)
            '01000000', //statement (core)
            '3f000000', //statement (init start offset)
            'd4000000', //Offset (line number 1652)
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '9c010000', //Offset (line number 1702)
            '8a000000', //GetPlayer Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '84000000', //GetDamage Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (temp int)
            '01000000', //parameter (temp int)
            '1e000000', //value 30
            '0f000000', //parameter (temp int)
            '04000000', //parameter (temp int)
            '23000000', //statement (core)
            '04000000', //statement (core)
            '01000000', //statement (core)
            '12000000', //statement (core)
            '01000000', //statement (core)
            '01000000', //statement (core)
            '3d000000', //statement (core)(operator smaller)
            '40010000', //statement (core)( Offset )
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '94010000', //Offset (line number 1700)
            '8a000000', //GetPlayer Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '01000000', //value 1
            '10000000', //nested call return result
            '01000000', //nested call return result
            '5e010000', //SetEntityInvulnerable Call
            '12000000', //parameter (access script var)
            '01000000', //parameter (access script var)
            '00000000', //value 0
            '16000000', //parameter (access script var)
            '04000000', //parameter (access script var)
            '18000000', //EndAudioLooped Call
            '01000000', //unknown
            '3c000000', //statement (init statement start offset)
            '30000000', //Offset (line number 1611)
            '11000000', //Script end block
            '09000000', //Script end block
            '0a000000', //Script end block
            '0f000000', //Script end block
            '0a000000', //Script end block
            '3b000000', //Script end block
            '00000000', //nil Call
            
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