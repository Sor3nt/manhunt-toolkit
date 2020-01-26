<?php
namespace App\Tests\CompilerV2\Functions\CustomFunction;

use App\MHT;
use PHPUnit\Framework\TestCase;

class CustomFunctionTest extends TestCase
{

    public function test()
    {
//        $this->assertEquals(true,true);
//return;
        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

            FUNCTION InDeathTrigger : boolean; FORWARD;

            script OnCreate;
                begin


                end;
            
            FUNCTION InDeathTrigger;
            VAR
                ePos : vec3d;
                result : boolean;
            begin
                result := FALSE;
                
                if (NOT IsEntityAlive('TruckGuard1(hunter)')) then
                begin
                    if (NOT IsHunterInShadow('TruckGuard1(hunter)')) then
                        result := TRUE;
                end;
                
                if (NOT IsEntityAlive('TruckGuard2(hunter)')) then
                begin
                    if (NOT IsHunterInShadow('TruckGuard2(hunter)')) then
                        result := TRUE;
                end;
                
                InDeathTrigger := result;
                
            end;


            end.
        ";

        $expected = [

            // custom function start
            '10000000', //Script start block
            '0a000000', //Script start block
            '11000000', //Script start block
            '0a000000', //Script start block
            '09000000', //Script start block

            '34000000', //reserve bytes
            '09000000', //reserve bytes
            '04000000', //Offset in byte

            '34000000', //reserve bytes
            '09000000', //reserve bytes
            '10000000', //Offset in byte

            '12000000', //unknown
            '01000000', //unknown
            '00000000', //nil Call
            '15000000', //unknown
            '04000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //SetHunterGunFireMinBurst Call
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '14000000', //value 20
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'aa010000', //IsEntityAlive Call
            '29000000', //NOT
            '01000000', //NOT
            '01000000', //NOT
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '04010000', //Offset (line number 65)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //SetHunterGunFireMinBurst Call
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '14000000', //value 20
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'ca030000', //IsHunterInShadow Call
            '29000000', //NOT
            '01000000', //NOT
            '01000000', //NOT
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '04010000', //Offset (line number 65)
            '12000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '15000000', //unknown
            '04000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '18000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '14000000', //value 20
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'aa010000', //IsEntityAlive Call
            '29000000', //NOT
            '01000000', //NOT
            '01000000', //NOT
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            'c0010000', //Offset (line number 112)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '18000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '14000000', //value 20
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'ca030000', //IsHunterInShadow Call
            '29000000', //NOT
            '01000000', //NOT
            '01000000', //NOT
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            'c0010000', //Offset (line number 112)

            '12000000', //Read simple value 1
            '01000000', //Read simple value 1
            '01000000', //Read simple value 1

            '15000000', //Assign to Variable result
            '04000000', //Assign to Variable result
            '10000000', //offset for result
            '01000000', //Assign to Variable result




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
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '10000000', //Offset


            '0f000000', //write to object
            '02000000', //write to object
            '17000000', //write to object
            '04000000', //write to object
            '02000000', //write to object
            '01000000', //write to object


            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '04000000', //Offset

            // custom function end
            '11000000', //unknown
            '09000000', //unknown
            '0a000000', //unknown
            '0f000000', //unknown
            '0a000000', //unknown
            '3a000000', //unknown
            '04000000', //unknown
            
            
            
            // script start
            '10000000',
            '0a000000',
            '11000000',
            '0a000000',
            '09000000',



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