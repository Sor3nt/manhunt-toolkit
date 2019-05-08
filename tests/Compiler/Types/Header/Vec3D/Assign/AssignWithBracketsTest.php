<?php
namespace App\Tests\CompilerByType\Script\Vec3D\Assign;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AssignWithBracketsTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;
            var
                pos : Vec3D;
                spot : Vec3D;
                space : Vec3D;
                
            script OnCreate;

                begin
                    spot.x := ( (pos.x) +	(space.x * (-0.6)));
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

            //pos.x
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '0c000000', //pos.x
            '10000000', //nested call return result
            '01000000', //nested call return result
            '21000000', //read from object
            '04000000', //read from object
            '01000000', //read from object
            '00000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            
            '0f000000', //read from object
            '02000000', //read from object
            '18000000', //read from object
            '01000000', //read from object
            '04000000', //read from object
            '02000000', //read from object
            
            '10000000', //nested call return result
            '01000000', //nested call return result

            
            

                //space.x
                '21000000', //Prepare string read (DATA table)
                '04000000', //Prepare string read (DATA table)
                '01000000', //Prepare string read (DATA table)
                '18000000', //space.x
                '10000000', //nested call return result
                '01000000', //nested call return result

                '0f000000', //read from object
                '02000000', //read from object
                '18000000', //read from object
                '01000000', //read from object
                '04000000', //read from object
                '02000000', //read from object
                '10000000', //nested call return result
                '01000000', //nested call return result

                //-0.6
                '12000000', //parameter (read simple type (int/float...))
                '01000000', //parameter (read simple type (int/float...))
                '9a99193f', //value -0.6
                '10000000', //nested call return result
                '01000000', //nested call return result

                '4f000000', //turn prev number into negative
                '32000000', //turn prev number into negative
                '09000000', //turn prev number into negative
                '04000000', //turn prev number into negative

                '10000000', //nested call return result
                '01000000', //nested call return result

                '52000000', //Token::T_MULTIPLY
                '10000000', //nested call return result
                '01000000', //nested call return result

            '50000000', //Token::T_ADDITION 


            //spot.x
            '0f000000', //[T_ASSIGN] toObject
            '02000000', //[T_ASSIGN] toObject
            '17000000', //[T_ASSIGN] toObject
            '04000000', //[T_ASSIGN] toObject
            '02000000', //[T_ASSIGN] toObject
            '01000000', //[T_ASSIGN] toObject

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
            $index = 0;
            foreach ($compiled['CODE'] as $index => $item) {
                if (!isset($expected[$index])){
                    echo "TO MUCH, got " . $compiled['CODE'][$index] . " " . $compiled['CODE'][$index]->debug . "\n";

                }else{
                    if ($expected[$index] == $item){
                        echo ($index + 1) . '->' . $item . " " . $item->debug . "\n";
                    }else{
                        echo "MISSMATCH need " . $expected[$index] . " got " . $compiled['CODE'][$index] . " " . $compiled['CODE'][$index]->debug . "\n";
                    }

                }
            }

            exit;
        }


        $this->assertEquals($compiled['CODE'], $expected, 'The bytecode is not correct');
    }

}