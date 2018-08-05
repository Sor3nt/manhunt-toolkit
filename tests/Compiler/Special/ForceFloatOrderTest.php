<?php
namespace App\Tests\Special;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ForceFloatOrderTest extends KernelTestCase
{

    public function test()
    {
//        $this->assertEquals(true, true, 'The bytecode is not correct');
//        return;


        $script = "
            
                            
                scriptmain LevelScript;
                
                script OnCreate;
                var
                    pos : Vec3D;
                
                begin
        			SetVector(pos, -15, 24.09, 24);
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



            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '0c000000', //Offset in byte

            '10000000', //nested call return result
            '01000000', //nested call return result






            '12000000', //int init
            '01000000', //int init
            '0f000000', //15
            '2a000000', //negate
            '01000000', //negate

            '10000000', //nested call return result
            '01000000', //nested call return result

            '4d000000', //negate int
            '10000000', //negate int
            '01000000', //negate int






            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))

            '52b8c041', //value 24.09
            '10000000', //nested call return result
            '01000000', //nested call return result





            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '18000000', //value 24

            '10000000', //nested call return result
            '01000000', //nested call return result

            '4d000000', //
            '10000000', //nested call return result
            '01000000', //nested call return result

            '84010000', //setvector Call

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