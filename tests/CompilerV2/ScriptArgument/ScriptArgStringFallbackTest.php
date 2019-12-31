<?php
namespace App\Tests\CompilerV2\ScriptArgument;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ScriptArgStringFallbackTest extends KernelTestCase
{

    public function test()
    {
//        $this->assertEquals(true,true);
//
//        return;

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

            
            script PlaySound;
                ARG
                    SoundName : string[8] : '';
                begin
                    EndScriptAudioStream;
                    PreLoadScriptAudioStream(SoundName, false);
                    while not IsScriptAudioStreamPreLoaded do sleep(1);
                    PlayScriptAudioStream(100);
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
            '0c000000', //Offset in byte

            '10030000', //init argument read

            '24000000', //read argument
            '01000000', //read argument
            '00000000', //offset ?

            '3f000000', //unknown
            'b0000000', //Offset (line number 7698)

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '00000000', //value 0

            '10000000', //nested call return result
            '01000000', //nested call return result


            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte

            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '01000000', //value 1
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '0c030000', //unknown
            '22000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '08000000', //unknown
            '12000000', //parameter (read string array? assign?)
            '03000000', //parameter (read string array? assign?)
            '08000000', //value 8
            '10000000', //parameter (read string array? assign?)
            '04000000', //parameter (read string array? assign?)
            '10000000', //unknown
            '03000000', //unknown
            '48000000', //unknown
            '0f030000', //unknown



            'ce020000', //EndScriptAudioStream Call

            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '08000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '08000000', //value 8
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '00000000', //value 0
            '10000000', //nested call return result
            '01000000', //nested call return result
            'c9020000', //PreLoadScriptAudioStream

            'ca020000', //IsScriptAudioStreamPreLoaded
            '29000000', //NOT
            '01000000', //NOT
            '01000000', //NOT
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '3c010000', //Offset (line number 7733)

            //sleep(1);
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '01000000', //value 1
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //Sleep Call

            '3c000000', //statement (init statement start offset)
            'f8000000', //Offset (line number 7716)

            //PlayScriptAudioStream(100);
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '64000000', //value 100
            '10000000', //nested call return result
            '01000000', //nested call return result
            'cb020000', //PlayScriptAudioStream call



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