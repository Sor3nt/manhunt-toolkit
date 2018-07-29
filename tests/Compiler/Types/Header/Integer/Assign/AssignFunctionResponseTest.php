<?php
namespace App\Tests\CompilerByType\Header\Integer\Assign;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AssignFunctionResponseTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;

            var
                animLength : integer;

            script OnCreate;
                begin
            		animLength := GetAnimationLength('ASY_NURSE_ATTACK4A');
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

            '21000000', // Prepare string read (DATA table)
            '04000000', // Prepare string read (DATA table)
            '01000000', // Prepare string read (DATA table)
            '00000000', // offset

            '12000000', // parameter (Read String var)
            '02000000', // parameter (Read String var)
            '13000000', // ASY_NURSE_ATTACK4A + 1
            '10000000', // parameter (Read String var)
            '01000000', // parameter (Read String var)

            '10000000', // string pointer move
            '02000000', // string pointer move

            '49030000', // getanimationlength Call

            '16000000',
            '04000000',
            '14000000',
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