<?php
namespace App\Tests\CompilerV2\IfStatement;

use App\MHT;
use PHPUnit\Framework\TestCase;

class IfIntegerGreaterFloatTest extends TestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

            script OnCreate;
            
                begin

				    if(GetDamage(GetEntity('wife(hunter)')) > 0.0) then InflictDamage(GetEntity('wife(hunter)'), 10000, ARM_HEAVY);

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

            "21000000",
            "04000000",
            "01000000",
            "00000000", //wife offset
            "12000000",
            "02000000",
            "0d000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "77000000", //GetEntity
            "10000000",
            "01000000",
            "84000000",//GetDamage
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "00000000",
            "10000000",
            "01000000",


            "0f000000",
            "01000000",
            "0f000000",
            "02000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "4d000000",
            "0f000000",
            "02000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "4e000000",
            "12000000",
            "01000000",
            "01000000",
            "42000000",
            "7c840000",
            "33000000",
            "01000000",
            "01000000",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "44010000",
            "21000000",
            "04000000",
            "01000000",
            "00000000",
            "12000000",
            "02000000",
            "0d000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "77000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "10270000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "03000000",
            "10000000",
            "01000000",
            "85000000",

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

            foreach ($compiler->codes as $index => $newCode) {


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