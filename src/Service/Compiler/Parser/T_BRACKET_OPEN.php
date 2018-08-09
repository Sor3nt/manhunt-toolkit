<?php
namespace App\Service\Compiler\Parser;

use App\Bytecode\Helper;
use App\Service\Compiler\Evaluate;
use App\Service\Compiler\EvaluateAssign;
use App\Service\Compiler\Token;

class T_BRACKET_OPEN {


    static public function map( $tokens, $current, \Closure $parseToken ){

        $token = $tokens[$current];

        $isNot = false;
        $operator = false;
        if (isset($tokens[$current - 1])){
            if ($tokens[$current - 1]['type'] == Token::T_AND) $operator = Token::T_AND;
            if ($tokens[$current - 1]['type'] == Token::T_OR) $operator = Token::T_OR;
            if ($tokens[$current - 1]['type'] == Token::T_NOT) $isNot = true;
        }

        $current++;

        $node = [
            'type' => $token['type'],
            'nested' => isset($token['nested']) ? $token['nested'] : false,
            'operator' => $operator,
            'isNot' => $isNot,
            'params' => []
        ];

        if (count($tokens) == $current + 1) return [$current, $node];

        while ($current < count($tokens)) {

            $token = $tokens[$current];

            if ($token['type'] === Token::T_BRACKET_CLOSE) {

                return [$current + 1 , $node];
            }else if (
                ($token['type'] === Token::T_AND || $token['type'] === Token::T_OR) &&
                $tokens[$current - 1]['type'] == Token::T_BRACKET_CLOSE
            ) {

                $current++;
                continue;
            }else{

                list($current, $param) = $parseToken($tokens, $current);
                if (
                    $token['type'] == Token::T_BRACKET_OPEN ||
                    isset(end($node['params'])['nested']) && end($node['params'])['nested'] == true

                ){
                    $param['nested'] = true;
                }

                $node['params'][] = $param;

            }
        }

        throw new \Exception('Parser: parseBracketOpen unable to handle');
    }
}