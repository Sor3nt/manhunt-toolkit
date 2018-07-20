<?php
namespace App\Tests\Command;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WhileShortTest extends KernelTestCase
{
//
    public function test() {

        $script = "
            scriptmain LevelScript;

            script OnCreate;

                begin
                    while IsPlayerWalking then sleep(1500);
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
    
            'ed020000', //IsPlayerWalking call
            '24000000', //statement (core 2)
            '01000000', //statement (core 2)
            '00000000', //statement (core 2)
            '3f000000', //statement (line offset)
            '3c000000', //Offset in byte
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            'dc050000', //value 1500
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call

            '3c000000', //line offset
            '24000000', //unknown

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
        foreach ($sectionCode as $item) {
            echo $item . "\n";
}
        $this->assertEquals($sectionCode, $expected, 'The bytecode is not correct');
    }


}