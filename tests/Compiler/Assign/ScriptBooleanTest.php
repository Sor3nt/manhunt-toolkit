<?php
namespace App\Tests\Command;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ScriptBooleanTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;

            script OnCreate;
                var
                    result : boolean;
                begin
            		result := FALSE;
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

            '12000000', // parameter
            '01000000', // parameter
            '00000000', // false
            '15000000', // parameter (Read String var)
            '04000000', // parameter (Read String var)

            '00000000',
            '01000000',


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
//var_dump($sectionCode);
//exit;
        $this->assertEquals($sectionCode, $expected, 'The bytecode is not correct');
    }

}