<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Compiler\Evaluate;
use App\Service\Compiler\Token;

class T_ASSIGN {


    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        Evaluate::processAssign(
            $node,
            $code,
            $getLine,
            $emitter,
            $data
        );

        return $code;
    }

}