<?php
namespace App\Tests\CompilerV2\WriteDebug;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WritedebugStringIntegerTest extends KernelTestCase
{

    public function test()
    {


        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

            var
                lCurrentAmbientAudioTrack : integer;
                
            script OnCreate;

                begin
        			WriteDebug('Setting ambient track to: ', lCurrentAmbientAudioTrack);
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

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte

            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '1b000000', //value 27

            '10000000', //nested call return result
            '01000000', //nested call return result

            '10000000', //nested string return result
            '02000000', //nested string return result

            '73000000', //writedebugstringarray Call


            '14000000', //Read VAR from header
            '01000000', //Read VAR from header
            '04000000', //Read VAR from header
            '1c000000', //Offset

            '6e000000', //WriteDebugInteger Call
            '74000000', //WriteDebugFlush Call

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