<?php
namespace App\Tests\CompilerV2\Assign\Script;

use App\MHT;
use PHPUnit\Framework\TestCase;

class AssignEmptyStringTest extends TestCase
{

    public function test()
    {


        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

            script OnCreate;
                VAR
                    savepointName : String[32];            

                begin
                	savepointName := '';
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
            '24000000', //Offset in byte


            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte

            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '01000000', //value size / str len

            '10000000', //nested call return result
            '01000000', //nested call return result

            '10000000', //nested string return result
            '02000000', //nested string return result

            '22000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '20000000', //unknown offset

            '12000000', //parameter (read string array? assign?)
            '03000000', //parameter (read string array? assign?)
            '20000000', //value 32

            '10000000', //parameter (read string array? assign?)
            '04000000', //parameter (read string array? assign?)

            '10000000', //unknown
            '03000000', //unknown
            '48000000', //unknown

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