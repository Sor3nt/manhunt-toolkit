<?php
namespace App\Tests\CompilerByType\Header\LevelVarBoolean\Assign;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AssignTrueTest extends KernelTestCase
{
//
    public function test() {

        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

            var
                stealthOneLooper : level_var boolean;

            script OnCreate;

                begin
                    stealthOneLooper := TRUE;
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

            '12000000', //parameter (access level_var)
            '01000000', //parameter (access level_var)
            '01000000', //Bool true / int 1

            '1a000000', //parameter (access level_var)
            '01000000', //parameter (access level_var)
            'b0170000', //unknown
            '04000000', //

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
        $levelScriptCompiled = $compiler->parse(file_get_contents(__DIR__ . '/../0#levelscript.srce'));

        $compiler = new Compiler();
        $compiled = $compiler->parse($script, $levelScriptCompiled);

        if ($compiled['CODE'] != $expected){
            foreach ($compiled['CODE'] as $index => $item) {
                if ($expected[$index] == $item){
                    echo ($index + 1) . '->' . $item . "\n";
                }else{
                    echo "MISSMATCH need " . $expected[$index] . " got " . $compiled['CODE'][$index] . "\n";
                }
            }
            exit;
        }

        $this->assertEquals($compiled['CODE'], $expected, 'The bytecode is not correct');
    }


}