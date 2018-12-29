<?php
namespace App\Tests\CompilerByType\Script\Integer\Math;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MultiplyTest extends KernelTestCase
{


    public function testScriptVar()
    {

        $script = "
            scriptmain LevelScript;
                        
            entity
                A01_Escape_Asylum : et_level;

            script OnCreate;
                VAR
            		killableHunters : integer;
                begin
            		SetMaxScoreForLevel(killableHunters * 4);
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



            '34000000',
            '09000000',
            '04000000',



            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '04000000', //Offset
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (temp int)
            '01000000', //parameter (temp int)
            '04000000', //value 4

            //multiply
            '0f000000', //parameter (temp int)
            '04000000', //parameter (temp int)
            '35000000', //unknown
            '04000000', //unknown

            '10000000', //nested call return result
            '01000000', //nested call return result

            '10000000', //nested call return result
            '01000000', //nested call return result

            '59030000', //SetMaxScoreForLevel Call

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