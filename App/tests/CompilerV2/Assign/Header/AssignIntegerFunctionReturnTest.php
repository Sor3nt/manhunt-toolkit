<?php
namespace App\Tests\CompilerV2\Assign\Header;

use App\MHT;
use PHPUnit\Framework\TestCase;

class AssignIntegerFunctionReturnTest extends TestCase
{

    public function test()
    {


        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

            var
                animLength : integer;

            script OnCreate;
                begin
            		animLength := GetAnimationLength('ASY_NURSE_ATTACK4A');
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

            '21000000', // Prepare string read (DATA table)
            '04000000', // Prepare string read (DATA table)
            '01000000', // Prepare string read (DATA table)
            '00000000', // offset

            '12000000', // parameter (Read String var)
            '02000000', // parameter (Read String var)
            '13000000', // ASY_NURSE_ATTACK4A + 1
            '10000000', // parameter (Read String var)
            '01000000', // parameter (Read String var)

            '10000000', // string pointer move
            '02000000', // string pointer move

            '49030000', // getanimationlength Call

            '16000000',
            '04000000',
            '14000000',
            '01000000',

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