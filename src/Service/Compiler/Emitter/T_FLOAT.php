<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;

class T_FLOAT {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $value = (float) $node['value'];

        if ($value < 0) $value = $value * -1;

        return [ $getLine(Helper::fromFloatToHex( $value )) ];
    }

}