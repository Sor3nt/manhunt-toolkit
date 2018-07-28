<?php
namespace App\Tests\Math\Substraction;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class HeaderIntegerTest extends KernelTestCase
{


    public function testHeaderVar()
    {

        $script = "
            scriptmain LevelScript;

            var
                animLength : integer;

            script OnCreate;
                begin
                    animLength := animLength - 1500;
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

            '33000000', //unknown
            '04000000', //unknown
            '01000000', //unknown

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