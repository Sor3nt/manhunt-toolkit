<?php
namespace App\Tests\Special;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ScriptLevelVarTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain EntityScript;
    
                ENTITY
                    tLockerTut1 : et_name;
                    
                script OnEnterTrigger;
                VAR
                    pos : vec3d;
                    bLockerTutDisplayed : level_var boolean;
                begin
                    WriteDebug('tLockerTut1 Entered');
                
                    if bLockerTutDisplayed = FALSE then 
                    begin
                        bLockerTutDisplayed := TRUE;
                        DisplayGameText('OPEN_PC');
                    end;
                    
                    RemoveThisScript;
                end;
            end.
            
        ";

        $expected = [

            "10000000",
            "0a000000",
            "11000000",
            "0a000000",
            "09000000",

            "34000000",
            "09000000",
            "10000000",

            "21000000",
            "04000000",
            "01000000",
            "00000000",
            "12000000",
            "02000000",
            "14000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "73000000",
            "74000000",
            "1b000000",
            "ec170000",
            "04000000",
            "01000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "00000000",
            "0f000000",
            "04000000",
            "23000000",
            "04000000",
            "01000000",
            "12000000",
            "01000000",
            "01000000",
            "3f000000",
            "ac000000",
            "33000000",
            "01000000",
            "01000000",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "0c010000",
            "12000000",
            "01000000",
            "01000000",
            "1a000000",
            "01000000",
            "ec170000",
            "04000000",
            "21000000",
            "04000000",
            "01000000",
            "18000000",
            "12000000",
            "02000000",
            "08000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "04010000",
            "e8000000",
            "11000000",
            "09000000",
            "0a000000",
            "0f000000",
            "0a000000",
            "3b000000",
            "00000000"

        ];

        $compiler = new Compiler();
        $levelScriptCompiled = $compiler->parse(file_get_contents(__DIR__ . '/../0#levelscript.srce'));


        $compiler = new Compiler();
        $compiled = $compiler->parse($script, $levelScriptCompiled);
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

        var_dump($compiled);

    }


}