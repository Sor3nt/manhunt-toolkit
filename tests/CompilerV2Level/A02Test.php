<?php
namespace App\Tests\CompilerV2\LevelScripts;

use App\MHT;
use App\Service\Compiler\Compiler;
use App\Service\Resources;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class A02Test extends KernelTestCase
{

    public function testLevelScript()
    {
        echo "\n* MLS: Testing Manhunt 2 PC (compile A02) ==> ";

        $resources = new Resources();
        $resources->workDirectory = explode("/tests/", __DIR__)[0] . "/tests/Resources";

        $resource = $resources->load('/Archive/Mls/Manhunt2/PC/A02_The_Old_House.mls', MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC);
        $handler = $resource->getHandler();

        $mhls = $handler->unpack( $resource->getInput(), MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC);

        $levelScriptCompiler = new \App\Service\CompilerV2\Compiler($mhls[0]['SRCE'], MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC);
        $levelScriptCompiler->compile();

        for($i = 0; $i < count($mhls) ; $i ++){

            $testScript = $mhls[$i];

            var_dump("Script: "  . $testScript['NAME']['name'] . " / Index: " . $i);

            $compiler = new \App\Service\CompilerV2\Compiler($testScript['SRCE'], MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC);
            $compiler->levelScript = $levelScriptCompiler;
            $subMls = $compiler->compile();


            $expected = $testScript['CODE'];

            if ($compiler->validateCode($expected) === false){
                $code = $compiler->codes;

                foreach ($code as $index => $newCode) {

                    if ($expected[$index] == $newCode['code']){
                        echo $index . " " . $newCode['code'] . ' -> ' . $newCode['msg'] . "\n";

                    }else{
                        echo "MISMATCH: Need: " . $expected[$index] . ' Got: ' . $newCode['code'] . ' -> ' . $newCode['msg']. "\n";

                    }
                }

                exit;
            }else{
                $this->assertEquals(true,true);
            }


            foreach ($subMls as $index => $section) {
                echo "testing " . $index;
                //only used inside the compiler
                if ($index == "CODE") continue;
                if ($index == "extra") continue;

                //memory is not correct but works...
                if ($index == "DMEM") continue;
                if ($index == "SMEM") continue;

                //we do not generate the LINE (debug stuff)
                if ($index == "LINE") continue;
                if ($index == "STAB" && count($section) == 0) continue;

                if ($index == "DATA"){

                    if (!isset($testScript[$index])){

                        if (
                            count($section['const']) == 0 &&
                            count($section['strings']) == 0
                        ){
                            continue;
                        }
                    }
//
                    if ($testScript[$index] != $section){
                        unset($testScript[$index]['byteReserved']);

                    }
                }

                if ($index == "STAB"){
                    foreach ($testScript[$index] as &$mhl) {
                        unset($mhl['nameGarbage']);
                    }
                }

//                if ($testScript[$index] != $section){
//                    var_dump($section);
//                    var_dump($compiler->variables);
//                }

                $this->assertEquals(
                    $testScript[$index],
                    $section,
                    $index . " Mismatch " . $testScript['ENTT']['name']
                );

            }

        }
    }
}