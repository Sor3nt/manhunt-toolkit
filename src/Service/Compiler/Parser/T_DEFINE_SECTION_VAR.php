<?php
namespace App\Service\Compiler\Parser;

use App\Bytecode\Helper;
use App\Service\Compiler\Evaluate;
use App\Service\Compiler\EvaluateAssign;
use App\Service\Compiler\Token;

class T_DEFINE_SECTION_VAR {


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
                $token['type'] == Token::T_DEFINE_SECTION_TYPE ||
                $token['type'] == Token::T_DEFINE_SECTION_ENTITY ||
                $token['type'] == Token::T_PROCEDURE ||
                $token['type'] == Token::T_SCRIPT ||
                $token['type'] == Token::T_CUSTOM_FUNCTION ||
                $token['type'] == Token::T_BEGIN
            ){
                return [$current, $node];

            }else{

                if ($token['type'] !== Token::T_DEFINE && $token['type'] !== Token::T_LINEEND) {
                    $node['body'][] = $token;
                }
            }

            $current++;
        }

        return [++$current, $node];
    }
}