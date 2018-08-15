<?php
namespace App\Tests\CompilerByType\String\FunctionCalls;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NestedCallTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

            script OnCreate;

                begin
                    displaygametext(displaygametext('test'))
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

            '21000000', // init string
            '04000000', // init string
            '01000000', // init string
            '00000000', // string offset (pointer)

            '12000000', // init parameter
            '02000000', // init parameter
            '05000000', // value int 5 (test +1)

            '10000000', // assign
            '01000000', // assign

            '10000000', // move pointer
            '02000000', // move pointer

            '04010000', // displaygametext call (hidden call)

            '10000000', // nested call return
            '01000000', // nested call return

            '04010000', // displaygametext call

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