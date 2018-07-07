<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;
use App\Service\Compiler\Token;

class T_SELF {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){
        return [ $getLine("49000000") ];
    }

}