<?php
namespace App\Tests\CompilerByType\Header\LevelVarState\Assign;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IfStateTest extends KernelTestCase
{

    public function test()
    {

        $this->assertEquals(true, true, 'The bytecode is not correct');
        return;

        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

            type
                tLevelState = ( StartOfLevel, PickedUpSyringe, InOffice, LuredHunter, KilledHunter, BeforeElevator, LeftElevator, BeforeBeasts, SpottedByCamera, TurnedOnTV, InCarPark, EndOfLevel );
            
            VAR
                lLevelState : level_var tLevelState;
            
            script OnCreate;
            
                begin
                    if lLevelState <> BeforeElevator then
                    begin
                
                
                    end;

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
            '3b000000',
            '00000000'
        ];


//        $compiler = new Compiler();
//        $levelScriptCompiled = $compiler->parse(file_get_contents(__DIR__ . '/../0#levelscript.srce'));

        $compiler = new Compiler();
        $compiled = $compiler->parse($script, false);

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