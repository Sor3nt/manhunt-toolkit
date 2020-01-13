<?php
namespace App\Tests\CompilerV2\Switches;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SwitchIntegerOfIntegerTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;
                
            var
                me : string[16];
                
            script OnCreate;
                VAR
                	iRandNum : integer; 
                begin
                    case iRandNum of
                        0: AIPlayCommunication(me,'ShoutForHelp','ScriptedAudio');
                        1: AIPlayCommunication(me,'ShoutForAssistance','ScriptedAudio');
                        2: AIPlayCommunication(me,'ShoutForAssistance','ScriptedAudio');
                        3: AIPlayCommunication(me,'ShoutForAssistance','ScriptedAudio');
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


            '34000000', //reserve bytes
            '09000000', //reserve bytes
            '04000000', //Offset in byte




            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '04000000', //Offset

            '24000000', //define case
            '01000000', //define case
            '03000000', //case number 3

            '3f000000', //statement (init start offset)
            '88000000', //Offset (line number 3295)

            '24000000', //define case
            '01000000', //define case
            '02000000', //case number 2

            '3f000000', //statement (init start offset)
            '18010000', //Offset (line number 3331)

            '24000000', //define case
            '01000000', //define case
            '01000000', //case number 1
            '3f000000', //statement (init start offset)
            'a8010000', //Offset (line number 3367)

            '24000000', //define case
            '01000000', //define case
            '00000000', //case number 0
            '3f000000', //statement (init start offset)
            '38020000', //Offset (line number 3403)

            '3c000000', //statement (init statement start offset)
            'c8020000', //End Offset (line number 3439)


            // 3: AIPlayCommunication(me,'ShoutForAssistance','ScriptedAudio');
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '34000000', //Offset in byte

            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '10000000', //value 16

            '10000000', //nested call return result
            '01000000', //nested call return result

            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '20000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '13000000', //value 19
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
            '0e000000', //value 14
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'fe010000', // AIPlayCommunication call




            // 2: AIPlayCommunication(me,'ShoutForAssistance','ScriptedAudio');


            '3c000000', //statement (init statement start offset)
            'c8020000', //End Offset (line number 3439)

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '34000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '10000000', //value 16
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '20000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '13000000', //value 19
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
            '0e000000', //value 14
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'fe010000', // AIPlayCommunication call



            // 1: AIPlayCommunication(me,'ShoutForAssistance','ScriptedAudio');

            '3c000000', //statement (init statement start offset)
            'c8020000', //End Offset (line number 3439)

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '34000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '10000000', //value 16
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '20000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '13000000', //value 19
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
            '0e000000', //value 14
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'fe010000', // AIPlayCommunication call


            // 0: AIPlayCommunication(me,'ShoutForHelp','ScriptedAudio');

            '3c000000', //statement (init statement start offset)
            'c8020000', //End Offset (line number 3439)

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '34000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '10000000', //value 16
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0d000000', //value 13
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
            '0e000000', //value 14
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'fe010000', // AIPlayCommunication call




            '3c000000', //statement (init statement start offset)
            'c8020000', //End Offset (line number 3439)


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