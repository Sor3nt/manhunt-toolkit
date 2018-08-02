<?php
namespace App\Tests\Compiler\Statements;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IfNestedTest extends KernelTestCase
{
//
    public function test() {

        $script = "
            scriptmain LevelScript;

            var
                stealthOneLooper : level_var boolean;

            script OnCreate;

                begin
                  	if 
                  	    (GetDamage(GetPlayer) < 125) 
                  	    AND 
                  	    (
                  	        (GetEntity('G_First_Aid_(CT)13') <> NIL) OR 
                  	        (GetEntity('G_First_Aid_(CT)14') <> NIL) OR 
                  	        (GetEntity('G_First_Aid_(CT)15') <> NIL)
                  	    ) then
                    stealthOneLooper := TRUE;
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
//                  	if
//                  	    (GetDamage(GetPlayer) < 125)
//                  	    AND
//                  	    (
//                  	        (GetEntity('G_First_Aid_(CT)13') <> NIL) OR
//                  	        (GetEntity('G_First_Aid_(CT)14') <> NIL) OR
//                  	        (GetEntity('G_First_Aid_(CT)15') <> NIL)
//                  	    ) then
//                    stealthOneLooper := TRUE;
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


            '8a000000', //GetPlayer Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '84000000', //GetDamage Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (temp int)
            '01000000', //parameter (temp int)
            '7d000000', //value 125
            '0f000000', //parameter (temp int)
            '04000000', //parameter (temp int)
            '23000000', //statement (core)
            '04000000', //statement (core)
            '01000000', //statement (core)
            '12000000', //statement (core)
            '01000000', //statement (core)
            '01000000', //statement (core)
            '3d000000', //statement (core)(operator lower)
            '6c000000', //statement (core)( Offset )
            '33000000', //statement (unknown)
            '01000000', //statement (unknown)
            '01000000', //statement (unknown)
            '10000000', //nested call return result
            '01000000', //nested call return result
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
            '77000000', //getentity Call
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
            'ec000000', //statement (core)( Offset )
            '33000000', //statement (unknown)
            '01000000', //statement (unknown)
            '01000000', //statement (unknown)
            '10000000', //nested call return result
            '01000000', //nested call return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '14000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '13000000', //value 19
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '77000000', //getentity Call
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
            '6c010000', //statement (core)( Offset )
            '33000000', //statement (unknown)
            '01000000', //statement (unknown)
            '01000000', //statement (unknown)
            '0f000000', //unknown
            '04000000', //unknown
            '27000000', //statement (unknown hmm)
            '01000000', //statement (unknown hmm)
            '04000000', //statement (unknown hmm)
            '10000000', //nested call return result
            '01000000', //nested call return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '28000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '13000000', //value 19
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '77000000', //getentity Call
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
            '00020000', //statement (core)( Offset )
            '33000000', //statement (unknown)
            '01000000', //statement (unknown)
            '01000000', //statement (unknown)
            '0f000000', //unknown
            '04000000', //unknown
            '27000000', //statement (unknown hmm)
            '01000000', //statement (unknown hmm)
            '04000000', //statement (unknown hmm)
            '0f000000', //unknown
            '04000000', //unknown
            '25000000', //unknown
            '01000000', //unknown
            '04000000', //unknown

            '24000000', //statement (core 2)
            '01000000', //statement (core 2)
            '00000000', //statement (core 2)

            '3f000000', //statement (line offset)
            '58020000', //Offset in byte

            '12000000', //parameter (access level_var)
            '01000000', //parameter (access level_var)
            '01000000', //Bool true / int 1
            '1a000000', //parameter (access level_var)
            '01000000', //parameter (access level_var)
            '3c000000', //unknown
            '04000000', //


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

        if ($sectionCode != $expected){
            foreach ($sectionCode as $index => $item) {
                if ($expected[$index] == $item){
                    echo ($index + 1) . '->' . $item . "\n";
                }else{
                    echo "MISSMATCH need " . $expected[$index] . " got " . $sectionCode[$index] . "\n";
                }
            }
            exit;
        }

        $this->assertEquals($sectionCode, $expected, 'The bytecode is not correct');
    }


}