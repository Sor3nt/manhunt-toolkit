<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;

class T_FALSE {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){
        return [ $getLine(Helper::fromIntToHex( 0 ))];

    }

}