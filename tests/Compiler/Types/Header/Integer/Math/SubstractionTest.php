<?php
namespace App\Tests\CompilerByType\Header\Integer\Math;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SubstractionTest extends KernelTestCase
{


    public function testHeaderVar()
    {

        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

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

            '14000000', //read from script var
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

            '16000000', //unknown
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