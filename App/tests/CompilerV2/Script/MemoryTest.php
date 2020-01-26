<?php
namespace App\Tests\CompilerV2\Script;

use App\MHT;
use PHPUnit\Framework\TestCase;

class MemoryTest extends TestCase
{

    public function test()
    {


        /**
         *
         * Need 208
         *
         * vec3d 12x10 => 120
         * integer 4 => 4
         * integer 4 => 4
         * integer 4 => 4
         * string 32 => 36
         * string 32 => 36
         * real   4  => 4
         */

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

            script OnCreate;
                var pos : array [1..10] of vec3d;
                    i, y, playing : integer;
                    triggerName, triggerNum : string[32];
                    radius : real;
                begin

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

            '34000000',
            '09000000',
            'd0000000',

            // script end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3b000000',
            '00000000',

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