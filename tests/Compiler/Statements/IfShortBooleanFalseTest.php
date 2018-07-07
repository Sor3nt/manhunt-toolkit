<?php
namespace App\Tests\Command;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IfShortBooleanFalseTest extends KernelTestCase
{
//
    public function test() {

        $script = "
            scriptmain LevelScript;

            var
                runningCloseCheck : boolean;

            script OnCreate;

                begin
                  if runningCloseCheck = FALSE then
            		RunScript('ButtonCell4_(S)', 'WaitForOpenClose');
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


            '14000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '00000000', //unknown
            '10000000', //If statement
            '01000000', //If statement
            '12000000', //parameter (temp)
            '01000000', //parameter (temp)
            '00000000', //Bool false / int 0
            '0f000000', //parameter (temp)
            '04000000', //parameter (temp)
            '23000000', //If statement
            '04000000', //If statement
            '01000000', //If statement
            '12000000', //If statement
            '01000000', //If statement
            '01000000', //If statement
            '3f000000', //equal
            '1a000000', //If statement(unknown)
            '33000000', //If statement
            '01000000', //If statement
            '01000000', //If statement
            '24000000', //If statement
            '01000000', //If statement
            '00000000', //If statement
            '3f000000', //store value
            '5c000000', //unknown
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '04000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '10000000', //value
            '10000000', //parameter (Read String var)
            '01000000', //parameter (Read String var)
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '14000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '11000000', //value
            '10000000', //parameter (Read String var)
            '01000000', //parameter (Read String var)
            '10000000', //nested string return result
            '02000000', //nested string return result
            'e4000000', //runscript Call


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

        $this->assertEquals($sectionCode, $expected, 'The bytecode is not correct');
    }


}