<?php
namespace App\Tests\Procedure;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProcedureOneParamVarTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;
                
            procedure SetupDoor(EntityName : string; OutAngle, InAngle : real); FORWARD;

            script Patrol;
            begin
             	SetupDoor('Door01_(D)', 120.0, 120.0);
            end;

            PROCEDURE SetupDoor;
            var
                Door : EntityPtr;
            begin
                Door := GetEntity(EntityName);
                SetDoorOpenAngleOut(Door, OutAngle);
                SetDoorOpenAngleIn(Door, InAngle);
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
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'ecffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'15000000', //unknown
'04000000', //unknown
'04000000', //unknown
'01000000', //unknown
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'04000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f0ffffff', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'd4010000', //SetDoorOpenAngleOut Call
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'04000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'd3010000', //SetDoorOpenAngleIn Call
'11000000', //unknown
'09000000', //unknown
'0a000000', //unknown
'0f000000', //unknown
'0a000000', //unknown
'3a000000', //unknown
'10000000', //unknown


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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000f042', //value 1123024896
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000f042', //value 1123024896
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //unknown
'04000000', //unknown
'11000000', //unknown
'02000000', //unknown
'00000000', //unknown
'32000000', //unknown
'02000000', //unknown
'1c000000', //unknown
'10000000', //unknown
'02000000', //unknown
'39000000', //unknown
'00000000', //unknown

'11000000', //Script end block
'09000000', //Script end block
'0a000000', //Script end block
'0f000000', //Script end block
'0a000000', //Script end block
'3b000000', //Script end block
'00000000', //Script end block
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