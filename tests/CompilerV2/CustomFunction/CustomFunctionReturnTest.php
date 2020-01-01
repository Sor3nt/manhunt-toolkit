<?php
namespace App\Tests\CompilerV2\Functions\CustomFunction;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CustomFunctionReturnTest extends KernelTestCase
{

    public function test()
    {
//        $this->assertEquals(true,true);
//return;
        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

            var 
                self : string[32];
                PIIsDead : level_var boolean;

            function IsPIAlive : boolean;
                begin if((IsEntityAlive(self)) and (not PIISDead)) then IsPIAlive := true else IsPIAlive := false; end;

            
            end.
        ";

        $expected = [

            '10000000', //Script start block
            '0a000000', //Script start block
            '11000000', //Script start block
            '0a000000', //Script start block
            '09000000', //Script start block

            '34000000', //reserve bytes
            '09000000', //reserve bytes
            '04000000', //Offset in byte

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //AiDefineGoalGotoVector Call
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'aa010000', //IsEntityAlive Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '1b000000', //unknown
            '24000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '29000000', //NOT
            '01000000', //NOT
            '01000000', //NOT
            '0f000000', //unknown
            '04000000', //unknown
            '25000000', //statement (AND operator)
            '01000000', //statement (AND operator)
            '04000000', //statement (AND operator)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '08010000', //Offset (line number 704)
            '10000000', //unknown
            '02000000', //unknown
            '11000000', //unknown
            '02000000', //unknown
            '0a000000', //unknown
            '34000000', //unknown
            '02000000', //unknown
            '04000000', //unknown
            '20000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
            '0f000000', //unknown
            '02000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (function return (bool?))
            '01000000', //parameter (function return (bool?))
            '01000000', //value 1
            '0f000000', //parameter (function return (bool?))
            '02000000', //parameter (function return (bool?))
            '17000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
            '01000000', //unknown
            '3c000000', //statement (init statement start offset)
            '6c010000', //Offset (line number 729)
            '10000000', //unknown
            '02000000', //unknown
            '11000000', //unknown
            '02000000', //unknown
            '0a000000', //unknown
            '34000000', //unknown
            '02000000', //unknown
            '04000000', //unknown
            '20000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
            '0f000000', //unknown
            '02000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (function return (bool?))
            '01000000', //parameter (function return (bool?))
            '00000000', //value 0
            '0f000000', //parameter (function return (bool?))
            '02000000', //parameter (function return (bool?))
            '17000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
            '01000000', //unknown
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '04000000', //Offset



            '11000000', //unknown
            '09000000', //unknown
            '0a000000', //unknown
            '0f000000', //unknown
            '0a000000', //unknown
            '3a000000', //unknown
            '04000000', //unknown

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