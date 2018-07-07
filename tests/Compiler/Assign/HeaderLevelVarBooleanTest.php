<?php
namespace App\Tests\Command;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class HeaderLevelVarBooleanTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
            
            VAR
                stealthTwoHeard : level_var boolean;
            
            script OnCreate;
            
                begin
                    stealthTwoHeard := FALSE; 
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
            '00000000', // value int 0

            '1a000000', // assign to level_var
            '01000000', // assign to level_var
            'c0170000', // save into stealthTwoHeard (c0170000)
            '04000000', // assign

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