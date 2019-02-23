<?php
namespace App\Service\Compiler\Parser;

use App\Bytecode\Helper;
use App\Service\Compiler\Evaluate;
use App\Service\Compiler\EvaluateAssign;
use App\Service\Compiler\Token;

class T_FUNCTION {


    static public function map( $tokens, $current, \Closure $parseToken ){

        $token = $tokens[$current];

        $current++;

        $node = [
            'type' => $token['type'],
            'value' => $token['value'],
            'nested' => isset($token['nested']) ? $token['nested'] : false,
            'params' => []
        ];

        if (count($tokens) == $current + 1) return [$current, $node];
        if (!isset($tokens[$current])) return [$current, $node];
        $token = $tokens[$current];

        if ($token['type'] != Token::T_BRACKET_OPEN){
            return [$current, $node];
        }

        $current++;

        $deep = 0;
        while ($current < count($tokens)) {

            $token = $tokens[$current];

            if ($deep == 0 &&$token['type'] === Token::T_BRACKET_CLOSE) {

                $current++;

                if (isset($tokens[$current]) && $tokens[$current]['type'] == Token::T_DEFINE){

                    $node['arguments'] = [];

                    $current++;
                    foreach (explode(",", $tokens[$current]['value']) as $variable) {
                        $variable = trim($variable);

                        $node['arguments'][] = [
                            'type' => Token::T_VARIABLE,
                            'value' => $variable
                        ];
                    }
                    $current++;
                }


                return [$current, $node];
            }else if ($token['type'] === Token::T_BRACKET_OPEN) {
                $deep++;
                $current++;
            }else if ($deep > 0 && $token['type'] === Token::T_BRACKET_CLOSE) {
                $deep--;
                $current++;
            }else{

                list($current, $param) = $parseToken($tokens, $current);

                if ($token['type'] == Token::T_FUNCTION){
                    $param['nested'] = true;
                }

                $node['params'][] = $param;
            }
        }

        throw new \Exception('Parser: parseFunction unable to handle');
    }
}