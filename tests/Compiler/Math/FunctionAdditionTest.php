<?php
namespace App\Tests\Math;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FunctionAdditionTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;

            script OnCreate;
                begin
                    Sleep(RandNum(100) + 30);
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

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '64000000', //value 100

            '10000000', //nested call return result
            '01000000', //nested call return result
            '69000000', //randnum Call
            '10000000', //nested call return result
            '01000000', //nested call return result

            '12000000', //parameter (temp int)
            '01000000', //parameter (temp int)
            '1e000000', //value 30
            '0f000000', //parameter (temp int)
            '04000000', //parameter (temp int)

            '31000000', //unknown
            '01000000', //unknown
            '04000000', //unknown

            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call


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