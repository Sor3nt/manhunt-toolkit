<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Helper;

class T_TRUE {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){
        $debugMsg = sprintf('[T_TRUE] map ');

        return [
            $getLine('12000000', false, $debugMsg),
            $getLine('01000000', false, $debugMsg),

            $getLine(Helper::fromIntToHex( 1 ), false, $debugMsg . 'value 1')
        ];
    }

}