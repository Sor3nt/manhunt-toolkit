<?php
namespace App\Service\Compiler;

class Compiler {


    public function parse($source, $levelScript = false, $game = "mh2"){

        if (!defined('GAME')){
            define('GAME', $game);
        }

        $newCompiler = new NewCompiler($source, $levelScript);
        return $newCompiler->compile();

    }

}