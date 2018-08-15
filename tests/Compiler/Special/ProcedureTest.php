<?php
namespace App\Tests\Special;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProcedureTest extends KernelTestCase
{

    public function test()
    {


//        $this->assertEquals(true, true, 'The bytecode is not correct');
//        return;

        $script = "
            scriptmain LevelScript;
                
                entity
                    A01_Escape_Asylum : et_level;

                procedure InitAI;
                    var
                        pos : Vec3D;
                    begin
    
    
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

            '34000000',
            '09000000',
            '0c000000',


            // script end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3a000000',
            '04000000'
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