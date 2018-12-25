<?php
namespace App\Service\Compiler\Parser;

use App\Service\Compiler\Token;

class T_CASE {


    static public function map( $tokens, $current, \Closure $parseToken ){

        //skip T_CASE
        $current++;

        list($current, $switchBy) = $parseToken($tokens, $current);

        //skip T_OF
        $current++;

        $switch = [
            'type' => Token::T_SWITCH,
            'switch' => $switchBy,
            'cases' => []
        ];

        while ($current < count($tokens)) {
            if ($tokens[$current]['type'] == Token::T_SWITCH_END){

                return [
                    $current + 1, $switch
                ];

            }
            $case = [
                'index' => $tokens[$current],
                'body' => []
            ];


            $current++;

            //skip T_DEFINE
            $current++;

            $shortCase = true;
            if ($tokens[$current]['type'] == Token::T_BEGIN){
                $current++;
                $shortCase = false;
            }


            while ($current < count($tokens)) {
                if (
                    (
                        $shortCase &&
                        $tokens[$current]['type'] == Token::T_LINEEND
                    ) || (
                        $shortCase == false &&
                        $tokens[$current]['type'] == Token::T_CASE_END
                    )
                ) {

                    $current++;

                    $innerCurrent = 0;
                    $innerTokens = $case['body'];

                    $case['body'] = [];
                    while($innerCurrent < count($innerTokens)){

                        list($innerCurrent, $node) = $parseToken($innerTokens, $innerCurrent);

                        if ($node !== false){
                            $case['body'][] = $node;

                        }
                    }

                    $switch['cases'][] = $case;

                    break;
                }else{
                    $case['body'][] = $tokens[$current];
                }

                $current++;
            }
        }

        throw new \Exception('Parser: parseSwitchCase not handeld correct');
    }
}