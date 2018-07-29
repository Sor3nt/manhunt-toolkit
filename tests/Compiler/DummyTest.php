<?php
namespace App\Tests\Compiler;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DummyTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
            
            var
                me : string[30];

            script OnCreate;
                begin
                    me := GetEntityName(this);
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
            '49000000', //value 73
            '10000000', //nested call return result
            '01000000', //nested call return result
            '86000000', //getentityname Call
            '21000000', //Prepare string read (header)
            '04000000', //Prepare string read (header)
            '04000000', //Prepare string read (header)
            '00000000', //Offset in byte
            '12000000', //parameter (read string array? assign?)
            '03000000', //parameter (read string array? assign?)
            '1e000000', //value 30
            '10000000', //parameter (read string array? assign?)
            '04000000', //parameter (read string array? assign?)
            '10000000', //unknown
            '03000000', //unknown
            '48000000', //unknown

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