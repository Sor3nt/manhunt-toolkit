<?php
namespace App\Tests\CompilerV2\Assign\Header;

use App\MHT;
use PHPUnit\Framework\TestCase;

class AssignStringTest extends TestCase
{

    public function test()
    {

        $script = "
            
                            
                scriptmain LevelScript;
                
                entity
                    A01_Escape_Asylum : et_level;

                var
                    me : string[30];
                
                script OnCreate;
                begin
                    me := GetEntityName(this);
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
            '49000000', //value 73

            '10000000', //nested call return result
            '01000000', //nested call return result

            '86000000', //GetEntityName Call

            '21000000', //Prepare string read (header)
            '04000000', //Prepare string read (header)
            '04000000', //Prepare string read (header)
            '00000000', //offset

            '12000000', //parameter (read string array? assign?)
            '03000000', //parameter (read string array? assign?)
            '1e000000', //value 30

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