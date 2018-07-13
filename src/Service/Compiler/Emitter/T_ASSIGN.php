<?php
namespace App\Service\Compiler\Emitter;

use App\Bytecode\Helper;
use App\Service\Compiler\Emitter;
use App\Service\Compiler\Token;

class T_ASSIGN {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $code = [];

        if (!isset($data['variables'][$node['value']])){
            throw new \Exception(sprintf('T_ASSIGN: unable to detect variable: %s', $data['value']));
        }

        $mapped = $data['variables'][$node['value']];

        // me : string[30]
        if (substr(strtolower($mapped['type']), 0, 7) == "string[") {

            if (count($node['body']) == 1 && $node['body'][0]['type'] == Token::T_FUNCTION) {

                //evaluate the function call
                $resultCode = $emitter($node['body'][0]);
                foreach ($resultCode as $line) {
                    $code[] = $line;
                }

                $code[] = $getLine('21000000');
                $code[] = $getLine('04000000');
                $code[] = $getLine('04000000');

                $code[] = $getLine($mapped['offset']);

                $code[] = $getLine('12000000');
                $code[] = $getLine('03000000');


                $code[] = $getLine(Helper::fromIntToHex($mapped['size']));

                $code[] = $getLine('10000000');
                $code[] = $getLine('04000000');
                $code[] = $getLine('10000000');
                $code[] = $getLine('03000000');
                $code[] = $getLine('48000000');


                return $code;

            } else {
                throw new \Exception(sprintf('T_ASSIGN: Unknown type for string array assignment: %s  '), $node['body'][0]['type']);
            }

        //animLength := GetAnimationLength('ASY_NURSE_ATTACK4A');
        }else if ($mapped['type'] == "integer"){
            if (count($node['body']) == 1 && $node['body'][0]['type'] == Token::T_FUNCTION) {

                //evaluate the function call
                $resultCode = $emitter($node['body'][0]);
                foreach ($resultCode as $line) {
                    $code[] = $line;
                }

                if ($mapped['section'] == "script") {
                    $code[] = $getLine('15000000');
                }else{
                    $code[] = $getLine('16000000');
                }

                $code[] = $getLine('04000000');

                $code[] = $getLine($mapped['offset']);

                $code[] = $getLine('01000000');



            } else {
                throw new \Exception(sprintf('T_ASSIGN: Unknown type for integer assignment: %s  '), $node['body'][0]['type']);
            }



                //alreadyDone := FALSE;
        }else if ($mapped['type'] == "boolean"){


                if (
                    count($node['body']) == 1 &&
                    (
                        $node['body'][0]['type'] == Token::T_INT ||
                        $node['body'][0]['type'] == Token::T_FALSE ||
                        $node['body'][0]['type'] == Token::T_TRUE
                    )
                ) {
                    if ($mapped['section'] == "script"){
                        $code[] = $getLine('12000000');
                        $code[] = $getLine('01000000');


                        //evaluate the boolean
                        $resultCode = $emitter($node['body'][0]);
                        foreach ($resultCode as $line) {
                            $code[] = $line;
                        }

                        $code[] = $getLine('15000000'); // read from script header
                        $code[] = $getLine('04000000');

                        $code[] = $getLine('10000000');
                        $code[] = $getLine('01000000');


                    }else{

                        $code[] = $getLine('12000000');
                        $code[] = $getLine('01000000');


                        //evaluate the boolean
                        $resultCode = $emitter($node['body'][0]);
                        foreach ($resultCode as $line) {
                            $code[] = $line;
                        }

                        $code[] = $getLine('16000000'); // read from global header
                        $code[] = $getLine('04000000');

                        $code[] = $getLine($mapped['offset']);


                        $code[] = $getLine('01000000');


                    }


                } else {
                    throw new \Exception(sprintf('T_ASSIGN: Unknown type for boolean assignment: %s  '), $node['body'][0]['type']);
                }



//        //stealthTwoHeard := TRUE;
        }else if ($mapped['type'] == "level_var tLevelState"){

            if (isset($data['types'][$node['value']])){

                $variableType = $data['types'][$node['value']];
                $type = $variableType[$node['body'][0]['value']];

                $code[] = $getLine('12000000');
                $code[] = $getLine('01000000');

                $code[] = $getLine($type['offset']);

                $code[] = $getLine('1a000000');
                $code[] = $getLine('01000000');

                $code[] = $getLine($mapped['offset']);

                $code[] = $getLine('04000000');
            }else{
                throw new \Exception(sprintf('T_ASSIGN: level_var tLevelState type not found: %s  '), $node['value']);

            }

        //stealthTwoHeard := TRUE;
        }else if ($mapped['type'] == "level_var boolean"){

            if (
                count($node['body']) == 1 &&
                (
                    $node['body'][0]['type'] == Token::T_INT ||
                    $node['body'][0]['type'] == Token::T_FALSE ||
                    $node['body'][0]['type'] == Token::T_TRUE
                )
            ) {

                $code[] = $getLine('12000000');
                $code[] = $getLine('01000000');

                //evaluate the integer
                $resultCode = $emitter($node['body'][0]);
                foreach ($resultCode as $line) {
                    $code[] = $line;
                }

                $code[] = $getLine('1a000000');
                $code[] = $getLine('01000000');

                $code[] = $getLine($mapped['offset']);

                $code[] = $getLine('04000000');

            } else {
                throw new \Exception(sprintf('T_ASSIGN: Unknown type for level_var boolean assignment: %s  '), $node['body'][0]['type']);

            }


        }else{

            throw new \Exception(sprintf('T_ASSIGN: Type %s not implemented  ', $mapped['type']));
        }

        return $code;

    }

}