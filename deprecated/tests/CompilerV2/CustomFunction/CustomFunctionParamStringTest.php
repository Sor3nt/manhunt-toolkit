<?php
namespace App\Tests\CompilerV2\Functions\CustomFunction;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CustomFunctionParamStringTest extends KernelTestCase
{

    public function test()
    {
//        $this->assertEquals(true,true);
//return;
        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

            VAR
                barrelEnteredOne : level_var boolean;
            	x1, y1, z1, x2, y2, z2 : real;
                
            function FuncInsideTrigger(EntityName : string) : boolean; forward;
           
           
            script WaitForEnterOne;
                begin
                    
                    while NOT FuncInsideTrigger('hChaser09s(hunter)') do sleep(10);
                    barrelEnteredOne := TRUE;
                    writedebug('BARREL ENTER ONE');
                end;

            function FuncInsideTrigger;
            
                var
                    vEntityPos : vec3d;
                    result : boolean;
                
                begin
                     vEntityPos := GetEntityPosition(GetEntity(EntityName));
                     
                     writeDebug('FuncInsideTrigger: Checking object ', EntityName, ' with position: ', vEntityPos.x, ', ', vEntityPos.y, ', ', vEntityPos.z);
                   
                   result := false;
                     if (vEntityPos.x > x1) and (vEntityPos.x < x2)
                     and (vEntityPos.y > y1) and (vEntityPos.y < y2)
                   and (vEntityPos.z > z1) and (vEntityPos.z < z2) then
                   begin
                            writeDebug('FuncInsideTrigger: ', EntityName, ' is inside the trigger');
                            result := true;
                     end
                   else
                   begin
                            writeDebug('FuncInsideTrigger: ', EntityName, ' is NOT inside the trigger');
                     end;      
                
                   FuncInsideTrigger := result;
                
                end;
            
            
            
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
            '34000000', //reserve bytes
            '09000000', //reserve bytes
            '10000000', //Offset in byte
            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '0c000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            'f4ffffff', //Offset
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '00000000', //value 0
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '77000000', //GetEntity Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '78000000', //GetEntityPosition Call
            '12000000', //assign (to script var)
            '03000000', //assign (to script var)
            '0c000000', //value
            '0f000000', //assign (to script var)
            '01000000', //assign (to script var)
            '0f000000', //unknown
            '04000000', //unknown
            '44000000', //unknown
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '28000000', //KillEntity Call
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '24000000', //value 36
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebugstringarray Call
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            'f4ffffff', //Offset
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '00000000', //value 0
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebugstringarray Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '50000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '11000000', //value 17
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebugstringarray Call
            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '0c000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '02000000', //unknown
            '18000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
            '6f000000', //WriteDebugReal Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '64000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '03000000', //value 3
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebugstringarray Call
            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '0c000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '01000000', //unknown
            '32000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '02000000', //unknown
            '18000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
            '6f000000', //WriteDebugReal Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '64000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '03000000', //value 3
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebugstringarray Call
            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '0c000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '01000000', //unknown
            '32000000', //unknown
            '01000000', //unknown
            '08000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '02000000', //unknown
            '18000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
            '6f000000', //WriteDebugReal Call
            '74000000', //WriteDebugFlush Call
            '12000000', //unknown
            '01000000', //unknown
            '00000000', //nil Call
            '15000000', //unknown
            '04000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '0c000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '02000000', //unknown
            '18000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '14000000', //Read VAR from header
            '01000000', //Read VAR from header
            '04000000', //Read VAR from header
            'b8000000', //Offset
            '10000000', //nested call return result
            '01000000', //nested call return result
            '4e000000', //unknown
            '12000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '42000000', //unknown
            'f8020000', //unknown
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '10000000', //nested call return result
            '01000000', //nested call return result
            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '0c000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '02000000', //unknown
            '18000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '14000000', //Read VAR from header
            '01000000', //Read VAR from header
            '04000000', //Read VAR from header
            'c4000000', //Offset
            '10000000', //nested call return result
            '01000000', //nested call return result
            '4e000000', //unknown
            '12000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '3d000000', //unknown
            '74030000', //unknown
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '0f000000', //unknown
            '04000000', //unknown
            '25000000', //statement (AND operator)
            '01000000', //statement (AND operator)
            '04000000', //statement (AND operator)
            '10000000', //nested call return result
            '01000000', //nested call return result
            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '0c000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '01000000', //unknown
            '32000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '02000000', //unknown
            '18000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '14000000', //Read VAR from header
            '01000000', //Read VAR from header
            '04000000', //Read VAR from header
            'bc000000', //Offset
            '10000000', //nested call return result
            '01000000', //nested call return result
            '4e000000', //unknown
            '12000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '42000000', //unknown
            '20040000', //unknown
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '0f000000', //unknown
            '04000000', //unknown
            '25000000', //statement (AND operator)
            '01000000', //statement (AND operator)
            '04000000', //statement (AND operator)
            '10000000', //nested call return result
            '01000000', //nested call return result
            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '0c000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '01000000', //unknown
            '32000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '02000000', //unknown
            '18000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '14000000', //Read VAR from header
            '01000000', //Read VAR from header
            '04000000', //Read VAR from header
            'c8000000', //Offset
            '10000000', //nested call return result
            '01000000', //nested call return result
            '4e000000', //unknown
            '12000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '3d000000', //unknown
            'cc040000', //unknown
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '0f000000', //unknown
            '04000000', //unknown
            '25000000', //statement (AND operator)
            '01000000', //statement (AND operator)
            '04000000', //statement (AND operator)
            '10000000', //nested call return result
            '01000000', //nested call return result
            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '0c000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '01000000', //unknown
            '32000000', //unknown
            '01000000', //unknown
            '08000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '02000000', //unknown
            '18000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '14000000', //Read VAR from header
            '01000000', //Read VAR from header
            '04000000', //Read VAR from header
            'c0000000', //Offset
            '10000000', //nested call return result
            '01000000', //nested call return result
            '4e000000', //unknown
            '12000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '42000000', //unknown
            '78050000', //unknown
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '0f000000', //unknown
            '04000000', //unknown
            '25000000', //statement (AND operator)
            '01000000', //statement (AND operator)
            '04000000', //statement (AND operator)
            '10000000', //nested call return result
            '01000000', //nested call return result
            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '0c000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '01000000', //unknown
            '32000000', //unknown
            '01000000', //unknown
            '08000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '02000000', //unknown
            '18000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '14000000', //Read VAR from header
            '01000000', //Read VAR from header
            '04000000', //Read VAR from header
            'cc000000', //Offset
            '10000000', //nested call return result
            '01000000', //nested call return result
            '4e000000', //unknown
            '12000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '3d000000', //unknown
            '24060000', //unknown
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '0f000000', //unknown
            '04000000', //unknown
            '25000000', //statement (AND operator)
            '01000000', //statement (AND operator)
            '04000000', //statement (AND operator)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '04070000', //Offset (line number 449)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '68000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '14000000', //value 20
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebugstringarray Call
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            'f4ffffff', //Offset
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '00000000', //value 0
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebugstringarray Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '80000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '17000000', //value 23
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebugstringarray Call
            '74000000', //WriteDebugFlush Call
            '12000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '15000000', //unknown
            '04000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '3c000000', //statement (init statement start offset)
            '98070000', //Offset (line number 486)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '68000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '14000000', //value 20
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebugstringarray Call
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            'f4ffffff', //Offset
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '00000000', //value 0
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebugstringarray Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '98000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '1b000000', //value 27
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebugstringarray Call
            '74000000', //WriteDebugFlush Call
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
            '0f000000', //unknown
            '02000000', //unknown
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
            '08000000', //unknown


            '10000000', //Script start block
            '0a000000', //Script start block
            '11000000', //Script start block
            '0a000000', //Script start block
            '09000000', //Script start block

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte

            '10000000', //nested call return result
            '01000000', //nested call return result

            '10000000', //procedure/function call
            '04000000', //procedure/function call
            '11000000', //procedure/function call
            '02000000', //procedure/function call
            '00000000', //procedure/function call
            '32000000', //procedure/function call
            '02000000', //procedure/function call
            '1c000000', //procedure/function call
            '10000000', //procedure/function call
            '02000000', //procedure/function call
            '39000000', //procedure/function call
            '00000000', //procedure/function call OFFSET

            '29000000', //NOT
            '01000000', //NOT
            '01000000', //NOT

            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            'c8080000', //Offset (line number 704)

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '0a000000', //value 10
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //Sleep Call

            '3c000000', //statement (init statement start offset)
            '40080000', //Offset (line number 670)


            '12000000', //parameter (access level_var)
            '01000000', //parameter (access level_var)
            '01000000', //value 1
            '1a000000', //parameter (access level_var)
            '01000000', //parameter (access level_var)
            'b4000000', //unknown
            '04000000', //unknown
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '14000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '11000000', //value 17
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebugstringarray Call
            '74000000', //WriteDebugFlush Call

            '11000000', //Script end block
            '09000000', //Script end block
            '0a000000', //Script end block
            '0f000000', //Script end block
            '0a000000', //Script end block
            '3b000000', //Script end block
            '00000000', //Script end block

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