<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Compiler\Evaluate;

class T_SELF {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $code = [];

        Evaluate::readIndex(
            '49000000',
            $code,
            $getLine
        );

        return $code;

    }

}