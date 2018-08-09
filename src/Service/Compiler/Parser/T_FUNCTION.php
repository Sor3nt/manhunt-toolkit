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

        while ($current < count($tokens)) {

            $token = $tokens[$current];

            if ($token['type'] === Token::T_BRACKET_CLOSE) {
                return [$current + 1 , $node];
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