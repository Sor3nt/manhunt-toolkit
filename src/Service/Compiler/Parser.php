<?php
namespace App\Service\Compiler;

class Parser {

    private $types = [
        'T_CASE' => Parser\T_CASE::class,
        'T_VARIABLE' => Parser\T_VARIABLE::class,
        'T_FUNCTION' => Parser\T_FUNCTION::class,
        'T_DEFINE_SECTION_TYPE' => Parser\T_DEFINE_SECTION_TYPE::class,
        'T_DEFINE_SECTION_ENTITY' => Parser\T_DEFINE_SECTION_ENTITY::class,
        'T_DEFINE_SECTION_CONST' => Parser\T_DEFINE_SECTION_CONST::class,
        'T_DEFINE_SECTION_VAR' => Parser\T_DEFINE_SECTION_VAR::class,
        'T_BRACKET_OPEN' => Parser\T_BRACKET_OPEN::class,
        'T_PROCEDURE' => Parser\T_PROCEDURE::class,
        'T_SCRIPT' => Parser\T_SCRIPT::class
    ];

    /**
     * @param $tokens
     * @return array
     */
    public function toAST( $tokens ){

        $current = 0;
        $ast = [
            'type' => 'root',
            'body' => [],
        ];

        $node = null;

        while ($current < count($tokens)) {

            list($current, $node) = $this->parseToken($tokens, $current);

            if ($node !== false){
                $ast['body'][] = $node;
            }
        }

        return $ast;
    }

    public function handleForward( $ast ){

        foreach ($ast['body'] as &$token) {
            if ($token['type'] == Token::T_FORWARD){

                foreach ($ast['body'] as $index => $tokenInner) {
                    if (
                        isset($token['section']) &&
                        $tokenInner['type'] == $token['section'] &&
                        $tokenInner['value'] == $token['to']
                    ){

                        $token = $tokenInner;

                        unset($ast['body'][$index]);
                        $ast['body'] = array_values($ast['body']);
                    }
                }
            }
        }

        return $ast;
    }

    private function parseToken($tokens, $current) {

        if (!isset($tokens[$current])) return [$current, false];

        $token = $tokens[$current];

        switch ($token['type']){

            /**********************************
             *
             *
             * Ignore this tokens, we do not need them
             *
             *
             *********************************/
            case Token::T_DEFINE :
            case Token::T_DO :
            case Token::T_LINEEND :
            case Token::T_BEGIN :
            case Token::T_SCRIPTMAIN:
            case Token::T_SCRIPTMAIN_NAME:
            case Token::T_IF_END:
            case Token::T_WHILE_END:
            case Token::T_CASE_END:
            case Token::T_SCRIPT_END:
            case Token::T_PROCEDURE_END:
            case Token::T_END_CODE:
                //just go to the next position
                return $this->parseToken($tokens, $current + 1);


            /**********************************
             *
             *
             * simple types, just return the token
             *
             *
             *********************************/
            case Token::T_NIL :
            case Token::T_TRUE :
            case Token::T_IS_EQUAL :
            case Token::T_IS_NOT_EQUAL :
            case Token::T_IS_SMALLER :
            case Token::T_IS_GREATER :
            case Token::T_FALSE :
            case Token::T_STRING:
            case Token::T_INT:
            case Token::T_FLOAT:
            case Token::T_TYPE_VAR:
            case Token::T_SELF:
            case Token::T_NOT:
            case Token::T_FORWARD:
            case Token::T_ADDITION:
            case Token::T_SUBSTRACTION:
            case Token::T_OR:
            case Token::T_AND:
            case Token::T_OF:
                return [
                    $current + 1, $tokens[$current]
                ];

            /**********************************
             *
             *
             * some complex types
             *
             *
             *********************************/

            case Token::T_IF:
            case Token::T_WHILE:
                list($current, $nodes) = $this->parseIfStatement($tokens, $current);

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

                        list($innerCurrent, $tree)= $this->parseToken($innerTokens,$innerCurrent);

                        if (
                            $tree['type'] == Token::T_AND ||
                            $tree['type'] == Token::T_OR
                        ){
                            continue;
                        }


                        $tree = [$tree];
                        $this->remapCondition( $tree, $isNot );
                        $this->extendConditionInformation( $tree );

                        $parsedConditions[] = current($tree);
                    }

                    $case['condition'] = $parsedConditions;

                    $parsedIsTrue = [];
                    $innerCurrent = 0;
                    $innerTokens = $case['isTrue'];

                    while($innerCurrent < count($innerTokens)){
                        list($innerCurrent, $tree)= $this->parseToken($innerTokens, $innerCurrent);

                        if ($tree) $parsedIsTrue[] = $tree;

                    }

                    $case['isTrue'] = $parsedIsTrue;
                }

                return [$current, $nodes];

            default:

                if (isset($this->types[$token['type']])){
                    return (new $this->types[$token['type']]())->map($tokens, $current, function($tokens, $current){
                        return $this->parseToken($tokens, $current);
                    });

                }else{
                    throw new \Exception(sprintf('Parser: unable to handle %s', $token['type']));
                }

        }
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
    private function remapCondition( &$tokens, $isOuterNot = false ){

        foreach ($tokens as $current => $token) {

            // this can happend because of the unset calls
            if (!isset($tokens[ $current ])) continue;


            if ($tokens[ $current ]['type'] == Token::T_BRACKET_OPEN) {
                $this->remapCondition( $tokens[ $current ]['params'], $isOuterNot);
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
                        $innerToken['type'] == Token::T_IS_SMALLER
                    ){
                        $opertation = [
                            'type' => Token::T_OPERATION,
                            'operator' => $innerToken,
                            'operation' => [ 'type' => 'default' ],
                            'params' => [
                                $innerTokens[ $innerCurrent - 1 ],
                                $innerTokens[ $innerCurrent  + 1]
                            ]
                        ];

                        unset($innerTokens[ $innerCurrent - 1]);
                        unset($innerTokens[ $innerCurrent]);
                        unset($innerTokens[ $innerCurrent  + 1]);
                    }

                    $innerCurrent++;
                }

                if ($opertation == false){

                    var_dump("operator not found", $innerTokens);
                    exit;
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

    private function extendConditionInformation( &$tokens ){

        foreach ($tokens as $current => &$token) {

            if (isset($tokens[ $current ]['params'])) {
                $this->extendConditionInformation( $tokens[ $current ]['params']);
            }

            if ($token['type'] == Token::T_BRACKET_OPEN){

                if ($current + 1 == count($tokens)){
                    $token['last'] = true;
                }
            }
        }
    }

    private function parseIfLastElse( $tokens, $current  ){

        $case = [
            'condition' => [],
            'isTrue'=> []
        ];

        while ($current < count($tokens)) {
            $token = $tokens[$current];

            if ($token['type'] == Token::T_IF_END) {
                return [$current, $case] ;
            }else {
                $case[ 'isTrue' ][] = $token;
            }

            $current++;
        }

        throw new \Exception('Parser: parseIfLastElse not handeld correct');
    }

    private function parseIfStatement( $tokens, $current ){

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
                        list($current, $innerIf) = $this->parseIfStatement(
                            $tokens, $current + 2
                        );

                        foreach ($innerIf['cases'] as $case) {
                            $node['cases'][] = $case;
                        }

                        // the else statment (without if)
                    }else{

                        list($current, $innerIf) =  $this->parseIfLastElse(
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




}