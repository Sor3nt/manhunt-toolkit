<?php
namespace App\Tests\CompilerV2\IfStatement;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IfFunctionEqualStringTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

            VAR
            	gsAcPlatformModifier : real;

            script OnCreate;

                begin
		            if(GetPlatform = 'PS2') then gsAcPlatformModifier := 0.0;
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

            'd4030000', //GetPlatform call

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //offset

            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '04000000', //value 4

            '10000000', //nested call return result
            '01000000', //nested call return result

            '10000000', //nested string return result
            '02000000', //nested string return result

            '49000000', //compare string

            '12000000', //unknown
            '01000000', //unknown
            '01000000', //unknown

            '3f000000', //statement (init start offset)
            '68000000', //Offset (line number 76)

            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)

            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)

            '3f000000', //statement (init start offset)
            '98000000', //Offset (line number 88)

            '12000000', //parameter (access script var)
            '01000000', //parameter (access script var)
            '00000000', //value 0

            '16000000', //write to gsAcPlatformModifier
            '04000000', //write to gsAcPlatformModifier
            '08000000', //offset gsAcPlatformModifier
            '01000000', //write to gsAcPlatformModifier


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