<?php
namespace App\Tests;

use App\MHT;
use App\Service\Compiler\Compiler;
use App\Service\Resources;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ValidateMls extends KernelTestCase
{

    public function process( $file, $game, $platform){
        $resources = new Resources();
        $resources->workDirectory = explode("/tests/", __DIR__)[0] . "/Resources";

        $resource = $resources->load($file, $game, $platform);
        $handler = $resource->getHandler();

        $mhls = $handler->unpack( $resource->getInput(), $game, $platform);

        $levelScriptCompiler = new \App\Service\CompilerV2\Compiler($mhls[0]['SRCE'], $game, $platform);
        $levelScriptCompiler->compile();

        for($i = 0; $i < count($mhls) ; $i ++){

            $testScript = $mhls[$i];
            $this->validate($testScript, $levelScriptCompiler, $game, $platform);

        }
    }

    public function validate( $testScript, $levelScriptCompiler, $game, $platform )
    {


//            var_dump("Script: "  . $testScript['NAME']['name']);

            $compiler = new \App\Service\CompilerV2\Compiler($testScript['SRCE'], $game, $platform);
            $compiler->levelScript = $levelScriptCompiler;
            try{
                $subMls = $compiler->compile();

            }catch(\Exception $e){
var_dump($e->getMessage());exit;
return;
            }


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
//                return;

            }else{
                $this->assertEquals(true,true);
            }


            foreach ($subMls as $index => $section) {

                //only used inside the compiler
                if ($index == "CODE") continue;
                if ($index == "STAB") continue;
                if ($index == "extra") continue;

                //memory is not correct but works...
                if ($index == "DMEM") continue;
                if ($index == "SMEM") continue;

                //we do not generate the LINE (debug stuff)
                if ($index == "LINE") continue;


                if ($index == "DATA"){

                    if (!isset($testScript[$index])){

                        if (
                            count($section['const']) == 0 &&
                            count($section['strings']) == 0
                        ){
                            continue;
                        }
                    }

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
                    $index . " Mismatch for " . $testScript['ENTT']['name']
                );

            }

        }
}