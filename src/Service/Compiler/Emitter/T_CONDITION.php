<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;
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

            //nested call return result
            $code[] = $getLine('10000000');
            $code[] = $getLine('01000000');

            $result = self::parseValue($value, $getLine, $emitter, $data);
            foreach ($result as $item) {
                $code[] = $item;
            }


            // statement core
            $code[] = $getLine('23000000');
            $code[] = $getLine('04000000');
            $code[] = $getLine('01000000');
            $code[] = $getLine('12000000');
            $code[] = $getLine('01000000');
            $code[] = $getLine('01000000');


            switch ($operation['type']){
                case Token::T_IS_EQUAL:
                    $code[] = $getLine('3f000000');
                    break;
                case Token::T_IS_NOT_EQUAL:
                    $code[] = $getLine('40000000');
                    break;
                case Token::T_IS_SMALLER:
                    $code[] = $getLine('3d000000');
                    break;
                case Token::T_IS_GREATER:
                    $code[] = $getLine('42000000');
                    break;
                default:
                    throw new \Exception(sprintf('T_CONDITION: Unknown operator %s', $operation['type']));
                    break;
            }

            $lastLine = end($code)->lineNumber + 4;

            // line offset for the IF start (or so)
            $code[] = $getLine( Helper::fromIntToHex($lastLine * 4) );

//            if (Helper::fromIntToHex($lastLine) == "63040000"){
//                var_dump($code);
//                exit;
//
//            }


            $code[] = $getLine('33000000');
            $code[] = $getLine('01000000');
            $code[] = $getLine('01000000');
        }else if (count($node['body']) == 1){

            $result = self::parseValue(current($node['body']), $getLine, $emitter, $data);
            foreach ($result as $item) {
                $code[] = $item;
            }

            if ($node['isNot']){
                $code[] = $getLine('29000000');
                $code[] = $getLine('01000000');
                $code[] = $getLine('01000000');
            }

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

            $code[] = $getLine('12000000');
            $code[] = $getLine('01000000');

            $resultCode = $emitter( $node );

            foreach ($resultCode as $line) {
                $code[] = $line;
            }

            $code[] = $getLine('0f000000');
            $code[] = $getLine('04000000');

            return $code;

        }else if ($node['type'] == Token::T_VARIABLE){

            if (isset(Manhunt2::$functions[ strtolower($node['value']) ])) {
                // mismatch, some function has no params and looks loke variables
                // just redirect to the function handler
                return $emitter( [
                    'type' => Token::T_FUNCTION,
                    'value' => $node['value']
                ] );

            }else if (isset(Manhunt2::$constants[ $node['value'] ])) {
                $mapped = Manhunt2::$constants[$node['value']];
                $mapped['section'] = "constant";

            }else if (isset(Manhunt2::$levelVarBoolean[ $node['value'] ])) {
                $mapped = Manhunt2::$levelVarBoolean[$node['value']];
                $mapped['section'] = "level_var";

            }else if (isset($data['variables'][$node['value']])){
                $mapped = $data['variables'][$node['value']];

            }else{
                throw new \Exception(sprintf("T_FUNCTION: unable to find variable offset for %s", $node['value']));
            }

            // initialize string
            if ($mapped['section'] == "constant") {
                $code[] = $getLine('12000000');
                $code[] = $getLine('01000000');

                // define the offset
                $code[] = $getLine($mapped['offset']);

                $code[] = $getLine('0f000000');
                $code[] = $getLine('04000000');

            }else if (
            ($mapped['section'] == "header" && $mapped['type'] == "boolean") ||
            ($mapped['section'] == "body" && $mapped['type'] == "boolean")
            ) {

                $code[] = $getLine('14000000');
                $code[] = $getLine('01000000');
                $code[] = $getLine('04000000');

                // define the offset
                $code[] = $getLine($mapped['offset']);

            }else if ($mapped['section'] == "level_var") {
                $code[] = $getLine('1b000000');

                // define the offset
                $code[] = $getLine($mapped['offset']);

                $code[] = $getLine('04000000');
                $code[] = $getLine('01000000');
            }else{
                throw new \Exception(sprintf("T_FUNCTION: section unknown %s", $mapped['section']));

            }

            return $code;

        }else{
            var_dump($node);
            throw new \Exception(sprintf('T_CONDITION: %s is not supported', $node['type']));
        }
    }


}