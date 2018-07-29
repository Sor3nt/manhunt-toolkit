<?php
namespace App\Tests\CompilerByType\Header\Boolean\Assign;

use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AssignTrueTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;

            VAR
                alreadyDone : boolean;

            script OnCreate;

                begin
                    alreadyDone := TRUE;
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


            '12000000', // init parameter
            '01000000', // init parameter
            '01000000', // value int 1
            '16000000', // assign to script var
            '04000000', // assign to script var
            '00000000', // save into alreadyDone
            '01000000', // assign

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