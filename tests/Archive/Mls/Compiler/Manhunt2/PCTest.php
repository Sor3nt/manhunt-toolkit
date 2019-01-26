<?php
namespace App\Tests\Archive\Mls\Compiler\Manhunt2;

use App\MHT;
use App\Service\Compiler\Compiler;
use App\Service\Resources;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PCTest extends KernelTestCase
{

    public function testLevelScript()
    {
        echo "\n* MLS: Testing Manhunt 2 PC (compile) ==> ";

        $resources = new Resources();
        $resources->workDirectory = explode("/tests/", __DIR__)[0] . "/tests/Resources";

        $resource = $resources->load('/Archive/Mls/Manhunt2/PC/A01_Escape_Asylum.mls', MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC);
        $handler = $resource->getHandler();

        $mhls = $handler->unpack( $resource->getInput(), MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC);

        // compile levelscript
        $compiler = new Compiler();
        $levelScriptCompiled = $compiler->parse($mhls[0]['SRCE'], false, 'mh2');

//        foreach ($levelScriptCompiled as $index => $section) {
//
//            //only used inside the compiler
//            if ($index == "extra") continue;
//
//            //memory is not correct but works...
//            if ($index == "DMEM") continue;
//            if ($index == "SMEM") continue;
//
//            //we do not generate the LINE (debug stuff)
//            if ($index == "LINE") continue;
//
//            $this->assertEquals(
//                $mhls[0][$index],
//                $section,
//                $index . " Mismatch"
//            );
//        }

        $test = 58; // operator not found
//        $test = 68; // unable to handle T_ASSIGN
//        $test = 82; // T_VARIABLE: unable to find variable offset for bLockerTutDisplayed
//        $test = 83; // T_VARIABLE: unable to find variable offset for bLockerTutDisplayed
//        $test = 94;
//        $test = 97;


//        $test = 37;
        for($i = 0; $i < 58 ; $i++){
//        for($i = $test; $i < $test+1 ; $i++){
            $testScript = $mhls[$i];

//            var_dump($testScript['ENTT']['name']);

            //compile a other script based on the levelscript
            $compiled = $compiler->parse($testScript['SRCE'], $levelScriptCompiled, 'mh2');



            if ($testScript['CODE'] != $compiled['CODE']){
                foreach ($testScript['CODE'] as $index => $item) {
                    if ($compiled['CODE'][$index] == $item){
                        echo ($index + 1) . '->' . $item . " " . $compiled['CODE'][$index]->debug . "\n";
                    }else{
                        echo "MISMATCH need |" . $item . "| got |" . $compiled['CODE'][$index] . " " . $compiled['CODE'][$index]->debug . "|\n";
                    }
                }
                exit;
            }
//exit;
            $this->assertEquals(
                $testScript['CODE'],
                $compiled['CODE']
            );

//            foreach ($compiled as $index => $section) {
//
//                //only used inside the compiler
//                if ($index == "extra") continue;
//
//                //memory is not correct but works...
//                if ($index == "DMEM") continue;
//                if ($index == "SMEM") continue;
//
//                //we do not generate the LINE (debug stuff)
//                if ($index == "LINE") continue;
//                if ($index == "STAB" && count($section) == 0) continue;
//
//                if ($index == "DATA"){
//                    if ($testScript[$index] != $section){
////                        var_dump(bin2hex($testScript[$index][0]), $section);
//                    }
//                }
//
//                $this->assertEquals(
//                    $testScript[$index],
//                    $section,
//                    $index . " Mismatch " . $testScript['NAME']
//                );
//            }

        }
    }
}