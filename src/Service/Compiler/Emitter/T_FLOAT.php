<?php
namespace App\Service\Compiler\Emitter;


use App\Service\Helper;

class T_FLOAT {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $value = (float) $node['value'];

        if ($value < 0) $value = $value * -1;

        return [
            $getLine('12000000'),
            $getLine('01000000'),

            $getLine(Helper::fromFloatToHex( $value ))
        ];
    }

}