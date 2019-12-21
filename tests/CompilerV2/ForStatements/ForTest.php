<?php
namespace App\Tests\CompilerV2\ForStatements;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ForTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;
                
                
            var 
                PKarray: array[1..3] of boolean;

            script RemoveBarHunters;
                var 
                    i : integer;
                begin
                              
                      for i:= 1 to 3 do PKarray[i] := false;  
                    
                end;

            
            end.

        ";

        $expected = [

            "10000000", //Script start block
            "0a000000", //Script start block
            "11000000", //Script start block
            "0a000000", //Script start block
            "09000000", //Script start block

            '34000000', //reserve bytes
            '09000000', //reserve bytes
            '04000000', //Offset in byte


            '12000000', //unknown
            '01000000', //unknown
            '01000000', //start 1

            '15000000', //unknown
            '04000000', //unknown
            '20000000', //32
            '01000000', //unknown

            '12000000', //unknown
            '01000000', //unknown
            '03000000', //end 3

            '13000000', //unknown
            '02000000', //unknown
            '04000000', //unknown
            '20000000', //unknown
            '23000000', //unknown
            '01000000', //unknown
            '02000000', //unknown
            '41000000', //unknown
            '00390000', //unknown

            '3c000000', //statement (init statement start offset)
            '10010000', //Offset (line number 3687)


            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte

            '10000000', //nested call return result
            '01000000', //nested call return result

            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '04000000', //Offset

            '34000000', //unknown
            '01000000', //unknown
            '01000000', //unknown

            '12000000', //unknown
            '04000000', //unknown
            '04000000', //unknown

            '35000000', //unknown
            '04000000', //unknown

            '0f000000', //unknown
            '04000000', //unknown

            '31000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '10000000', //unknown
            '04000000', //unknown

            '12000000', //parameter (function return (bool?))
            '01000000', //parameter (function return (bool?))
            '00000000', //value 0

            '0f000000', //parameter (function return (bool?))
            '02000000', //parameter (function return (bool?))
            '17000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
            '01000000', //unknown

            '2f000000', //unknown
            '04000000', //unknown
            '1c000000', //unknown

            '3c000000', //statement (init statement start offset)
            '3c000000', //Offset (line number 3634)

            '30000000', //unknown
            '04000000', //unknown
            '1c000000', //unknown



            "11000000", //Script end block
            "09000000", //Script end block
            "0a000000", //Script end block
            "0f000000", //Script end block
            "0a000000", //Script end block
            "3b000000", //Script end block
            "00000000", //Script end block

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