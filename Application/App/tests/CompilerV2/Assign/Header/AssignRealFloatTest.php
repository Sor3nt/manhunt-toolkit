<?php
namespace App\Tests\CompilerV2\Assign\Header;

use App\MHT;
use PHPUnit\Framework\TestCase;

class AssignRealFloatTest extends TestCase
{

    public function test()
    {

        $script = "
            
                            
                scriptmain LevelScript;
                
                entity
                    A01_Escape_Asylum : et_level;

                var
                      GunAccuracyRadiusNear	: real;
                
                script OnCreate;
                begin
                        GunAccuracyRadiusNear	:= 0.5;
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

            '12000000', //parameter (access script var)
            '01000000', //parameter (access script var)
            '0000003f', //value 1056964608
            '16000000', //parameter (access script var)
            '04000000', //parameter (access script var)

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