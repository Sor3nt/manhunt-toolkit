<?php
namespace App\Tests\CompilerByType\Header\LevelVarInteger\Math;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SubstractionTest extends KernelTestCase
{
////
    public function testLevelVar()
    {

        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

            var numBricks : level_var integer;

            script OnCreate;
                begin
            		numBricks := numBricks - 1;
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
        '33000000', //unknown
        '04000000', //unknown
        '01000000', //unknown
        '11000000', //unknown
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
        $levelScriptCompiled = $compiler->parse(file_get_contents(__DIR__ . '/../0#levelscript.srce'));

        $compiler = new Compiler();
        $compiled = $compiler->parse($script, $levelScriptCompiled);

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