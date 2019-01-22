<?php
namespace App\Service\Compiler\Emitter;



use App\Service\Helper;

class T_INT {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){
        $debugMsg = "[T_INT] map ";

        $value = (int) $node['value'];

        if ($value < 0) $value = $value * -1;

        return [

            $getLine('12000000', false, $debugMsg),
            $getLine('01000000', false, $debugMsg),

            $getLine(Helper::fromIntToHex( $value ), false, $debugMsg . 'value ' . $value )
        ];
    }

}