<?php
namespace App\Service\Compiler\Parser;

use App\Bytecode\Helper;
use App\Service\Compiler\Evaluate;
use App\Service\Compiler\EvaluateAssign;
use App\Service\Compiler\Token;

class T_DEFINE_SECTION_TYPE {


    static public function map( $tokens, $current, \Closure $parseToken ){

        $token = $tokens[$current];
        $current++;

        $node = [
            'type' => $token['type'],
            'value' => $token['value'],
            'body' => []
        ];

        while ($current < count($tokens)) {

            $token = $tokens[$current];

            if (
                $token['type'] == Token::T_IS_EQUAL ||
                $token['type'] == Token::T_BRACKET_OPEN
            ){
                $current++;
                continue;
            }

            if (
                $token['type'] == Token::T_BRACKET_CLOSE
            ){
                return [++$current, $node];

            }else{
                $node['body'][] = $token;
            }

            $current++;
        }


        return [++$current, $node];
    }
}