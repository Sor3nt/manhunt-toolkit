<?php
namespace App\Tests\Command;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IfFunctionLowerIntTest extends KernelTestCase
{
//
    public function test() {



        $script = "
            scriptmain LevelScript;

            var
                stealthOneLooper : level_var boolean;

            script OnCreate;

                begin
                  	if (
                  	        GetDamage(GetPlayer) < 125
                  	    ) AND (
                            (
                                GetEntity('G_First_Aid_(CT)13') <> NIL
                            ) OR (
                                GetEntity('G_First_Aid_(CT)14') <> NIL
                            ) OR (
                                GetEntity('G_First_Aid_(CT)15') <> NIL
                            )
                        ) then
                        GraphModifyConnections(GetEntity('cell2_(SD)'), AISCRIPT_GRAPHLINK_ALLOW_EVERYTHING);
                end;

            end.
        ";
//
//        $script = "
//            scriptmain LevelScript;
//
//            var
//                stealthOneLooper : level_var boolean;
//
//            script OnCreate;
//
//                begin
//                  if (GetDoorState(GetEntity('cell2_(SD)')) = DOOR_CLOSED) then
//                    GraphModifyConnections(GetEntity('cell2_(SD)'), AISCRIPT_GRAPHLINK_ALLOW_EVERYTHING);
//                end;
//
//            end.
//        ";

        $expected = [
            // script start
            '10000000',
            '0a000000',
            '11000000',
            '0a000000',
            '09000000',


            '00000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0b000000', //value
            '10000000', //parameter (Read String var)
            '01000000', //parameter (Read String var)
            '10000000', //nested string return result
            '02000000', //nested string return result
            '77000000', //getentity Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '96000000', //getdoorstate Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (temp)
            '01000000', //parameter (temp)
            '02000000', //value
            '0f000000', //parameter (temp)
            '04000000', //parameter (temp)
            '23000000', //If statement
            '04000000', //If statement
            '01000000', //If statement
            '12000000', //If statement
            '01000000', //If statement
            '01000000', //If statement
            '3f000000', //equal
            '98000000', //lockentity Call
            '33000000', //If statement
            '01000000', //If statement
            '01000000', //If statement
            '24000000', //If statement
            '01000000', //If statement
            '00000000', //If statement
            '3f000000', //store value
            'cc020000', //unknown
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0b000000', //value
            '10000000', //parameter (Read String var)
            '01000000', //parameter (Read String var)
            '10000000', //nested string return result
            '02000000', //nested string return result
            '77000000', //getentity Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '03000000', //value
            '10000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            'e9000000', //graphmodifyconnections Call

            
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
        list($sectionCode, $sectionDATA) = $compiler->parse($script);
//var_dump($sectionCode);
//exit;
        $this->assertEquals($sectionCode, $expected, 'The bytecode is not correct');
    }


}