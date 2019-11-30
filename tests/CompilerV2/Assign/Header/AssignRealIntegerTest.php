<?php
namespace App\Tests\CompilerV2\Assign\Header;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AssignRealIntegerTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            
                            
                scriptmain LevelScript;
                
                entity
                    A01_Escape_Asylum : et_level;

                var
                      GunAccuracyRangeNear	: real;
                
                script OnCreate;
                begin
                        GunAccuracyRangeNear	:= 5;
                end;

                end.
        ";

        $expected = [


            // procedure start
            '10000000',
            '0a000000',
            '11000000',
            '0a000000',
            '09000000',

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '05000000', //value 5

            '10000000', //nested call return result
            '01000000', //nested call return result

            '4d000000', //unknown
            '16000000', //unknown
            '04000000', //unknown

            '00000000', //unknown
            '01000000', //unknown

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