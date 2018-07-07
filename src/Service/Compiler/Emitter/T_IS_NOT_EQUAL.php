<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;
use App\Service\Compiler\Token;

class T_IS_NOT_EQUAL {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){
        return [
            $getLine('40000000')
        ];


    }

}