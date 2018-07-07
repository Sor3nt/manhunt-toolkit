<?php
namespace App\Service\Compiler\Emitter;

class T_PROCEDURE {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $code = [ ];

        /**
         * Create script start sequence
         */
        $code[] = $getLine('10000000');
        $code[] = $getLine('0a000000');
        $code[] = $getLine('11000000');
        $code[] = $getLine('0a000000');
        $code[] = $getLine('09000000');

        foreach ($node['body'] as $node) {
            $resultCode = $emitter( $node );
            foreach ($resultCode as $line) {
                $code[] = $line;
            }

        }

        return $code;
    }

}