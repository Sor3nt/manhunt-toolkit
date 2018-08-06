<?php
namespace App\Service\Compiler\Emitter;

use App\Bytecode\Helper;

class T_TRUE {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){
        return [
            $getLine('12000000'),
            $getLine('01000000'),

            $getLine(Helper::fromIntToHex( 1 ))
        ];
    }

}