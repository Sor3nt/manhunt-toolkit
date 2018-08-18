<?php
namespace App\Service\Compiler\Parser;

use App\Bytecode\Helper;
use App\Service\Compiler\Evaluate;
use App\Service\Compiler\EvaluateAssign;
use App\Service\Compiler\Token;

class T_SCRIPT {


    static public function map( $tokens, $current, \Closure $parseToken ){
        $token = $tokens[$current];

        $node = [
            'type' => $token['type'],
            'value' => false,
            'body' => [],
        ];

        $current++;

        while ($current < count($tokens)) {
//var_dump($tokens[$current]);
            switch ($tokens[$current]['type']){

                case Token::T_PROCEDURE_NAME:
                case Token::T_SCRIPT_NAME:
                    $node['value'] = $tokens[$current]['value'];
                    $current++;
                    continue;
                    break;

                case Token::T_LINEEND:
                case Token::T_BEGIN:
                    $current++;
                    continue;
                    break;

                case Token::T_PROCEDURE_END:
                case Token::T_SCRIPT_END:
                    return [
                        $current, $node
                    ];
                default:

                    list($current, $token) = $parseToken($tokens, $current);

                    if ($token !== false){
                        $node['body'][] = $token;
                    }
                    break;
            }

        }

        throw new \Exception('Parser: parseScript not handeld correct');
    }
}