<?php
namespace App\Service\Compiler\Emitter;

class T_IS_EQUAL {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        return [
            $getLine('3f000000')
        ];
    }

}