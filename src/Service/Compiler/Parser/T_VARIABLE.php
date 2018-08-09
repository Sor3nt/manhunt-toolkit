<?php
namespace App\Service\Compiler\Parser;

use App\Bytecode\Helper;
use App\Service\Compiler\Evaluate;
use App\Service\Compiler\EvaluateAssign;
use App\Service\Compiler\Token;

class T_VARIABLE {


    static public function map( $tokens, $current, \Closure $parseToken ){

        $token = $tokens[$current];

        if (isset($tokens[$current + 1])){

            $nextToken = $tokens[$current + 1];

            if ($nextToken['type'] == Token::T_ASSIGN){

                $node = [
                    'type' => $nextToken['type'],
                    'value' => $token['value'],
                    'body' => [],
                ];

                $current++;
                $current++;
                while ($current < count($tokens)) {
                    $token = $tokens[$current];

                    if ($token['type'] == Token::T_LINEEND){
                        return [
                            $current, $node
                        ];
                    }else{
                        list($current, $param) = $parseToken($tokens, $current);
                        $node['body'][] = $param;

                    }
                }

                return [
                    $current, $node
                ];
            }
        }

        return [
            $current + 1, $token
        ];
    }
}