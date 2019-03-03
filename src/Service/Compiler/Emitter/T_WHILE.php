<?php
namespace App\Service\Compiler\Emitter;

use App\MHT;
use App\Service\Compiler\Evaluate;

class T_WHILE {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $code = [];
        $resultCode = T_IF::map($node, $getLine,$emitter, $data, true );

        $firstLine = $resultCode[0]->lineNumber - 1;
        $firstLine *= 4;
        if ($data['game'] == MHT::GAME_MANHUNT){
            $firstLine += 12;
        }

        foreach ($resultCode as $line) {
            $line->debug = '[T_WHILE] map ' . $line->debug;
            $code[] = $line;
        }

        Evaluate::goto($firstLine, $code, $getLine);

        return $code;
    }
}