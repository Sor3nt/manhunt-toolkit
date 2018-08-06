<?php
namespace App\Service\Compiler\Emitter;

use App\Bytecode\Helper;

class T_SCRIPT {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $code = [ ];

        /**
         * Create script start sequence
         *
         * Note: we have here no names, its calculated by the offset inside the todo... section
         */
        $code[] = $getLine('10000000');
        $code[] = $getLine('0a000000');
        $code[] = $getLine('11000000');
        $code[] = $getLine('0a000000');
        $code[] = $getLine('09000000');

        /**
         * generate the needed bytes for the script
         */
        $sum = 0;
        foreach ($data['variables'] as $variable) {

            if (
                $variable['section'] == "script"
            ){
                $sum += $variable['size'];
            }
        }

        if ($sum > 0){
            $code[] = $getLine('34000000');
            $code[] = $getLine('09000000');
            $code[] = $getLine(Helper::fromIntToHex($sum));
        }

        foreach ($node['body'] as $node) {
            $resultCode = $emitter( $node );

            if (is_null($resultCode)){
                throw new \Exception('Return was null, a emitter missed a return statement ?');
            }

            foreach ($resultCode as $line) {
                $code[] = $line;
            }
        }

        /**
         * Create script end sequence
         */
        $code[] = $getLine('11000000');
        $code[] = $getLine('09000000');
        $code[] = $getLine('0a000000');
        $code[] = $getLine('0f000000');
        $code[] = $getLine('0a000000');
        $code[] = $getLine('3b000000');
        $code[] = $getLine('00000000');


        return $code;
    }

}