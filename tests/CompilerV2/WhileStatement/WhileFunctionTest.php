<?php
namespace App\Tests\CompilerV2\WhileStatement;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WhileFunctionTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

            script OnCreate;

                begin
                    while IsPlayerWalking do sleep(1500);
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

            'ed020000', //IsPlayerWalking call
            '24000000', //statement (core 2)
            '01000000', //statement (core 2)
            '00000000', //statement (core 2)
            '3f000000', //statement (line offset)
            '4c000000', //Offset in byte
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            'dc050000', //value 1500
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call

            '3c000000', //line offset
            '14000000', //unknown

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