<?php
namespace App\Tests\CompilerByType\Header\LevelVarInteger\SwitchCase;


use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SwitchTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;
                
            var
            	LevelState : level_var integer;
                me : string[16];
                
            script OnCreate;
                begin

                    case LevelState of
                        0: begin
                            AIPlayCommunication(me,'ShoutForAssistance','ScriptedAudio');
                            displaygametext;
                        end;
                        1: begin
                            AIPlayCommunication(me,'ShoutForAssistance','ScriptedAudio');
                        end;
                        2: begin
                            AIPlayCommunication(me,'ShoutForAssistance','ScriptedAudio');
                        end;
                        3: begin
                            AIPlayCommunication(me,'ShoutForAssistance','ScriptedAudio');
                        end;
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

            
            '1b000000', //read from level var
            '24000000', //Offset
            '04000000', //read from level var
            '01000000', //read from level var

            '24000000', //define case
            '01000000', //define case
            '03000000', //case number 3
            '3f000000', //statement (init start offset)
            '7c000000', //Offset (line number 3295)
            
            '24000000', //define case
            '01000000', //define case
            '02000000', //case number 2
            '3f000000', //statement (init start offset)
            '10010000', //Offset (line number 3331)
            
            '24000000', //define case
            '01000000', //define case
            '01000000', //case number 1
            '3f000000', //statement (init start offset)
            'a0010000', //Offset (line number 3367)

            '24000000', //define case
            '01000000', //define case
            '00000000', //case number 0
            '3f000000', //statement (init start offset)
            '30020000', //Offset (line number 3403)
            
            '3c000000', //statement (init statement start offset)
            'c0020000', //End Offset (line number 3439)


            // 3: AIPlayCommunication(me,'ShoutForAssistance','ScriptedAudio');
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '28000000', //Offset in byte
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
            '13000000', //value 19
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '14000000', //Offset in byte
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
            'c0020000', //End Offset (line number 3439)

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '28000000', //Offset in byte
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
            '13000000', //value 19
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '14000000', //Offset in byte
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
            'c0020000', //End Offset (line number 3439)

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '28000000', //Offset in byte
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
            '13000000', //value 19
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '14000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0e000000', //value 14
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'fe010000', // AIPlayCommunication call


            // 0: AIPlayCommunication(me,'ShoutForAssistance','ScriptedAudio');

            '3c000000', //statement (init statement start offset)
            'c0020000', //End Offset (line number 3439)

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '28000000', //Offset in byte
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
            '13000000', //value 19
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '14000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0e000000', //value 14
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'fe010000', // AIPlayCommunication call
            '04010000', // displaygametext call


            '3c000000', //statement (init statement start offset)
            'c0020000', //End Offset (line number 3439)
                 
            
            // script end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3b000000',
            '00000000'
        ];



        $compiler = new Compiler();
        $levelScriptCompiled = $compiler->parse(file_get_contents(__DIR__ . '/../0#levelscript.srce'));

        $compiler = new Compiler();
        $compiled = $compiler->parse($script, $levelScriptCompiled);

        if ($compiled['CODE'] != $expected){
            foreach ($compiled['CODE'] as $index => $item) {
                if ($expected[$index] == $item){
                    echo ($index + 1) . '->' . $item . "\n";
                }else{
                    echo "MISSMATCH need " . $expected[$index] . " got " . $compiled['CODE'][$index] . "\n";
                }
            }
            exit;
        }

        $this->assertEquals($compiled['CODE'], $expected, 'The bytecode is not correct');
    }

}