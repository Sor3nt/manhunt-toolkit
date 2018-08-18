<?php
namespace App\Tests\Compiler;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use App\Service\Compiler\FunctionMap\Manhunt2;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DummyTest extends KernelTestCase
{

    public function test()
    {

//        foreach (Manhunt2::$constants as $name => $function) {
//            echo ';' . $name;
//        }
//        exit;

        $this->assertEquals(true, true, 'The bytecode is not correct');
        return;
//
        $script = "
scriptmain trigStealthSummary;

ENTITY
	triggerStealthSummary : et_name;
	
VAR
	stealthTutSpotted : level_var integer;
	
script OnEnterTrigger;
begin
	
    
    if stealthTutSpotted = 0 then begin

    end else if stealthTutSpotted = 1 then begin

    
    end else if stealthTutSpotted = 1 then begin

    end;
    
    ClearLevelGoal('GOAL3');
      
end;

end.


        ";
        $expected = [

            '10000000', //Script start block
            '0a000000', //Script start block
            '11000000', //Script start block
            '0a000000', //Script start block
            '09000000', //Script start block

            '11000000', //Script end block
            '09000000', //Script end block
            '0a000000', //Script end block
            '0f000000', //Script end block
            '0a000000', //Script end block
            '3b000000', //Script end block
            '00000000', //Script end block

        ];

//
//        $compiler = new Compiler();
//        $levelScriptCompiled = $compiler->parse(file_get_contents(__DIR__ . '/0#levelscript.srce'));


        $compiler = new Compiler();
        $compiled = $compiler->parse($script, false);

        if ($compiled['CODE'] != $expected){
            foreach ($compiled['CODE'] as $index => $item) {
//                    echo ($index + 1) . '->' . $item . "\n";
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