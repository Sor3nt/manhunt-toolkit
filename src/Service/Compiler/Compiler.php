<?php
namespace App\Service\Compiler;

use App\MHT;

class Compiler {


    public function parse($source, $levelScript = false, $game = MHT::GAME_MANHUNT_2, $platform = MHT::PLATFORM_PC){

        $newCompiler = new NewCompiler($source, $levelScript, $game, $platform);
        return $newCompiler->compile();

    }

}