<?php
namespace App\Service\Compiler\Parser;

use App\Service\Compiler\Token;

class T_IF {


    static public function map( $tokens, $current, \Closure $parseToken ){

        list($current, $nodes) =  self::parseIfStatement($tokens, $current, $parseToken);
        // Todo: wrap code into a function
        foreach ($nodes['cases'] as $index => &$case) {

            if (isset($nodes['cases'][ $index + 1])){

                if (count($nodes['cases'][ $index + 1]['condition']) == 0){
                    $case['next'] = Token::T_ELSE;
                }else{
                    $case['next'] = Token::T_IF;
                }
            }


            if (count($case['condition'])){
                $firstNode = $case['condition'][0];
                $lastNode = end($case['condition']);

                $doWrap = false;
                if (
                !(
                    $firstNode['type'] == Token::T_NOT &&
                    $case['condition'][1]['type'] == Token::T_BRACKET_OPEN
                )

                ){

                    if (
                        $firstNode['type'] == Token::T_BRACKET_OPEN &&
                        $lastNode['type'] !== Token::T_BRACKET_CLOSE
                    ) {
                        $doWrap = true;
                    }else if (
                        $firstNode['type'] !== Token::T_BRACKET_OPEN &&
                        $lastNode['type'] !== Token::T_BRACKET_CLOSE
                    ){
                        $doWrap = true;
                    }else if (
                        $firstNode['type'] !== Token::T_BRACKET_OPEN &&
                        $lastNode['type'] == Token::T_BRACKET_CLOSE
                    ){
                        $doWrap = true;
                    }
                }

                //wrap if statements always in brackets
                if ($doWrap){

                    array_unshift($case['condition'], [
                        'type' => Token::T_BRACKET_OPEN
                    ]);

                    array_push($case['condition'], [
                        'type' => Token::T_BRACKET_CLOSE
                    ]);
                }

            }
            $parsedConditions = [];
            $innerCurrent = 0;
            $innerTokens = $case['condition'];

            while($innerCurrent < count($innerTokens)){

                $isNot = false;
                if($innerTokens[$innerCurrent]['type'] == Token::T_NOT){
                    $isNot = true;
                    $innerCurrent++;
                }

                list($innerCurrent, $tree)= $parseToken($innerTokens,$innerCurrent);

                if (
                    $tree['type'] == Token::T_AND ||
                    $tree['type'] == Token::T_OR
                ){
                    continue;
                }


                $tree = self::fixDoubleBracketOpen($tree);

                $tree = [$tree];

                self::remapCondition( $tree, $isNot );
                self::extendConditionInformation( $tree );

                $parsedConditions[] = current($tree);
            }

            $case['condition'] = $parsedConditions;

            $parsedIsTrue = [];
            $innerCurrent = 0;
            $innerTokens = $case['isTrue'];


            while($innerCurrent < count($innerTokens)){
                list($innerCurrent, $tree) = $parseToken($innerTokens, $innerCurrent);

                if ($tree) $parsedIsTrue[] = $tree;

            }

            $case['isTrue'] = $parsedIsTrue;
        }

        return [$current, $nodes];
    }

    static public function fixDoubleBracketOpen( $tokens ){
        if (
            count($tokens['params']) == 3 &&
            $tokens['params'][0]['type'] == Token::T_BRACKET_OPEN &&
            $tokens['params'][0]['operator'] == $tokens['operator'] &&
            $tokens['params'][0]['isNot'] == $tokens['isNot']
        ){
            $tokens['params'][0] = $tokens['params'][0]['params'][0];
        }

        return $tokens;
    }

    static public function parseIfStatement( $tokens, $current, \Closure $parseToken ){

        $token = $tokens[$current];
$a = $tokens;
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

                if ($token['type'] == Token::T_LINEEND || $token['type'] == Token::T_ELSE) {

                    $node['cases'][] = $case;

                    //some short codes did not end with lineend
                    if ($token['type'] == Token::T_ELSE){
                        $current--;
                    }

                    if (isset($tokens[$current + 1])){
                        if (
                            $tokens[$current + 1]['type'] == Token::T_ELSE &&
                            $tokens[$current + 2]['type'] == Token::T_IF
                        ) {

                            list($current, $innerIf) = self::parseIfStatement(
                                $tokens, $current + 2, $parseToken
                            );


                            foreach ($innerIf['cases'] as $case) {
                                $node['cases'][] = $case;
                            }

                            // +0 : just return given one
                            return [$current, $node];

                            //short else
                        }else if (
                            $tokens[$current + 1]['type'] == Token::T_ELSE
                        ) {

                            /**
                             * bad hack, i parse here the tokens to get the needed length....
                             */
                            $beforeCurrent = $current + 2;
                            list($current, ) =  $parseToken(
                                $tokens, $current + 2
                            );

                            $parsedTokens = $current - $beforeCurrent;

                            $node['cases'][] = [
                                'condition' => [],
                                'isTrue'=> array_slice($tokens, $beforeCurrent, $parsedTokens)
                            ];

                        }
                    }

                    // +1 : skip T_LINEEND
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

                if ($token['type'] == Token::T_THEN || $token['type'] == Token::T_DO || $token['type'] == Token::T_ELSE) {

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
                    }else if($tokens[$current + 2]['type'] == Token::T_BEGIN){
                        list($current, $innerIf) =  self::parseIfLastElse(
                            $tokens, $current + 3
                        );

                        $node['cases'][] = $innerIf;
                    }else{

                        /**
                         * bad hack, i parse here the tokens to get the needed length....
                         */
                        $beforeCurrent = $current + 2;
                        list($current, ) =  $parseToken(
                            $tokens, $current + 2
                        );

                        $parsedTokens = $current - $beforeCurrent;

                        $node['cases'][] = [
                            'condition' => [],
                            'isTrue'=> array_slice($tokens, $beforeCurrent, $parsedTokens)
                        ];
                    }

                    return [$current, $node];

                    break;

                }else if (
                    $token['type'] == Token::T_IF_END ||
                    $token['type'] == Token::T_END_ELSE ||
                    $token['type'] == Token::T_FOR_END ||
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

        throw new \Exception('Parser: parseIfStatement unable to handle (if)');
    }


    static function parseIfLastElse( $tokens, $current ){

        $case = [
            'condition' => [],
            'isTrue'=> []
        ];

        $deep = 0;

        while ($current < count($tokens)) {
            $token = $tokens[$current];

            if ($deep == 0 && $token['type'] == Token::T_IF_END) {
                return [$current + 1, $case] ;
            }else if (
                $token['type'] == Token::T_BEGIN ||
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
     * @param bool $isOuterNot
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
                $innerTokens = $tokens;
                $innerCurrent = 0;
                $innerTokenCount = count($innerTokens);
                while($innerCurrent < $innerTokenCount){
                    if (!isset($innerTokens[ $innerCurrent ])){
                        $innerCurrent++;
                        continue;
                    }

                    $innerToken = $innerTokens[ $innerCurrent ];

                    if (
                        $innerToken['type'] == Token::T_IS_EQUAL ||
                        $innerToken['type'] == Token::T_IS_NOT_EQUAL ||
                        $innerToken['type'] == Token::T_IS_GREATER ||
                        $innerToken['type'] == Token::T_IS_GREATER_EQUAL ||
                        $innerToken['type'] == Token::T_IS_SMALLER_EQUAL ||
                        $innerToken['type'] == Token::T_IS_SMALLER
                    ){


                        $opertation = [
                            'type' => Token::T_OPERATION,
                            'operator' => $innerToken,
                            'operation' => [ 'type' => 'default' ],
                            'params' => [
                                $innerTokens[ $innerCurrent - 1 ],
                                $innerTokens[ $innerCurrent + 1]
                            ]
                        ];

                        unset($innerTokens[ $innerCurrent - 1]);
                        unset($innerTokens[ $innerCurrent]);
                        unset($innerTokens[ $innerCurrent  + 1]);
                    }

                    $innerCurrent++;
                }

                if ($opertation == false){
//                    var_dump($tokens);
                    throw new \Exception('T_IF: operator not found');
                }
                $innerTokens = array_values($innerTokens);
                $innerCurrent = 0;
                $innerTokenCount = count($innerTokens);


                while($innerCurrent < $innerTokenCount){
                    if (!isset($innerTokens[ $innerCurrent ])){
                        $innerCurrent++;
                        continue;
                    }

                    $innerToken = $innerTokens[ $innerCurrent ];


                    if (
                        $innerToken['type'] == Token::T_AND ||
                        $innerToken['type'] == Token::T_OR
                    ){
                        $opertation['operation'] = [ 'type' => $innerToken['type'] ];
                        $opertation['params'][] = $innerTokens[ $innerCurrent  + 1];

                        unset($innerTokens[ $innerCurrent ]);
                        unset($innerTokens[ $innerCurrent  + 1]);

                    }

                    $innerCurrent++;
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