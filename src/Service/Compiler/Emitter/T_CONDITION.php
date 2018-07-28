<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;
use App\Service\Compiler\Evaluate;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\Token;

class T_CONDITION {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $code = [];

        if (count($node['body']) == 3) {
            if ($node['isNot']){
                throw new \Exception('T_CONDITION: The expression NOT can not be combined with an operator!');
            }

            list($variable, $operation, $value) = $node['body'];


            $result = self::parseValue($variable, $getLine, $emitter, $data);
            foreach ($result as $item) {
                $code[] = $item;
            }

            Evaluate::returnResult($code, $getLine);

            $result = self::parseValue($value, $getLine, $emitter, array_merge($data, [ 'conditionVariable' => $variable]));
            foreach ($result as $item) {
                $code[] = $item;
            }

            Evaluate::initializeStatement($code, $getLine);
            Evaluate::statementOperator($operation, $code, $getLine);

            $lastLine = end($code)->lineNumber + 4;

            // line offset for the IF start (or so)
            $code[] = $getLine( Helper::fromIntToHex($lastLine * 4) );

            Evaluate::setStatementFullCondition($code, $getLine);

        }else if (count($node['body']) == 1){

            $result = self::parseValue(current($node['body']), $getLine, $emitter, $data);
            foreach ($result as $item) {
                $code[] = $item;
            }

            if ($node['isNot']){
                Evaluate::setStatementNot($code, $getLine);
            }

        }else if (count($node['body']) == 4){

            list($variable, $operation, $value, $addon) = $node['body'];


            $result = self::parseValue($variable, $getLine, $emitter, $data);
            foreach ($result as $item) {
                $code[] = $item;
            }

            Evaluate::returnResult($code, $getLine);

            $result = self::parseValue($value, $getLine, $emitter, array_merge($data, [ 'conditionVariable' => $variable]));
            foreach ($result as $item) {
                $code[] = $item;
            }

            $result = self::parseValue($addon, $getLine, $emitter, $data);
            foreach ($result as $item) {
                $code[] = $item;
            }

            //TODO: OR verbauen
            Evaluate::setStatementAnd($code, $getLine);

            Evaluate::initializeStatement($code, $getLine);
            Evaluate::statementOperator($operation, $code, $getLine);

            $lastLine = end($code)->lineNumber + 4;

            // line offset for the IF start (or so)
            $code[] = $getLine( Helper::fromIntToHex($lastLine * 4) );

            Evaluate::setStatementFullCondition($code, $getLine);

        }


        return $code;
    }




    static public function parseValue( $node, \Closure $getLine, \Closure $emitter, $data){

        $code = [];


        if ($node['type'] == Token::T_FUNCTION){

            $result = $emitter($node);
            foreach ($result as $item) {
                $code[] = $item;
            }

            return $code;

        /**
         * Define for INT, FLOAT and STRING a construct and destruct sequence
         */
        }else if (
            $node['type'] == Token::T_INT ||
            $node['type'] == Token::T_FLOAT ||
            $node['type'] == Token::T_NIL ||
            $node['type'] == Token::T_TRUE ||
            $node['type'] == Token::T_FALSE ||
            $node['type'] == Token::T_SELF
        ) {

            $status = Evaluate::processNumeric(
                $node,
                $code,
                $data,
                $getLine,
                $emitter
            );

            if ($status === false) return $code;

            return $code;

        }else if ($node['type'] == Token::T_VARIABLE){

            Evaluate::processVariable(
                $node,
                $code,
                $data,
                $getLine,
                $emitter
            );

            return $code;

        }else{
            var_dump($node);
            throw new \Exception(sprintf('T_CONDITION: %s is not supported', $node['type']));
        }
    }


}