<?php
namespace App\Service\Compiler;

use App\MHT;
use App\Service\Compiler\Tokens\T_ARRAY;
use App\Service\Compiler\Tokens\T_ARRAY_RANGE;
use App\Service\Compiler\Tokens\T_BOOLEAN;
use App\Service\Compiler\Tokens\T_CUSTOM_FUNCTION;
use App\Service\Compiler\Tokens\T_ADDITION;
use App\Service\Compiler\Tokens\T_AND;
use App\Service\Compiler\Tokens\T_ASSIGN;
use App\Service\Compiler\Tokens\T_BEGIN;
use App\Service\Compiler\Tokens\T_BRACKET_CLOSE;
use App\Service\Compiler\Tokens\T_BRACKET_OPEN;
use App\Service\Compiler\Tokens\T_CASE;
use App\Service\Compiler\Tokens\T_CUSTOM_FUNCTION_NAME;
use App\Service\Compiler\Tokens\T_DEFINE;
use App\Service\Compiler\Tokens\T_DEFINE_SECTION_ARG;
use App\Service\Compiler\Tokens\T_DEFINE_SECTION_CONST;
use App\Service\Compiler\Tokens\T_DEFINE_SECTION_ENTITY;
use App\Service\Compiler\Tokens\T_DEFINE_TYPE;
use App\Service\Compiler\Tokens\T_DEFINE_SECTION_VAR;
use App\Service\Compiler\Tokens\T_DEFINE_SECTION_TYPE;
use App\Service\Compiler\Tokens\T_DO;
use App\Service\Compiler\Tokens\T_END;
use App\Service\Compiler\Tokens\T_ELSE;
use App\Service\Compiler\Tokens\T_FLOAT;
use App\Service\Compiler\Tokens\T_FOR;
use App\Service\Compiler\Tokens\T_FORWARD;
use App\Service\Compiler\Tokens\T_FUNCTION;
use App\Service\Compiler\Tokens\T_IF;
use App\Service\Compiler\Tokens\T_INT;
use App\Service\Compiler\Tokens\T_IS_EQUAL;
use App\Service\Compiler\Tokens\T_IS_GREATER;
use App\Service\Compiler\Tokens\T_IS_GREATER_EQUAL;
use App\Service\Compiler\Tokens\T_IS_NOT_EQUAL;
use App\Service\Compiler\Tokens\T_IS_SMALLER;
use App\Service\Compiler\Tokens\T_LEVEL_VAR;
use App\Service\Compiler\Tokens\T_LINEEND;
use App\Service\Compiler\Tokens\T_MULTIPLY;
use App\Service\Compiler\Tokens\T_NIL;
use App\Service\Compiler\Tokens\T_NOT;
use App\Service\Compiler\Tokens\T_NULL;
use App\Service\Compiler\Tokens\T_OF;
use App\Service\Compiler\Tokens\T_OR;
use App\Service\Compiler\Tokens\T_PROCEDURE;
use App\Service\Compiler\Tokens\T_PROCEDURE_NAME;
use App\Service\Compiler\Tokens\T_RECORD;
use App\Service\Compiler\Tokens\T_SCRIPT;
use App\Service\Compiler\Tokens\T_SCRIPT_NAME;
use App\Service\Compiler\Tokens\T_SCRIPTMAIN;
use App\Service\Compiler\Tokens\T_SCRIPTMAIN_NAME;
use App\Service\Compiler\Tokens\T_SELF;
use App\Service\Compiler\Tokens\T_SEPERATOR;
use App\Service\Compiler\Tokens\T_SQUARE_BRACKET_CLOSE;
use App\Service\Compiler\Tokens\T_SQUARE_BRACKET_OPEN;
use App\Service\Compiler\Tokens\T_STRING;
use App\Service\Compiler\Tokens\T_SUBSTRACTION;
use App\Service\Compiler\Tokens\T_THEN;
use App\Service\Compiler\Tokens\T_TO;
use App\Service\Compiler\Tokens\T_VARIABLE;
use App\Service\Compiler\Tokens\T_WHILE;
use App\Service\Compiler\Tokens\T_WHITESPACE;

class Tokenizer {

    public $tokens = [
        /**
         *
         * DO NOT CHANGE THIS ORDERS, i warned you
         *
         */

        T_STRING::class,
        T_WHITESPACE::class,

        T_RECORD::class,
        T_CASE::class,
        T_OF::class,
        T_FOR::class,
        T_TO::class,
        T_FORWARD::class,
        T_LINEEND::class,
        T_ADDITION::class,
        T_SUBSTRACTION::class,
        T_MULTIPLY::class,
        T_LEVEL_VAR::class,
        T_DEFINE_TYPE::class,
        T_DEFINE_SECTION_TYPE::class,
        T_DEFINE_SECTION_ENTITY::class,
        T_DEFINE_SECTION_VAR::class,
        T_DEFINE_SECTION_ARG::class,
        T_DEFINE_SECTION_CONST::class,
        T_SCRIPTMAIN_NAME::class,

        T_SCRIPTMAIN::class,
        T_DEFINE::class,
        T_PROCEDURE::class,
        T_CUSTOM_FUNCTION::class,
        T_CUSTOM_FUNCTION_NAME::class,
        T_SCRIPT::class,
        T_SCRIPT_NAME::class,
        T_PROCEDURE_NAME::class,
        T_SELF::class,

        T_SQUARE_BRACKET_OPEN::class,
        T_SQUARE_BRACKET_CLOSE::class,
        T_BRACKET_OPEN::class,
        T_BRACKET_CLOSE::class,
        T_SEPERATOR::class,

        T_IF::class,
        T_OR::class,
        T_AND::class,
        T_NOT::class,
        T_NIL::class,
        T_ELSE::class,
        T_WHILE::class,
        T_THEN::class,
        T_BEGIN::class,
        T_END::class,
        T_DO::class,

        T_ARRAY::class,
        T_BOOLEAN::class,
        T_FLOAT::class,
        T_INT::class,
        T_NULL::class,

        T_ASSIGN::class,
        T_IS_NOT_EQUAL::class,
        T_IS_GREATER_EQUAL::class,
        T_IS_GREATER::class,
        T_IS_SMALLER::class,
        T_IS_EQUAL::class,


        T_FUNCTION::class,
        T_VARIABLE::class
    ];

    private $game;

    public function __construct( $game = MHT::GAME_MANHUNT_2 )
    {
        $this->game = $game;
    }

    /**
     * the tokenizer has no section informations while tokenizing.
     * We need to find procedures and fix the T_END to T_PROCEDURE_END
     */
    public function fixProcedureEndCall($tokens){
        $current = 0 ;

        $found = false;
        while($current < count($tokens)){

            $token = $tokens[ $current ];

            if (
                $token['type'] == Token::T_PROCEDURE &&
                $tokens[ $current + 3 ]['type']  != Token::T_FORWARD
            ){
                $found = true;
            }elseif ($found && $token['type'] == Token::T_SCRIPT_END){
                $found = false;
                $tokens[ $current ]['type'] = Token::T_PROCEDURE_END;
            }

            $current++;
        }

        return $tokens;
    }

    public function fixCustomFunctionEndCall($tokens){
        $current = 0 ;

        $found = false;
        while($current < count($tokens)){

            $token = $tokens[ $current ];

            if (
                $token['type'] == Token::T_CUSTOM_FUNCTION &&
                $tokens[ $current + 3 ]['type']  != Token::T_FORWARD
            ){
                $found = true;
            }elseif ($found && $token['type'] == Token::T_SCRIPT_END){
                $found = false;
                $tokens[ $current ]['type'] = Token::T_CUSTOM_FUNCTION_END;
            }

            $current++;
        }

        return $tokens;
    }


    /*
     * the assignet value are sometimes a const, string, int or function call
     * the tokenizer can not handle this while parsing
     * we correct here any assign value to t_type_var
     */
    public function fixTypeMapping( $tokens, $types){
        $current = 0 ;

        while($current < count($tokens)){

            $token = $tokens[ $current ];

            if ($token['type'] == Token::T_ASSIGN){
                $prevToken = $tokens[ $current - 1];
                $nextToken = $tokens[ $current + 1];

                if (isset($types[$prevToken['value']])){

                    $tokens[ $current + 1] = [
                        'type' => Token::T_VARIABLE,
                        'value' => $nextToken['value'],
                        'abstract' => 'state',
                        'target' => $prevToken['value']
                    ];
                }
            }

            $current++;
        }

        return $tokens;
    }


    public function fixHeaderBracketMismatches( $tokens ){

        $found = false;

        $currentToken = false;
        foreach ($tokens as $index => $token) {

            if ($token['type'] == Token::T_DEFINE_SECTION_ENTITY) {
                $found = true;

                $currentToken = $index + 1;
            }else if($currentToken == $index){
                continue;
            }else if(
                $currentToken !== false &&
                $found &&
                (
                    $token['type'] != Token::T_DEFINE &&
                    $token['type'] != Token::T_LINEEND
                )
            ) {

                $tokens[$currentToken]['type'] = Token::T_VARIABLE;
                $tokens[$currentToken]['value'] .= $token['value'];

                unset($tokens[$index]);

            }elseif ($found === true){
                $found = false;
            }

        }

        return array_values($tokens);
    }

    /**
     * @param $source
     * @return array
     * @throws \Exception
     */
    public function run($source) {

        $lines = explode("\n", $source);

        $tokens = [];


        foreach($lines as $lineNumber => $line) {

            $line = trim($line);

            $offset = 0;

            while($offset < strlen($line)) {
                $result = $this->match($line, $offset, $tokens);

                if($result === false) {
                    throw new \Exception("Unable to parse line " . ($line+1) . ".");
                }

                $result['lineNumber'] = $lineNumber + 1;

                if (
                    $result['type'] !== Token::T_WHITESPACE &&
                    $result['type'] !== Token::T_SEPERATOR
                ){
                    $tokens[] = $result;
                }

                $offset += strlen($result['value']);
            }
        }

        return $tokens;
    }

    /**
     * @param $line
     * @param $offset
     * @return mixed
     * @throws \Exception
     */
    private function match($line, $offset, $tokens) {
        $string = substr($line, $offset);
        foreach ($this->tokens as $token) {

            if ($token == "App\\Service\\Compiler\\Tokens\\T_VARIABLE"){
                $parsed = $token::match($line, $offset, $this->game);

            }else{
                $parsed = $token::match($line, $offset, $tokens);
            }

            if ($parsed) return $parsed;
        }

        throw new \Exception(sprintf('Unable to Tokenize %s', $string));

    }

}