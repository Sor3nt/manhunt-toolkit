<?php
namespace App\Tests\Compiler;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class HeaderStringArrayTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
            
            VAR
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

            '34000000',
            '09000000',
            '1e000000',

            '12000000',
            '01000000',
            '49000000', // this
            '10000000',
            '01000000',
            '86000000', // GetEntityName call


            '21000000',
            '04000000',
            '04000000',
            '1e000000',

            '12000000',
            '03000000',
            '1e000000',  // 30
            '10000000',
            '04000000',

            '10000000',
            '03000000',
            '48000000',


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