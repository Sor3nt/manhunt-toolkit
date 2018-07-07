<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;
use App\Service\Compiler\Token;

class T_PROCEDURE_END {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){
        $code = [ ];

        /**
         * Create script end sequence
         */
        $code[] = $getLine('11000000');
        $code[] = $getLine('09000000');
        $code[] = $getLine('0a000000');
        $code[] = $getLine('0f000000');
        $code[] = $getLine('0a000000');
        $code[] = $getLine('3a000000');
        $code[] = $getLine('04000000');


        return $code;


    }

}