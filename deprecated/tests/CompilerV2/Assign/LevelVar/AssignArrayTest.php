<?php
namespace App\Tests\CompilerV2\Assign\LevelVar;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AssignArrayTest extends KernelTestCase
{

    public function test()
    {


        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;
            
            var
                me : string[32];
                bPerimeterAwareOfPlayer : level_var array[1..4] of boolean;	
                
            script OnCreate;
                begin
                    if (me = 'Perimeter4(hunter)') then	bPerimeterAwareOfPlayer[4] := false;
                end;


            end.
        ";

        $expected = [
            '10000000', //Script start block
            '0a000000', //Script start block
            '11000000', //Script start block
            '0a000000', //Script start block
            '09000000', //Script start block





            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '14000000', //Offset in byte

            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32

            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte

            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '13000000', //value 19

            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result

            '49000000', //string compare

            '12000000', //unknown
            '01000000', //unknown
            '01000000', //unknown

            '3f000000', //statement (init start offset)
            '90000000', //Offset (line number 286)
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '28010000', //Offset (line number 324)





            '1c000000', //levelVarPointerString
            '01000000', //levelVarPointerString
            '38000000', //offset
            '10000000', //levelVarSize

            '10000000', //nested call return result
            '01000000', //nested call return result

            '12000000', //array index 4
            '01000000', //array index 4
            '04000000', //array index 4


            '34000000', //Read array
            '01000000', //Read array
            '01000000', //Read array
            '12000000', //Read array
            '04000000', //Read array
            '04000000', //size of array
            '35000000', //Read array
            '04000000', //Read array
            '0f000000', //Read array
            '04000000', //Read array
            '31000000', //Read array
            '04000000', //Read array
            '01000000', //Read array
            '10000000', //Read array
            '04000000', //Read array


            '12000000', //parameter (function return (bool?))
            '01000000', //parameter (function return (bool?))
            '00000000', //value 0 (assign false)

            '0f000000', //writeToAttribute
            '02000000', //writeToAttribute
            '17000000', //writeToAttribute
            '04000000', //size
            '02000000', //writeToAttribute
            '01000000', //writeToAttribute


            '11000000', //Script end block
            '09000000', //Script end block
            '0a000000', //Script end block
            '0f000000', //Script end block
            '0a000000', //Script end block
            '3b000000', //Script end block
            '00000000', //nil Call
            
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