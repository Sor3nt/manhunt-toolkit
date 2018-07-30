<?php
namespace App\Tests\CompilerByType\Script\Vec3D\Statements;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WhileZTest extends KernelTestCase
{
//
    public function test() {
//$this->assertEquals(true, true, 'The bytecode is not correct');
//return;

        $script = "
            scriptmain LevelScript;

            script OnCreate;
                var
                    pos : Vec3D;

                begin
                    while pos.z > 20.0 do
                    begin
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
            '0c000000',


            '0f000000', //unknown
            '04000000', //unknown
            '44000000', //unknown
            '22000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '0c000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result

            '0f000000', //unknown
            '01000000', //unknown

            '32000000', //unknown
            '01000000', //unknown

            '08000000', //unknown

            '10000000', //nested call return result
            '01000000', //nested call return result

            '0f000000', //unknown
            '02000000', //unknown
            '18000000', //unknown
            '01000000', //unknown
            '08000000', //unknown
            '02000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '0000a041', //value 1101004800
            '10000000', //nested call return result
            '01000000', //nested call return result

            '4e000000', //unknown
            '12000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '42000000', //unknown
            'b8000000', //unknown
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            'd8000000', //Offset (line number 1321)

            '04010000', // displaygametext call
            '3c000000', // loop
            '20000000', // offset

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