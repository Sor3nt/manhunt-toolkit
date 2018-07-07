<?php
namespace App\Tests\Command;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProcedureEndTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;

            procedure OnCreate;
                begin
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
        list($sectionCode, $sectionDATA) = $compiler->parse($script);

        $this->assertEquals($sectionCode, $expected, 'The bytecode is not correct');
    }

}