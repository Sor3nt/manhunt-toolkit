<?php
namespace App\Tests\Compiler\Statements;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IfElseIfTest extends KernelTestCase
{
//
    public function test() {
//        $this->assertEquals(true, true, 'The bytecode is not correct');
//        return;

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

            VAR
                randFlash : integer;

            script OnCreate;

                begin
            
            
                    if randFlash = 0 then		
                        PlayScriptAudioStreamFromEntityAuto('LGHTN1', 100, GetPlayer, 40);
                    else if randFlash = 1 then
                        PlayScriptAudioStreamFromEntityAuto('LGHTN2', 100, GetPlayer, 40);
                    else if randFlash = 2 then
                        PlayScriptAudioStreamFromEntityAuto('LGHTN3', 100, GetPlayer, 40);

                    displaygametext;

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

'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'18000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (temp int)
'01000000', //parameter (temp int)
'00000000', //value 0
'0f000000', //parameter (temp int)
'04000000', //parameter (temp int)
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'3f000000', //statement (init start offset)
'6c000000', //Offset (line number 288)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'ec000000', //Offset (line number 320)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'00000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'64000000', //value 100
'10000000', //nested call return result
'01000000', //nested call return result
'8a000000', //GetPlayer Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'28000000', //value 40
'10000000', //nested call return result
'01000000', //nested call return result
'6b030000', //PlayScriptAudioStreamFromEntityAuto Call
'3c000000', //statement (init statement start offset)
'94020000', //Offset (line number 426)
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'18000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
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
'44010000', //Offset (line number 342)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'c4010000', //Offset (line number 374)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'64000000', //value 100
'10000000', //nested call return result
'01000000', //nested call return result
'8a000000', //GetPlayer Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'28000000', //value 40
'10000000', //nested call return result
'01000000', //nested call return result
'6b030000', //PlayScriptAudioStreamFromEntityAuto Call
'3c000000', //statement (init statement start offset)
'94020000', //Offset (line number 426)
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'18000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (temp int)
'01000000', //parameter (temp int)
'02000000', //value 2
'0f000000', //parameter (temp int)
'04000000', //parameter (temp int)
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'3f000000', //statement (init start offset)
'1c020000', //Offset (line number 396)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'94020000', //Offset (line number 426)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'64000000', //value 100
'10000000', //nested call return result
'01000000', //nested call return result
'8a000000', //GetPlayer Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'28000000', //value 40
'10000000', //nested call return result
'01000000', //nested call return result
'6b030000', //PlayScriptAudioStreamFromEntityAuto Call
'04010000', //display Call

            // script end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3b000000',
            '00000000'
        ];


//        $compiler = new Compiler();
//        $levelScriptCompiled = $compiler->parse(file_get_contents(__DIR__ . '/0#levelscript.srce'));

        $compiler = new Compiler();
        $compiled = $compiler->parse($script, false);

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