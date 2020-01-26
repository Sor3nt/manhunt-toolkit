<?php
namespace App\Tests;

use App\Service\CompilerV2\Compiler;
use App\Service\Resources;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ValidateMls extends KernelTestCase
{

    /**
     * @param $file
     * @param $game
     * @param $platform
     * @throws Exception
     */
    public function process( $file, $game, $platform){
        $resources = new Resources();
        $resources->workDirectory = explode("/tests/", __DIR__)[0] . "/Resources";

        $resource = $resources->load($file, $game, $platform);
        $handler = $resource->getHandler();

        $mhls = $handler->unpack( $resource->getInput(), $game, $platform);

        $levelScriptCompiler = new Compiler($mhls[0]['SRCE'], $game, $platform);
        $levelScriptCompiler->compile();

        for($i = 0; $i < count($mhls) ; $i ++){
            $testScript = $mhls[$i];
            $this->validate($testScript, $levelScriptCompiler, $game, $platform);
        }
    }

    /**
     * @param $testScript
     * @param $levelScriptCompiler
     * @param $game
     * @param $platform
     * @throws Exception
     */
    public function validate( $testScript, $levelScriptCompiler, $game, $platform )
    {

        $compiler = new Compiler($testScript['SRCE'], $game, $platform);
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

            if ($testScript[$index] != $section){
                var_dump($section);
            }

            $this->assertEquals(
                $testScript[$index],
                $section,
                $index . " Mismatch for " . $testScript['ENTT']['name']
            );

        }

    }
}