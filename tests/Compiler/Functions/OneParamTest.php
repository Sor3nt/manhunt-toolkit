<?php
namespace App\Tests\Functions;

use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OneParamTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
    
                entity
                    A01_Escape_Asylum : et_level;
                
                CONST
                    cActiveDamageRadiusAroundHunter = 2.0;
                    cActiveHearingRadiusAroundHunter = 5.0;
                
                
                var
                	me : string[16];
                
                   
                function SafeCalcDistance(TargetName : string; Distance : real) : boolean; forward;

                script OnLowHearing;
                begin
                    writeDebug(me,': OnLowHearing');
                    
                    if SafeCalcDistance('player(player)', cActiveDamageRadiusAroundHunter) then
                        AILookAtEntity(GetEntityName(this),'player(player)');
                    if SafeCalcDistance('Tom01(hunter)', cActiveDamageRadiusAroundHunter) then		
                        AILookAtEntity(GetEntityName(this),'Tom01(hunter)');
                    if SafeCalcDistance('Tom02(hunter)', cActiveDamageRadiusAroundHunter) then		
                        AILookAtEntity(GetEntityName(this),'Tom02(hunter)');	
                end;
                
                
                function SafeCalcDistance;
                var
                    result : boolean;
                    pTarget : EntityPtr;
                begin
                    pTarget := GetEntity(TargetName);
                    
                    if pTarget <> NIL then
                    begin
                        if CalcDistanceToEntity(pTarget, GetEntityPosition(this)) < Distance then
                            result := true
                        else result := false;
                    end;
                    
                    SafeCalcDistance := result;
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
            '08000000', //Offset in byte

            //=>pTarget := GetEntity(TargetName);
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            'f0ffffff', //Offset from TargetName

            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '00000000', //value 0 (read first param ?)

            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result

            '77000000', //GetEntity Call

            //assign
            '15000000', //unknown
            '04000000', //unknown
            '08000000', //unknown
            '01000000', //unknown


            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '08000000', //Offset
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (temp int)
            '01000000', //parameter (temp int)
            '00000000', //value 0
            '0f000000', //parameter (temp int)
            '04000000', //parameter (temp int)
            '23000000', //statement (core)
            '04000000', //statement (core)
            '01000000', //statement (core)
            '12000000', //statement (core)
            '01000000', //statement (core)
            '01000000', //statement (core)
            '40000000', //statement (core)(operator un-equal)
            'c4000000', //statement (core)( Offset )
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)

            //=> if CalcDistanceToEntity(pTarget, GetEntityPosition(this)) < Distance then
            //pTarget
            'a4010000', //Offset (line number 234)
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '08000000', //Offset
            '10000000', //nested call return result
            '01000000', //nested call return result

            //this
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '49000000', //value 73
            '10000000', //nested call return result
            '01000000', //nested call return result
            //GetEntityPosition
            '78000000', //GetEntityPosition Call
            //CalcDistanceToEntity
            '1a030000', //CalcDistanceToEntity Call
            '10000000', //nested call return result
            '01000000', //nested call return result



            //Distance
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            'f4ffffff', //Offset

            '10000000', //nested call return result
            '01000000', //nested call return result
            '4e000000', //unknown
            '12000000', //unknown
            '01000000', //unknown
            '01000000', //unknown


            '3d000000', //unknown
            '50010000', //unknown
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '88010000', //Offset (line number 227)
            '12000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '15000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '3c000000', //statement (init statement start offset)
            'a4010000', //Offset (line number 234)
            '12000000', //unknown
            '01000000', //unknown
            '00000000', //nil Call
            '15000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
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
            '04000000', //Offset
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
            '0c000000', //unknown

            '10000000', //Script start block
            '0a000000', //Script start block
            '11000000', //Script start block
            '0a000000', //Script start block
            '09000000', //Script start block
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '48000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '10000000', //value 16
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //WriteDebugString Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '08000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0f000000', //value 15
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //WriteDebugString Call
            '74000000', //WriteDebugFlush Call


            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '18000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '00000040', //float 2
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //unknown
            '04000000', //unknown
            '11000000', //unknown
            '02000000', //unknown
            '00000000', //nil Call
            '32000000', //unknown
            '02000000', //unknown
            '1c000000', //unknown
            '10000000', //unknown
            '02000000', //unknown
            '39000000', //unknown
            '00000000', //SetLevelCompleted Call
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '68030000', //Offset (line number 2615)
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '49000000', //value 73
            '10000000', //nested call return result
            '01000000', //nested call return result
            '86000000', //GetEntityName Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '18000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0f000000', //value 15
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'fd010000', //AILookAtEntity Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '28000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '00000040', //value 1073741824
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //unknown
            '04000000', //unknown
            '11000000', //unknown
            '02000000', //unknown
            '00000000', //nil Call
            '32000000', //unknown
            '02000000', //unknown
            '1c000000', //unknown
            '10000000', //unknown
            '02000000', //unknown
            '39000000', //unknown
            '00000000', //SetLevelCompleted Call
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '20040000', //Offset (line number 2661)
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '49000000', //value 73
            '10000000', //nested call return result
            '01000000', //nested call return result
            '86000000', //GetEntityName Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '28000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0e000000', //value 14
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'fd010000', //AILookAtEntity Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '38000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '00000040', //value 1073741824
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //unknown
            '04000000', //unknown
            '11000000', //unknown
            '02000000', //unknown
            '00000000', //nil Call
            '32000000', //unknown
            '02000000', //unknown
            '1c000000', //unknown
            '10000000', //unknown
            '02000000', //unknown
            '39000000', //unknown
            '00000000', //SetLevelCompleted Call
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            'd8040000', //Offset (line number 2707)
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '49000000', //value 73
            '10000000', //nested call return result
            '01000000', //nested call return result
            '86000000', //GetEntityName Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '38000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0e000000', //value 14
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'fd010000', //AILookAtEntity Call
            '11000000', //Script end block
            '09000000', //Script end block
            '0a000000', //Script end block
            '0f000000', //Script end block
            '0a000000', //Script end block
            '3b000000', //Script end block
            '00000000', //nil Call

        ];

        $compiler = new Compiler();
        $compiled = $compiler->parse($script);

        if ($compiled['CODE'] != $expected){
            $index = 0;
            foreach ($compiled['CODE'] as $index => $item) {
                if ($expected[$index] == $item){
                    echo ($index + 1) . '->' . $item . " " . $item->debug . "\n";
                }else{
                    echo "MISSMATCH need " . $expected[$index] . " got " . $compiled['CODE'][$index] . " " . $compiled['CODE'][$index]->debug . "\n";
                }
            }

            exit;
        }

        $this->assertEquals($compiled['CODE'], $expected, 'The bytecode is not correct');

    }

}