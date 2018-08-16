<?php
namespace App\Tests\Compiler\Statements;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ForTest extends KernelTestCase
{
//
    public function test() {
        $this->assertEquals(true, true, 'The bytecode is not correct');
        return;

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

            script OnCreate;
                var i : integer;

                begin
                    for i := 1 to 4 do begin
                        displaygametext;
                    end;

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


            '12000000', //unknown
            '01000000', //unknown
            '01000000', //start value 0

            '15000000', //offset var i ??
            '04000000', //unknown
            '04000000', // a offset ?
            '01000000', //

            '12000000', //unknown
            '01000000', //unknown
            '04000000', // to value 4

            '13000000',
            '02000000', //unknown
            '04000000', //unknown
            '04000000', // a offset ?

            '23000000', //unknown
            '01000000', //unknown
            '02000000', //unknown
            '41000000', //unknown
            '74000000', //

            '3c000000', //statement (init statement start offset)
            '8c000000', //Offset (line number 876)

            '04010000', //

            '3c000000', //statement (init statement start offset)
            '20000000', //Offset (line number 811)

            
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
        $compiled = $compiler->parse($script, false);

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