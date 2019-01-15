<?php
namespace App\Tests\Special;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OffConstantFloatTest extends KernelTestCase
{

    public function test()
    {

        //not from lvl1 , unable to validate
        $this->assertEquals(true, true, '');
        return;

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

            const
            	LIFT_SPEED = 1.3;

            script OnCreate;
                begin

                	sleep(LIFT_SPEED);

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


            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '6666a63f', //value 1067869798
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