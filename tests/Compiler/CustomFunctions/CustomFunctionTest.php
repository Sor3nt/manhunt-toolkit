<?php
namespace App\Tests\CustomFunctions;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CustomFunctionTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
                
                entity
                    A01_Escape_Asylum : et_level;
                    
                function DetectPlayer : boolean; forward;
                
                script OnCreate;

                    begin

                        if NOT DetectPlayer then
                        begin
                            writeDebug('TemporaryReturnToHuntPlayer: DetectPlayer = false - leg it');
                        end;


                    end;
    

                
                function DetectPlayer;
                var
                    result : boolean;
                    vMyPos : vec3d;
                    rDistanceToPlayer : real;
                begin
                    {discovers if player is close to hunter}
                    result := false;
                
                    vMyPos := GetEntityPosition(this);
                    rDistanceToPlayer := CalcDistanceToEntity(GetPlayer, vMyPos);
                    {writeDebug('DetectPlayer: rDistanceToPlayer: ', rDistanceToPlayer);}
                
                    if rDistanceToPlayer < 3.0 then
                    begin
                        writeDebug('DetectPlayer: Returned True');		
                        result := true;
                    end;
                
                    DetectPlayer := result;
                end;

                end.
        ";

        $expected = [

            '10000000', //function start block
            '0a000000', //function start block
            '11000000', //function start block
            '0a000000', //function start block
            '09000000', //function start block

            //reserve return size
            '34000000', //reserve bytes
            '09000000', //reserve bytes
            '04000000', //Offset in byte

            //reserve var size
            '34000000', //reserve bytes
            '09000000', //reserve bytes
            '14000000', //Offset in byte


            '12000000', //assign to scriptvar
            '01000000', //assign to scriptvar
            '00000000', //value 0
            '15000000', //assign to scriptvar
            '04000000', //assign to scriptvar
            '04000000', //assign to scriptvar
            '01000000', //offset?

            // vMyPos := GetEntityPosition(this);
            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '10000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result

            //this
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '49000000', //value 73
            '10000000', //nested call return result
            '01000000', //nested call return result
            //call
            '78000000', //GetEntityPosition Call


            '12000000', //assign (to script var)
            '03000000', //assign (to script var)
            '10000000', //offset
            '0f000000', //assign (to script var)
            '01000000', //assign (to script var)
            '0f000000', //unknown
            '04000000', //unknown
            '44000000', //unknown
            '8a000000', //GetPlayer Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '10000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '1a030000', //CalcDistanceToEntity Call
            '15000000', //unknown
            '04000000', //unknown
            '14000000', //unknown
            '01000000', //unknown
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '14000000', //Offset
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '00004040', //value 1077936128
            '10000000', //nested call return result
            '01000000', //nested call return result
            '4e000000', //unknown
            '12000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '3d000000', //unknown
            '20010000', //unknown
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '84010000', //Offset (line number 332)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '3c000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '1c000000', //value 28
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //WriteDebugString Call
            '74000000', //WriteDebugFlush Call

            //result := true;
            '12000000', //param int
            '01000000', //param int
            '01000000', // value true
            '15000000', //assign to bool
            '04000000', //assign to bool
            '04000000', //offset
            '01000000', //assign to bool

            //line 98
            //DetectPlayer := result;
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

            // function end
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


            '00000000', //DetectPlayer Call
            '29000000', //NOT
            '01000000', //NOT
            '01000000', //NOT
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '84020000', //Offset (line number 2281)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '3b000000', //value 59
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //WriteDebugString Call
            '74000000', //WriteDebugFlush Call
            
            
            // script end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3b000000',
            '00000000'
        ];
        $compiler = new Compiler();
        $compiled = $compiler->parse($script);

        if ($compiled['CODE'] != $expected){
            foreach ($compiled['CODE'] as $index => $item) {
                if ($expected[$index] == $item){
                    echo ($index + 1) . '->' . $item . "\n";
                }else{
                    echo "MISSMATCH need " . $expected[$index] . " got " . $compiled['CODE'][$index] . "\n";
                }
            }
            exit;
        }

        $this->assertEquals($compiled['CODE'], $expected, 'The bytecode is not correct');
    }

}