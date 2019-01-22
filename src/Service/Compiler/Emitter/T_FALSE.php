<?php
namespace App\Service\Compiler\Emitter;


use App\Service\Helper;

class T_FALSE {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){
        $debugMsg = sprintf('[T_FALSE] map ');

        return [
            $getLine('12000000', false, $debugMsg),
            $getLine('01000000', false, $debugMsg),
            $getLine(Helper::fromIntToHex( 0 ), false, $debugMsg . ' offset 0')
        ];

    }

}