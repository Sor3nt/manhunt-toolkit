<?php
namespace App\Service\Compiler;


class Parser {
    /**
     * @param $tokens
     * @return array
     */
    public function toAST( $tokens ){

        echo "################\n";
        echo "Parse Tokens\n";


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

    private function parseToken($tokens, $current) {

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
            case Token::T_BRACKET_OPEN :
            case Token::T_BRACKET_CLOSE :
            case Token::T_DO :
            case Token::T_LINEEND :
            case Token::T_BEGIN :
            case Token::T_SCRIPTMAIN:
            case Token::T_SCRIPTMAIN_NAME:
            case Token::T_SCRIPT_NAME:
            case Token::T_PROCEDURE_NAME:
                 //just go to the next position
                return $this->parseToken($tokens, $current + 1);


            /**********************************
             *
             *
             * simple types, just return the token
             *
             *
             *********************************/
            case Token::T_TRUE :
            case Token::T_FALSE :
            case Token::T_END:
            case Token::T_PROCEDURE_END:
            case Token::T_END_CODE:
            case Token::T_STRING:
            case Token::T_INT:
            case Token::T_FLOAT:
            case Token::T_TYPE_VAR:
            case Token::T_SELF:
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
                return $this->parseIfStatement($tokens, $current);

            case Token::T_DEFINE_SECTION_VAR:
                return $this->parseDefineVarRecursive($tokens, $current);

            case Token::T_DEFINE_SECTION_ENTITY:
                return $this->parseDefineEntityRecursive($tokens, $current);

            case Token::T_DEFINE_SECTION_TYPE:
                return $this->parseDefineTypeRecursive($tokens, $current);

            case Token::T_FUNCTION :
                return $this->parseFunction($tokens, $current);

            /**
             * A variable can be used or assigned
             * Return T_ASSIGN when its a define otherwise just the token
             */
            case Token::T_VARIABLE :
                return $this->parseVariable($tokens, $current);


            /**********************************
             *
             *
             * per default any other types are nested
             *
             *
             *********************************/
            default:
                return $this->parseRecursive($tokens, $current);

        }

    }

    private function parseRecursive($tokens, $current){
        $token = $tokens[$current];

        $node = [
            'type' => $token['type'],
            'value' => $token['value'],
            'body' => [],
        ];

        $current++;

        while ($current < count($tokens)) {

            list($current, $token) = $this->parseToken($tokens, $current);

            if ($token !== false){
                $node['body'][] = $token;
            }
        }


        return [
            $current, $node
        ];
    }


    /**
     * @param $tokens
     * @param $current
     * @return array
     */
    private function parseVariable($tokens, $current ){

        $token = $tokens[$current];
        $nextToken = $tokens[$current + 1];

        if ($nextToken['type'] == Token::T_ASSIGN){
            $node = [
                'type' => Token::T_ASSIGN,
                'value' => $token['value'],
                'body' => [],
            ];

            $current++;
            $current++;

            while ($current < count($tokens)) {
                $token = $tokens[$current];

                if ($token['type'] == Token::T_LINEEND || $token['type'] == Token::T_END){
                    return [
                        $current, $node
                    ];
                }else{
                    list($current, $param) = $this->parseToken($tokens, $current);
                    $node['body'][] = $param;

                }
            }

            return [
                $current, $node
            ];
        }

        return [
            $current + 1, $token
        ];
    }

    private function parseIfStatement( $tokens, $current ){

        $token = $tokens[$current];

        $node = [
            'type' => $token['type'],
            'value' => $token['value'],
            'condition' => [],
            'isTrue' => [],
            'isFalse' => [],
        ];

        $current++;
        $section = "condition";

        while ($current < count($tokens)) {

            $token = $tokens[$current];

            if ($token['type'] != Token::T_THEN) {
                $node[$section][] = $token;
            }

            if ($token['type'] == Token::T_THEN) {

                // normal if with BEGIN and END block
                if ($tokens[$current + 1]['type'] == Token::T_BEGIN){
                    while ($current < count($tokens)) {

                        $current++;
                        $nextToken = $tokens[$current];
                        if ($nextToken['type'] == Token::T_END){
                            return [$current + 1 , $node];

                        }else{
                            list($current, $innerNode) = $this->parseToken($tokens, $current);
                            $node['isTrue'][] = $innerNode;

                        }


                    }


                // single line if without BEGIN and END;
                }else{

                    list($current, $innerNode) = $this->parseToken($tokens, $current + 1);

                    $node['isTrue'][] = $innerNode;

                }

                return [$current, $node];
            }

            $current++;
        }

        return [++$current, $node];
    }

    private function parseDefineVarRecursive( $tokens, $current ){

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
                $token['type'] == Token::T_BEGIN
            ){
                return [$current, $node];

            }else{

//                list($current, $param) = $this->parseToken($tokens, $current);

                if ($token['type'] !== Token::T_DEFINE && $token['type'] !== Token::T_LINEEND) {
                    $node['body'][] = $token;
                }
            }
            $current++;
        }

        return [++$current, $node];
    }

    private function parseDefineEntityRecursive( $tokens, $current ){

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
                $token['type'] == Token::T_DEFINE_SECTION_VAR ||
                $token['type'] == Token::T_PROCEDURE ||
                $token['type'] == Token::T_SCRIPT ||
                $token['type'] == Token::T_BEGIN
            ){
                return [$current, $node];

            }else{

//                list($current, $param) = $this->parseToken($tokens, $current);

                if ($token['type'] !== Token::T_DEFINE && $token['type'] !== Token::T_LINEEND) {
                    $node['body'][] = $token;
                }
            }
            $current++;
        }

        return [++$current, $node];
    }

    private function parseDefineTypeRecursive( $tokens, $current ){

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
                return [$current, $node];

            }else{
                $node['body'][] = $token;
            }

            $current++;
        }


        return [++$current, $node];
    }

    /**
     * @param $tokens
     * @param $current
     * @return array
     */
    private function parseFunction($tokens, $current){

        $token = $tokens[$current];

        $current++;

        $node = [
            'type' => $token['type'],
            'value' => $token['value'],
            'nested' => isset($token['nested']) ? $token['nested'] : false,
            'params' => []
        ];

        if (count($tokens) == $current + 1) return [$current, $node];

        $token = $tokens[++$current];


        //todo: hmm stimmt das, bracket closed könnte doch zu früh sein wenn es verschachtelt ist...
        while ($token['type'] !== Token::T_BRACKET_CLOSE && $token['type'] !== Token::T_END) {

            list($current, $param) = $this->parseToken($tokens, $current);

            if ($token['type'] == Token::T_FUNCTION){
                $param['nested'] = true;
            }

            if ($param !== false) {
                $node['params'][] = $param;
            }

            if (count($tokens) == $current) return [$current, $node];
            $token = $tokens[$current];
        }

        if ($tokens[$current]['type'] !== Token::T_END){
            $current++;
        }

        return [$current, $node];
    }


}