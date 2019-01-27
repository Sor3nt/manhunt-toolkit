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
        'T_DEFINE_SECTION_ARG' => Parser\T_DEFINE_SECTION_ARG::class,
        'T_BRACKET_OPEN' => Parser\T_BRACKET_OPEN::class,
        'T_PROCEDURE' => Parser\T_PROCEDURE::class,
        'T_CUSTOM_FUNCTION' => Parser\T_CUSTOM_FUNCTION::class,
        'T_IF' => Parser\T_IF::class,
        'T_FOR' => Parser\T_FOR::class,
        'T_WHILE' => Parser\T_WHILE::class,
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
//var_dump($ast);
//        exit;
        return $ast;
    }

    public function getProcedures( $ast ){

        $result = [];


        foreach ($ast['body'] as $token) {
            if ($token['type'] == Token::T_PROCEDURE){

                $result[strtolower($token['value'])] = false;
            }
        }

        return $result;
    }


    public function getCustomFunctions( $ast ){

        $result = [];


        foreach ($ast['body'] as $token) {
            if ($token['type'] == Token::T_CUSTOM_FUNCTION){

                $result[strtolower($token['value'])] = false;
            }
        }

        return $result;
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

                        $tokenInner['vars'] = $token['params'];

                        if (isset($token['returnType'])){
                            $tokenInner['returnType'] = $token['returnType'];
                        }

                        if (isset($token['parameters'])){
                            $tokenInner['parameters'] = $token['parameters'];
                        }

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
            case Token::T_CUSTOM_FUNCTION_END:
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
            case Token::T_IS_EQUAL :
            case Token::T_IS_NOT_EQUAL :
            case Token::T_IS_GREATER_EQUAL :
            case Token::T_IS_SMALLER :
            case Token::T_IS_GREATER :
            case Token::T_STRING:
            case Token::T_INT:
            case Token::T_FLOAT:
            case Token::T_TYPE_VAR:
            case Token::T_SELF:
            case Token::T_NOT:
            case Token::T_FORWARD:
            case Token::T_ADDITION:
            case Token::T_SUBSTRACTION:
            case Token::T_MULTIPLY:
            case Token::T_OR:
            case Token::T_AND:
            case Token::T_SWITCH_END:
            case Token::T_OF:
//            case Token::T_BRACKET_CLOSE:
                return [
                    $current + 1, $tokens[$current]
                ];


            case Token::T_BOOLEAN:
                return [
                    $current + 1, [
                        'type' => Token::T_BOOLEAN,
                        'value' => $tokens[$current]['value'] == 'true'
                    ]
                ];

            /**********************************
             *
             *
             * some complex types
             *
             *
             *********************************/

            default:

                if (isset($this->types[$token['type']])){
                    return (new $this->types[$token['type']]())->map($tokens, $current, function($tokens, $current){
                        return $this->parseToken($tokens, $current);
                    });

                }else{
//                    var_dump($tokens[$current - 2], $tokens[$current - 1], $tokens[$current]);
//                    exit;
                    throw new \Exception(sprintf('Parser: unable to handle %s', $token['type']));
                }

        }
    }


}