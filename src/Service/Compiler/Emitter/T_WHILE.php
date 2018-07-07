<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;
use App\Service\Compiler\Token;

class T_WHILE {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $code = [];

        if (count($node['condition']) == 1 && $node['condition'][0]['type'] == Token::T_TRUE){


            $code[] = $getLine('24000000');
            $code[] = $getLine('01000000');
            $code[] = $getLine('00000000');
            $code[] = $getLine('3f000000');

            // create a dummy placeholder to keep the line numbers correct
            $offsetPlaceholder = $getLine('');


            $appendCode = [];
            foreach ($node['body'] as $node) {
                $resultCode = $emitter( $node );
                foreach ($resultCode as $line) {
                    $appendCode[] = $line;
                }

            }


            //length offset of the IF statement
            $offsetPlaceholder->hex = Helper::fromIntToHex(($offsetPlaceholder->getLine() + count($appendCode)) * 4);
            $code[] = $getLine($offsetPlaceholder);

            foreach ($appendCode as $line) {
                $code[] = $line;
            }

            return $code;
        }else{

            die("this kind of while is not implemented");
        }



    }

}