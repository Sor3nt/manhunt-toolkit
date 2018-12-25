<?php
namespace App\Service\Compiler\Parser;

use App\Bytecode\Helper;
use App\Service\Compiler\Evaluate;
use App\Service\Compiler\EvaluateAssign;
use App\Service\Compiler\Token;

class T_PROCEDURE {


    static public function map( $tokens, $current, \Closure $parseToken ){
        $code = [];


        $starCurrent = $current;

        $isForward = true;

        while ($current < count($tokens)) {
            $token = $tokens[$current];

            if ($token['type'] == Token::T_BEGIN){
                $isForward = false;
                break;
            }

            if ($token['type'] == Token::T_FORWARD){
                $isForward = true;
                break;
            }

            $current++;

        }

        $current = $starCurrent;

        /**
         * we have a forward define section
         */
        if ($isForward == true){

            $current++;

            $node = [
                'type' => Token::T_FORWARD,
                'to' => trim($tokens[$current]['value']),
                'section' => Token::T_PROCEDURE,
                'params' => [],
            ];

            $current++;

            if ($tokens[$current]['type'] == Token::T_BRACKET_OPEN){

                $current++;

                while ($current < count($tokens)) {

                    if ($tokens[$current]['type'] == Token::T_BRACKET_CLOSE){
                        $current++;
                        break;
                    }else{
                        $node['params'][] = $tokens[$current];
                    }

                    $current++;
                }
            }

            if ($tokens[$current]['type'] !== Token::T_LINEEND){
                throw new \Exception('Parser: parseForward T_LINEEND expected');
            }

            $current++;

            if (strtolower($tokens[$current]['value']) != "forward"){
                throw new \Exception('Parser: parseForward FORWARD expected');
            }

            $current++;

            /**
             * we have a procedure define section
             */
        }else{
            return T_SCRIPT::map($tokens, $current, $parseToken);
        }

        return [
            $current, $node
        ];
    }
}