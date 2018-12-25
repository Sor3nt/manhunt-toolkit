<?php
namespace App\Tests\Procedure;

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
                    
                procedure InitAI; FORWARD;
                
                script OnCreate;

                    begin
                        InitAI;
                    end;
    

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

            '34000000', //reserve memory
            '09000000', //reserve memory
            '0c000000', // 12 bytes for the vec3d (3x float a 4byte)


            // procedure end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3a000000',
            '04000000',



            // script start
            '10000000',
            '0a000000',
            '11000000',
            '0a000000',
            '09000000',


            '10000000', //procedure function call
            '04000000', //procedure function call
            '11000000', //procedure function call
            '02000000', //procedure function call
            '00000000', //procedure function call
            '32000000', //procedure function call
            '02000000', //procedure function call
            '1c000000', //procedure function call
            '10000000', //procedure function call
            '02000000', //procedure function call
            '39000000', //procedure function call
            '00000000', //procedure offset
            
            
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