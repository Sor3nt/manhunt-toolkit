<?php
namespace App\Service\Compiler;


use Symfony\Component\HttpKernel\EventListener\ValidateRequestListener;

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

        if (!isset($tokens[$current])) return [$current, false];

        $token = $tokens[$current];

echo "Next Token is : " . $token['type'] . "\n";
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
            case Token::T_SCRIPT_NAME:
            case Token::T_PROCEDURE_NAME:
//            case Token::T_BRACKET_CLOSE :
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
            case Token::T_END:
            case Token::T_PROCEDURE_END:
            case Token::T_END_CODE:
            case Token::T_STRING:
            case Token::T_INT:
            case Token::T_FLOAT:
            case Token::T_TYPE_VAR:
            case Token::T_SELF:
            case Token::T_OR:
            case Token::T_AND:
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

            case Token::T_BRACKET_OPEN :
                return $this->parseBracketOpen($tokens, $current);

            case Token::T_IF:
                list($current, $nodes) = $this->parseIfStatement($tokens, $current);


                foreach ($nodes['cases'] as &$case) {

                    //wrap if statements always in brackets
                    if (
                        count($case['condition']) &&
                        $case['condition'][0]['type'] !== Token::T_BRACKET_OPEN
                    ){

                        array_unshift($case['condition'], [
                            'type' => Token::T_BRACKET_OPEN,
                            'value' => "(",
                        ]);

                        array_push($case['condition'], [
                            'type' => Token::T_BRACKET_CLOSE,
                            'value' => ")",
                        ]);
                    }

                    /**
                     * convert the flat resultset into a tree
                     */
                    list($innerCurrent, $conditionTree)= $this->parseToken($case['condition'], 0);
                    list($innerCurrent, $isTrueTree)= $this->parseToken($case['isTrue'], 0);
                    $case['condition'] = $conditionTree;
                    $case['isTrue'] = $isTrueTree;
                }

                return [$current, $nodes];

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

        if (isset($tokens[$current + 1])){

            $nextToken = $tokens[$current + 1];

            if ($nextToken['type'] == Token::T_ASSIGN){

                $node = [
                    'type' => $nextToken['type'],
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

                throw new \Exception('Parser: parseVariable not handeld correct');
            }
        }

        return [
            $current + 1, $token
        ];
    }

    private function parseIfLastElse( $tokens, $current  ){

        $case = [
            'condition' => [],
            'isTrue'=> []
        ];

        while ($current < count($tokens)) {
            $token = $tokens[$current];

            if ($token['type'] == Token::T_END) {
                return [$current + 1, $case] ;
            }else {

                $case[ 'isTrue' ][] = $token;

            }

            $current++;

        }
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
        $section = "condition";

        while ($current < count($tokens)) {

            $token = $tokens[$current];
            if ($token['type'] == Token::T_THEN) {
                $section = "isTrue";
                $current++; //skip T_BEGIN (single line noch verbauen)

            // we have another If-statement
            }else if ($token['type'] == Token::T_END_ELSE) {

                $node['cases'][] = $case;


                if ($tokens[$current + 2]['type'] == Token::T_IF) {
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


                break;

            }else if ($token['type'] == Token::T_END) {
                $node['cases'][] = $case;
                break;
            }else {

                $case[ $section ][] = $token;

            }



            $current++;
        }





        return [$current + 1, $node];
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
                return [++$current, $node];

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

                list($current, $param) = $this->parseToken($tokens, $current);

                if ($token['type'] == Token::T_FUNCTION){
                    $param['nested'] = true;
                }

                $node['params'][] = $param;

            }
        }

        throw new \Exception('Parser: parseFunction unable to handle');
    }

    private function parseBracketOpen($tokens, $current){

        $token = $tokens[$current];

        $current++;

        $node = [
            'type' => $token['type'],
            'value' => $token['value'],
            'nested' => isset($token['nested']) ? $token['nested'] : false,
            'params' => []
        ];

        if (count($tokens) == $current + 1) return [$current, $node];
        while ($current < count($tokens)) {

            $token = $tokens[$current];

            if ($token['type'] === Token::T_BRACKET_CLOSE) {
                return [$current + 1 , $node];
            }else{

                list($current, $param) = $this->parseToken($tokens, $current);

                if ($token['type'] == Token::T_BRACKET_OPEN){
                    $param['nested'] = true;
                }

                $node['params'][] = $param;

            }
        }



        throw new \Exception('Parser: parseBracketOpen unable to handle');
    }

//    private function parseFunction($tokens, $current){
//
//        $token = $tokens[$current];
//
//        $current++;
//
//        $node = [
//            'type' => $token['type'],
//            'value' => $token['value'],
//            'nested' => isset($token['nested']) ? $token['nested'] : false,
//            'params' => []
//        ];
//
//        if (count($tokens) == $current + 1) return [$current, $node];
//
//        $token = $tokens[++$current];
//
//
//        //todo: hmm stimmt das, bracket closed könnte doch zu früh sein wenn es verschachtelt ist...
//        while ($token['type'] !== Token::T_BRACKET_CLOSE && $token['type'] !== Token::T_END) {
//
//            list($current, $param) = $this->parseToken($tokens, $current);
//
//            if ($token['type'] == Token::T_FUNCTION){
//                $param['nested'] = true;
//            }
//
//            if ($param !== false) {
//                $node['params'][] = $param;
//            }
//
//            if (count($tokens) == $current) return [$current, $node];
//
//            $token = $tokens[$current];
//        }
//
//        if ($tokens[$current]['type'] !== Token::T_END){
//            $current++;
//        }
//
//        return [$current, $node];
//    }


}