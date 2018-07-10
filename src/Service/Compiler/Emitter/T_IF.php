<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;
use App\Service\Compiler\Token;

class T_IF {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $code = [];


        // If statement case1: if X = Y then
//        if (
//            $node['condition'][0]['type'] == Token::T_VARIABLE &&
//            $node['condition'][2]['type'] == Token::T_TRUE ||
//            $node['condition'][2]['type'] == Token::T_FALSE ||
//            $node['condition'][3]['type'] == Token::T_THEN
//        ){



            foreach ($node['condition'] as $condition) {

                list($variable, $operation, $value) = $condition['params'];

//                $variable = $condition[0];
//                $operation = $node['condition'][1];
//                $value = $node['condition'][2];

                if ($variable['type'] == Token::T_FUNCTION) {

                    $code = [];
                    $result = $emitter($variable);
                    foreach ($result as $line) {
                        $code[] = $line;
                    }

                } else if ($variable['type'] == Token::T_VARIABLE){

                    $mapped = $data['variables'][$variable['value']];

                    if ( $mapped['type'] == "level_var boolean"){
                        $code[] = $getLine('1b000000');

                        $result = $emitter($variable);
                        foreach ($result as $line) {
                            $code[] = $line;
                        }

                        $code[] = $getLine('04000000');
                        $code[] = $getLine('01000000');

                    }else{

                        if ($mapped['section'] == "script"){
                            $code[] = $getLine('13000000');
                        }else{
                            $code[] = $getLine('14000000');

                        }
                        $code[] = $getLine('01000000');
                        $code[] = $getLine('04000000');

                        $result = $emitter($variable);
                        foreach ($result as $line) {
                            $code[] = $line;
                        }

                    }
                }else{
                    throw new \Exception('first if parameter is not supported');
                }



                if ($value){
                    $code[] = $getLine('10000000');
                    $code[] = $getLine('01000000');

                    $code[] = $getLine('12000000'); //parameter (temp)
                    $code[] = $getLine('01000000'); //parameter (temp)

                    $result = $emitter($value);
                    foreach ($result as $line) {
                        $code[] = $line;
                    }

                    $code[] = $getLine('0f000000'); //parameter (temp)
                    $code[] = $getLine('04000000'); //parameter (temp)

                }
            }



            $code[] = $getLine('23000000'); //If statement
            $code[] = $getLine('04000000'); //If statement
            $code[] = $getLine('01000000'); //If statement
            $code[] = $getLine('12000000'); //If statement
            $code[] = $getLine('01000000'); //If statement
            $code[] = $getLine('01000000'); //If statement

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
                default:
                    throw new \Exception(sprintf('T_IF Unknown operator %s', $operation['type']));
                    break;
            }

            $lastLine = end($code)->lineNumber + 4;

            $code[] = $getLine( Helper::fromIntToHex($lastLine) ); // line offset for the IF start (or so)

            $code[] = $getLine('33000000'); //If statement
            $code[] = $getLine('01000000'); //If statement
            $code[] = $getLine('01000000'); //If statement
            $code[] = $getLine('24000000'); //If statement
            $code[] = $getLine('01000000'); //If statement
            $code[] = $getLine('00000000'); //If statement
            $code[] = $getLine('3f000000'); //If statement

            //pre generaste the bytecode (only for calculation)
            $isTrue = [];
            foreach ($node['isTrue'] as $entry) {
                $codes = $emitter($entry, false);
                foreach ($codes as $singleLine) {
                    $isTrue[] = $singleLine;
                }

            }

            // calculate the length
            // calculate the length
            $lastLine = end($code)->lineNumber;
            $endOffset = ($lastLine + count($isTrue)) * 4;
            $code[] = $getLine( Helper::fromIntToHex($endOffset) ); // line offset for the IF end


            // create the bytecode
            foreach ($node['isTrue'] as $entry) {
                $codes = $emitter($entry, false);
                foreach ($codes as $singleLine) {
                    $code[] = $singleLine;
                }
            }

            if (isset($node['isFalse']) && count($node['isFalse'])){

                $code[] = $getLine('3c000000'); //else

                //pre generaste the bytecode (only for calculation)
                $isFalse = [];
                foreach ($node['isTrue'] as $entry) {
                    $codes = $emitter($entry, false);
                    foreach ($codes as $singleLine) {
                        $isFalse[] = $singleLine;
                    }

                }

                // calculate the length
                $lastLine = end($code)->lineNumber;
                $endOffset = ($lastLine + count($isFalse)) * 4;
                $code[] = $getLine( Helper::fromIntToHex($endOffset) ); // line offset for the IF end


                // create the bytecode
                foreach ($node['isFalse'] as $entry) {
                    $codes = $emitter($entry, false);
                    foreach ($codes as $singleLine) {
                        $code[] = $singleLine;
                    }
                }

            }

//        }else{
//            throw new \Exception('Unsupported If statement');
//        }

        return $code;
    }

}