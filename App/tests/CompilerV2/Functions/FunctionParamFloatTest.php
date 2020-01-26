<?php
namespace App\Tests\CompilerV2\Functions;

use App\MHT;
use PHPUnit\Framework\TestCase;

class FunctionParamFloatTest extends TestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;


            script OnCreate;

                begin
                    DisplayGameText(1.2);
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

            '12000000', // init parameter
            '01000000', // init parameter
            '9a99993f', // value float 1.2

            '10000000', // return
            '01000000', // return

            '04010000', // DisplayGameText call

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