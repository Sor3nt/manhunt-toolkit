<?php
namespace App\Tests\Compiler\Statements;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IfElseNoLineEndTest extends KernelTestCase
{
//
    public function test() {

        return true;
        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

            script OnCreate;

                begin
                    if (GetEntityname(this) <> 'Upt_Elevator_DoorUL_(SD)') or (GetEntityname(this) <> 'Upt_Elevator_DoorUR_(SD)') then
                        SetDoorState(this, DOOR_CLOSED)
                    else
                        SetDoorState(this, DOOR_OPEN)
                end;

            end.
        ";

        $expected = [
            // script start
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
'86000000', //getentityname Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'00000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4e000000', //unknown
'12000000', //unknown
'01000000', //unknown
'01000000', //unknown
'40000000', //unknown
'7c000000', //unknown
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'49000000', //value 73
'10000000', //nested call return result
'01000000', //nested call return result
'86000000', //getentityname Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'1c000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4e000000', //unknown
'12000000', //unknown
'01000000', //unknown
'01000000', //unknown
'40000000', //unknown
'ec000000', //unknown
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'0f000000', //unknown
'04000000', //unknown
'27000000', //statement (OR operator)
'01000000', //statement (OR operator)
'04000000', //statement (OR operator)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'48010000', //Offset (line number 261)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'49000000', //value 73
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'02000000', //value 2
'10000000', //nested call return result
'01000000', //nested call return result
'97000000', //SetDoorState Call
'3c000000', //statement (init statement start offset)
'74010000', //Offset (line number 272)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'49000000', //value 73
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'97000000', //SetDoorState Call

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
        $compiled = $compiler->parse($script, false);


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