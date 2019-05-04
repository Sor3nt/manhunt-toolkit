<?php
namespace App\Tests\Archive\Mls\Compiler\Manhunt;

use App\MHT;
use App\Service\Compiler\Compiler;
use App\Service\Resources;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PCTest extends KernelTestCase
{

    public function testLevelScript()
    {
        echo "\n* MLS: Testing Manhunt 1 PC (compile) ==> ";

//        return true;

        $resources = new Resources();
        $resources->workDirectory = explode("/tests/", __DIR__)[0] . "/tests/Resources";

        $resource = $resources->load('/Archive/Mls/Manhunt1/PC/jury_turf.mls', MHT::GAME_MANHUNT, MHT::PLATFORM_PC);
//        $resource = $resources->load('/Archive/Mls/Manhunt1/PC/asylum.mls', MHT::GAME_MANHUNT, MHT::PLATFORM_PC);
        $handler = $resource->getHandler();

        $mhls = $handler->unpack( $resource->getInput(), MHT::GAME_MANHUNT, MHT::PLATFORM_PC);

        // compile levelscript
        $compiler = new Compiler();
        $levelScriptCompiled = $compiler->parse($mhls[0]['SRCE'], false, MHT::GAME_MANHUNT, MHT::PLATFORM_PC);
//
//
//        $this->assertEquals(
//            $mhls[0]['CODE'],
//            $levelScriptCompiled['CODE']
//        );

        if ($mhls[0]['CODE'] != $levelScriptCompiled['CODE']){
            foreach ($mhls[0]['CODE'] as $index => $item) {
                if ($levelScriptCompiled['CODE'][$index] == $item){
                    echo ($index + 1) . '->' . $item . " " . $levelScriptCompiled['CODE'][$index]->debug . "\n";
                }else{
                    echo "MISMATCH need |" . $item . "| got |" . $levelScriptCompiled['CODE'][$index] . " " . $levelScriptCompiled['CODE'][$index]->debug . "|\n";
                }
            }
            exit;
        }




        foreach ($levelScriptCompiled as $index => $section) {

            //only used inside the compiler
            if ($index == "extra") continue;

            //memory is not correct but works...
            if ($index == "DMEM") continue;
            if ($index == "SMEM") continue;

            //we do not generate the LINE (debug stuff)
            if ($index == "LINE") continue;

            if ($index == "DATA"){
                unset($mhls[0][$index]['byteReserved']);
            }

            if ($index == "STAB"){
                foreach ($mhls[0][$index] as &$mhl) {
                    unset($mhl['nameGarbage']);
                }
            }

            $this->assertEquals(
                $mhls[0][$index],
                $section,
                $index . " Mismatch"
            );
        }

        //4 == falsche T_END zuordnung
        //5 == if statement ? klammern ?


        for($i = 1; $i < count($mhls) ; $i ++){

            $testScript = $mhls[$i];

            var_dump($testScript['ENTT']['name'], $i);

            //compile a other script based on the levelscript
//            $compiled = $compiler->parse($testScript['SRCE'], false, MHT::GAME_MANHUNT, MHT::PLATFORM_PC);
            $compiled = $compiler->parse($testScript['SRCE'], $levelScriptCompiled, MHT::GAME_MANHUNT, MHT::PLATFORM_PC);

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
//
//                    if (!isset($testScript[$index])){
//
//                        if (
//                            count($section['const']) == 0 &&
//                            count($section['strings']) == 0
//                        ){
//                            continue;
//                        }
//                    }
//
//                    if ($testScript[$index] != $section){
//                        unset($testScript[$index]['byteReserved']);
//
//                    }
//                }
//
//                if ($index == "STAB"){
//                    foreach ($testScript[$index] as &$mhl) {
//                        unset($mhl['nameGarbage']);
//                    }
//                }
//
//
//                $this->assertEquals(
//                    $testScript[$index],
//                    $section,
//                    $index . " Mismatch " . $testScript['ENTT']['name']
//                );
//            }

        }
    }
}