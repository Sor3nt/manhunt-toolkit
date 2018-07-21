<?php
namespace App\Service\Compiler;

use App\Service\Compiler\Tokens\T_ADDITION;
use App\Service\Compiler\Tokens\T_AND;
use App\Service\Compiler\Tokens\T_ASSIGN;
use App\Service\Compiler\Tokens\T_BEGIN;
use App\Service\Compiler\Tokens\T_BRACKET_CLOSE;
use App\Service\Compiler\Tokens\T_BRACKET_OPEN;
use App\Service\Compiler\Tokens\T_DEFINE;
use App\Service\Compiler\Tokens\T_DEFINE_SECTION_ENTITY;
use App\Service\Compiler\Tokens\T_DEFINE_TYPE;
use App\Service\Compiler\Tokens\T_DEFINE_SECTION_VAR;
use App\Service\Compiler\Tokens\T_DEFINE_SECTION_TYPE;
use App\Service\Compiler\Tokens\T_DO;
use App\Service\Compiler\Tokens\T_END;
use App\Service\Compiler\Tokens\T_ELSE;
use App\Service\Compiler\Tokens\T_FALSE;
use App\Service\Compiler\Tokens\T_FLOAT;
use App\Service\Compiler\Tokens\T_FORWARD;
use App\Service\Compiler\Tokens\T_FUNCTION;
use App\Service\Compiler\Tokens\T_IF;
use App\Service\Compiler\Tokens\T_INT;
use App\Service\Compiler\Tokens\T_IS_EQUAL;
use App\Service\Compiler\Tokens\T_IS_GREATER;
use App\Service\Compiler\Tokens\T_IS_NOT_EQUAL;
use App\Service\Compiler\Tokens\T_IS_SMALLER;
use App\Service\Compiler\Tokens\T_LEVEL_VAR;
use App\Service\Compiler\Tokens\T_LINEEND;
use App\Service\Compiler\Tokens\T_NIL;
use App\Service\Compiler\Tokens\T_NOT;
use App\Service\Compiler\Tokens\T_NULL;
use App\Service\Compiler\Tokens\T_OR;
use App\Service\Compiler\Tokens\T_PROCEDURE;
use App\Service\Compiler\Tokens\T_PROCEDURE_NAME;
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
use App\Service\Compiler\Tokens\T_TRUE;
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

//        T_HEADER_DEFINE::class,
        T_WHITESPACE::class,

        T_FORWARD::class,
        T_LINEEND::class,
        T_ADDITION::class,
        T_SUBSTRACTION::class,
        T_LEVEL_VAR::class,
        T_DEFINE_TYPE::class,
        T_DEFINE_SECTION_TYPE::class,
        T_DEFINE_SECTION_ENTITY::class,
        T_DEFINE_SECTION_VAR::class,
        T_SCRIPTMAIN_NAME::class,

        T_SCRIPTMAIN::class,
        T_DEFINE::class,
        T_PROCEDURE::class,
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

        T_TRUE::class,
        T_FALSE::class,
        T_FLOAT::class,
        T_INT::class,
        T_NULL::class,

        T_ASSIGN::class,
        T_IS_NOT_EQUAL::class,
        T_IS_EQUAL::class,
        T_IS_GREATER::class,
        T_IS_SMALLER::class,


        T_FUNCTION::class,
        T_VARIABLE::class,


    ];


    /**
     * the tokenizer has no section informations while tokenizing.
     * We need to find procedures and fix the T_END to T_PROCEDURE_END
     */
    public function fixProcedureEndCall($tokens){
        $current = 0 ;

        $found = false;
        while($current < count($tokens)){

            $token = $tokens[ $current ];

            if ($token['type'] == Token::T_PROCEDURE){
                $found = true;
            }elseif ($found && $token['type'] == Token::T_END){
                $found = false;
                $tokens[ $current ]['type'] = Token::T_PROCEDURE_END;
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
                        'type' => Token::T_TYPE_VAR,
                        'value' => $nextToken['value']
                    ];
                }
            }

            $current++;
        }

        return $tokens;
    }


    public function fixHeaderBracketMismatches( $tokens, $types){

        $result = [];
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
        echo "################\n";
        echo "Tokenizer Start\n";

        $lines = explode("\n", $source);

        $tokens = [];


        foreach($lines as $lineNumber => $line) {

            $line = trim($line);

            echo "\nProcess ". $line . "\n";

            $offset = 0;


            while($offset < strlen($line)) {
                $result = $this->match($line, $offset);

                if($result === false) {
                    throw new \Exception("Unable to parse line " . ($line+1) . ".");
                }

                if (
                    $result['type'] !== Token::T_WHITESPACE &&
                    $result['type'] !== Token::T_SEPERATOR
//                    $result['type'] !== Token::T_LINEEND
                ){
                    echo $result['type'] .  "\t\t\t" . $result['value'] .  "\n" ;

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
    private function match($line, $offset) {
        $string = substr($line, $offset);

        foreach ($this->tokens as $token) {
            $parsed = $token::match($line, $offset);

            if ($parsed) return $parsed;
        }

        throw new \Exception(sprintf('Unable to Tokenize %s', $string));

    }

}