<?php
namespace App\Service\Compiler\Emitter;

use App\MHT;
use App\Service\Helper;

class T_WHILE {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $code = [];

        $resultCode = T_IF::map($node, $getLine,$emitter, $data, true );

        $firstLine = $resultCode[0]->lineNumber - 1;

        foreach ($resultCode as $line) {
            $line->debug = '[T_WHILE] map ' . $line->debug;
            $code[] = $line;
        }

        //this is like a "goto" function, 3c == goto => line offset
        //move pointer back to while start
        $code[] = $getLine('3c000000', false, '[T_WHILE] map goto');
        if ($data['game'] == MHT::GAME_MANHUNT){
            $code[] = $getLine(Helper::fromIntToHex(($firstLine * 4) + 12), false, '[T_WHILE] map offset (first line) ');
        }else{
            $code[] = $getLine(Helper::fromIntToHex($firstLine * 4), false, '[T_WHILE] map offset (first line) ');
        }

        return $code;

    }

}