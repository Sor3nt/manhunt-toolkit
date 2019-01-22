<?php
namespace App\Service\Compiler\Emitter;

class T_SELF {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){
        $debugMsg = sprintf('[T_SELF] map ');

        return [
            $getLine('12000000', false, $debugMsg),
            $getLine('01000000', false, $debugMsg),
            $getLine("49000000", false, $debugMsg)
        ];
    }

}