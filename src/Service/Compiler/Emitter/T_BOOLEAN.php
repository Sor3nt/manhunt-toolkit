<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Helper;

class T_BOOLEAN {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){
        $debugMsg = sprintf('[T_BOOLEAN] map ');

        return [
            $getLine('12000000', false, $debugMsg),
            $getLine('01000000', false, $debugMsg),

            $getLine(Helper::fromIntToHex( (int) $node['value'] ), false, $debugMsg . 'value ' . $node['value'])
        ];
    }

}