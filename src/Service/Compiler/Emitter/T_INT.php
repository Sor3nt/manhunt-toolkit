<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;

class T_INT {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $value = (int) $node['value'];

        if ($value < 0) $value = $value * -1;

        return [ $getLine(Helper::fromIntToHex( $value ))];
    }

}