<?php
namespace App\Service\Compiler\Parser;

use App\Bytecode\Helper;
use App\Service\Compiler\Evaluate;
use App\Service\Compiler\EvaluateAssign;
use App\Service\Compiler\Token;

class T_FOR {


    static public function map( $tokens, $current, \Closure $parseToken ){

        $current++;

        $node = [
            'type' => Token::T_FOR
        ];

        if ($tokens[$current]['type'] == Token::T_VARIABLE){

            $node['variable'] = $tokens[$current];

            $current++;

        }else{
            throw new \Exception('T_FOR: Unable to handle type 1');
        }



        if ($tokens[$current]['type'] == Token::T_ASSIGN){
            $current++;
        }else{
            throw new \Exception('T_FOR: Unable to handle type 2');
        }

        if ($tokens[$current]['type'] == Token::T_INT){
            $node['start'] = $tokens[$current];
            $current++;
        }else{
            throw new \Exception('T_FOR: Unable to handle type 3');
        }

        //skip T_TO
        $current++;

        if ($tokens[$current]['type'] == Token::T_INT) {
            $node['end'] = $tokens[$current];
            $current++;
        }else if ($tokens[$current]['type'] == Token::T_VARIABLE) {
            $node['end'] = $tokens[$current];
            $current++;
        }else if ($tokens[$current]['type'] == Token::T_FUNCTION){
            list($current, $functionTree)= $parseToken($tokens, $current);
            $node['end'] = $functionTree;
        }else{
            var_dump($tokens[$current]['type']);
            throw new \Exception('T_FOR: Unable to handle type 4');
        }

        //skip T_DO
        $current++;

        //skip T_BEGIN
        $current++;

        $deep = 0;

        while ($current < count($tokens)) {
            $token = $tokens[$current];

            if ($token['type'] == Token::T_FOR) {
                $deep++;

            }else if (
                $token['type'] == Token::T_FOR_END
            ) {

                if ($deep == 0){

                    $parsedIsTrue = [];
                    $innerCurrent = 0;
                    $innerTokens = $node['params'];

                    while($innerCurrent < count($innerTokens)){
                        list($innerCurrent, $tree)= $parseToken($innerTokens, $innerCurrent);

                        if ($tree) $parsedIsTrue[] = $tree;

                    }

                    $node['params'] = $parsedIsTrue;

                    return [$current + 1, $node];
                }

                $deep--;
            }

            $node['params'][] = $token;

            $current++;
        }

        throw new \Exception('Parser: map unable to handle');
    }

    static public function parseIfStatement( $tokens, $current, \Closure $parseToken ){


        $token = $tokens[$current];

        $node = [
            'type' => $token['type'],
            'value' => $token['value'],

            'cases' => []
        ];

        $case = [
            'condition' => [],
            'isTrue'=> []
        ];

        $current++;

        $shortStatement = true;

        /**
         * parse the condition
         */
        while ($current < count($tokens)) {
            $token = $tokens[$current];

            if ($token['type'] == Token::T_THEN || $token['type'] == Token::T_DO) {
                $current++;

                if ($tokens[$current]['type'] == Token::T_BEGIN) {
                    $shortStatement = false;
                    $current++;
                }

                break;
            }else{
                $case['condition'][] = $token;
            }

            $current++;
        }

        /**
         * parse SHORT true code
         */
        if ($shortStatement){
            while ($current < count($tokens)) {
                $token = $tokens[$current];

                if ($token['type'] == Token::T_LINEEND) {
                    $node['cases'][] = $case;

                    return [$current + 1, $node];
                }

                $case['isTrue'][] = $token;

                $current++;
            }

            /**
             * parse regular true code
             */
        }else{

            $deep = 0;

            while ($current < count($tokens)) {
                $token = $tokens[$current];

                if ($token['type'] == Token::T_THEN || $token['type'] == Token::T_DO) {

                    if ($tokens[$current + 1]['type'] == Token::T_BEGIN) {
                        $deep++;
                    }

                    // we have another If-statement
                }else if ($deep == 0 && $token['type'] == Token::T_END_ELSE) {

                    $node['cases'][] = $case;


                    if ($tokens[$current + 2]['type'] == Token::T_IF || $tokens[$current + 2]['type'] == Token::T_WHILE) {
                        list($current, $innerIf) = self::parseIfStatement(
                            $tokens, $current + 2, $parseToken
                        );

                        foreach ($innerIf['cases'] as $case) {
                            $node['cases'][] = $case;
                        }

                        // the else statment (without if)
                    }else{

                        list($current, $innerIf) =  self::parseIfLastElse(
                            $tokens, $current + 3
                        );

                        $node['cases'][] = $innerIf;
                    }

                    return [$current + 1, $node];

                    break;

                }else if (
                    $token['type'] == Token::T_IF_END ||
                    $token['type'] == Token::T_WHILE_END
                ) {

                    if ($deep == 0){
                        $node['cases'][] = $case;

                        return [$current + 1, $node];
                    }

                    $deep--;
                }

                $case['isTrue'][] = $token;

                $current++;
            }

        }

        throw new \Exception('Parser: parseIfStatement unable to handle');
    }


    static function parseIfLastElse( $tokens, $current  ){

        $case = [
            'condition' => [],
            'isTrue'=> []
        ];

        $deep = 0;

        while ($current < count($tokens)) {
            $token = $tokens[$current];

            if ($deep == 0 && $token['type'] == Token::T_IF_END) {
                return [$current, $case] ;
            }else if (
                $token['type'] == Token::T_BEGIN ||
                $token['type'] == Token::T_DO ||
                $token['type'] == Token::T_OF
            ){
                $deep++;
            }else if (
                $token['type'] == Token::T_CASE_END ||
                $token['type'] == Token::T_END_ELSE ||
                $token['type'] == Token::T_IF_END ||
                $token['type'] == Token::T_CASE_END
            ){
                $deep--;
            }


            $case[ 'isTrue' ][] = $token;

            $current++;
        }

        throw new \Exception('Parser: parseIfLastElse not handeld correct');
    }



    /**
     * remap / regroup statements
     *
     * input : T_VARIABLE T_IS_EQUAL T_INT
     * output : T_IS_EQUAL[T_VARIABLE] = T_INT
     *
     * @param $tokens
     * @throws \Exception
     */
    static function remapCondition( &$tokens, $isOuterNot = false ){

        foreach ($tokens as $current => $token) {

            // this can happend because of the unset calls
            if (!isset($tokens[ $current ])) continue;


            if ($tokens[ $current ]['type'] == Token::T_BRACKET_OPEN) {
                self::remapCondition( $tokens[ $current ]['params'], $isOuterNot);
                continue;
            }

            $isNot = false;
            if ($tokens[ $current ]['type'] == Token::T_NOT) {
                $isNot = true;
                unset($tokens[ $current ]);

                $tokens = array_values($tokens);
            }

            $node = [
                'type' => Token::T_CONDITION,
                'isNot' => $isNot,
                'isOuterNot' => $isOuterNot,
                'body' => [],
            ];

            if (count($tokens) == 1){
                $opertation = [
                    'type' => Token::T_OPERATION,
                    'operation' => [ 'type' => 'default' ],
                    'params' => [
                        $tokens[0]
                    ]
                ];

            }else{


                $opertation = false;

                /**
                 *
                 * convert any condition into operation tokens
                 */
                $tokens = $tokens;
                $current = 0;
                $innerTokenCount = count($tokens);
                while($current < $innerTokenCount){
                    if (!isset($tokens[ $current ])){
                        $current++;
                        continue;
                    }

                    $innerToken = $tokens[ $current ];

                    if (
                        $innerToken['type'] == Token::T_IS_EQUAL ||
                        $innerToken['type'] == Token::T_IS_NOT_EQUAL ||
                        $innerToken['type'] == Token::T_IS_GREATER ||
                        $innerToken['type'] == Token::T_IS_SMALLER_EQUAL ||
                        $innerToken['type'] == Token::T_IS_SMALLER
                    ){
                        $opertation = [
                            'type' => Token::T_OPERATION,
                            'operator' => $innerToken,
                            'operation' => [ 'type' => 'default' ],
                            'params' => [
                                $tokens[ $current - 1 ],
                                $tokens[ $current  + 1]
                            ]
                        ];

                        unset($tokens[ $current - 1]);
                        unset($tokens[ $current]);
                        unset($tokens[ $current  + 1]);
                    }

                    $current++;
                }

                if ($opertation == false){

                    throw new \Exception('T_FOR: operator not found');
                }
                $tokens = array_values($tokens);
                $current = 0;
                $innerTokenCount = count($tokens);


                while($current < $innerTokenCount){
                    if (!isset($tokens[ $current ])){
                        $current++;
                        continue;
                    }

                    $innerToken = $tokens[ $current ];


                    if (
                        $innerToken['type'] == Token::T_AND ||
                        $innerToken['type'] == Token::T_OR
                    ){
                        $opertation['operation'] = [ 'type' => $innerToken['type'] ];
                        $opertation['params'][] = $tokens[ $current  + 1];

                        unset($tokens[ $current ]);
                        unset($tokens[ $current  + 1]);

                    }

                    $current++;

                }
            }

            if ($opertation){
                $node['body'][] = $opertation;
            }

            //remove all old token entries
            foreach ($tokens as $index => $token2) {
                unset($tokens[$index]);
            }

            $tokens[] = $node;
            $tokens = array_values($tokens);
        }
    }

    static function extendConditionInformation( &$tokens ){

        foreach ($tokens as $current => &$token) {

            if (isset($tokens[ $current ]['params'])) {
                self::extendConditionInformation( $tokens[ $current ]['params']);
            }

            if ($token['type'] == Token::T_BRACKET_OPEN){

                if ($current + 1 == count($tokens)){
                    $token['last'] = true;
                }
            }
        }
    }


}