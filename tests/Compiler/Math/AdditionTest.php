<?php
namespace App\Tests\Command;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AdditionTest extends KernelTestCase
{

    public function testLevelVar()
    {

        $script = "
            scriptmain LevelScript;

            VAR
                stealthTutSpotted : level_var integer;

            script OnCreate;
                begin
                    stealthTutSpotted := stealthTutSpotted + 1;
                end;
            end.

        ";

        $expected = [

            // procedure start
            '10000000',
            '0a000000',
            '11000000',
            '0a000000',
            '09000000',


            '1b000000', //unknown
            '00000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (temp int)
            '01000000', //parameter (temp int)
            '01000000', //value 1
            '0f000000', //parameter (temp int)
            '04000000', //parameter (temp int)
            '31000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '1a000000', //unknown
            '01000000', //unknown
            '00000000', //unknown
            '04000000', //unknown

            // script end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3b000000',
            '00000000',

        ];

        $compiler = new Compiler();
        list($sectionCode, $sectionDATA) = $compiler->parse($script);

        $this->assertEquals($sectionCode, $expected, 'The bytecode is not correct');
    }

    public function testHeaderVar()
    {

        $script = "
            scriptmain LevelScript;

            var
                animLength : integer;

            script OnCreate;
                begin
                    animLength := animLength + 1500;
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

            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '00000000', //Offset
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (temp int)
            '01000000', //parameter (temp int)
            'dc050000', //value 1500
            '0f000000', //parameter (temp int)
            '04000000', //parameter (temp int)
            '31000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '11000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '15000000', //unknown
            '04000000', //unknown
            '00000000', //unknown
            '01000000', //unknown

            // script end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3b000000',
            '00000000',

        ];

        $compiler = new Compiler();
        list($sectionCode, $sectionDATA) = $compiler->parse($script);

        $this->assertEquals($sectionCode, $expected, 'The bytecode is not correct');
    }

    public function testScriptVar()
    {

        $script = "
            scriptmain LevelScript;
            script OnCreate;
                VAR
                    openCount : integer;
                begin
                    openCount := openCount + 1;
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

            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '00000000', //Offset
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (temp int)
            '01000000', //parameter (temp int)
            '01000000', //value 1
            '0f000000', //parameter (temp int)
            '04000000', //parameter (temp int)
            '31000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '15000000', //unknown
            '04000000', //unknown
            '00000000', //unknown
            '01000000', //unknown
            
            // script end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3b000000',
            '00000000',

        ];

        $compiler = new Compiler();
        list($sectionCode, $sectionDATA) = $compiler->parse($script);
        foreach ($sectionCode as $item) {
            echo $item . "\n";
}
        $this->assertEquals($sectionCode, $expected, 'The bytecode is not correct');
    }

}