<?php
namespace App\Service\Compiler\Emitter;

use App\Bytecode\Helper;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\Token;

class T_ASSIGN {

    static public function handleSimpleMath( $node, \Closure $getLine, \Closure $emitter, $data ){

        $code = [];
        list($leftHand, $operator, $rightHand) = $node;


        if ($leftHand['type'] == Token::T_VARIABLE){

            if (isset(Manhunt2::$functions[ strtolower($leftHand['value']) ])) {
                // mismatch, some function has no params and looks loke variables
                // just redirect to the function handler
                return $emitter( [
                    'type' => Token::T_FUNCTION,
                    'value' => $leftHand['value']
                ] );

            }else if (isset(Manhunt2::$constants[ $leftHand['value'] ])) {
                $mapped = Manhunt2::$constants[$leftHand['value']];
                $mapped['section'] = "constant";
            }else if (isset(Manhunt2::$levelVarBoolean[ $leftHand['value'] ])) {
                $mapped = Manhunt2::$levelVarBoolean[$leftHand['value']];
                $mapped['section'] = "level_var";

            }else if (isset($data['variables'][$leftHand['value']])){
                $mapped = $data['variables'][$leftHand['value']];

            }else{
                throw new \Exception(sprintf("T_FUNCTION: unable to find variable offset for %s", $leftHand['value']));
            }

            // initialize string
            if ($mapped['section'] == "constant") {
                $code[] = $getLine('12000000');
                $code[] = $getLine('01000000');

                // define the offset
                $code[] = $getLine($mapped['offset']);

                $code[] = $getLine('0f000000');
                $code[] = $getLine('04000000');

            }else if ($mapped['section'] == "header" && $mapped['type'] == "boolean") {

                $code[] = $getLine('14000000');
                $code[] = $getLine('01000000');
                $code[] = $getLine('04000000');

                // define the offset
                $code[] = $getLine($mapped['offset']);
            }else if (
                $mapped['section'] == "header" &&
                $mapped['type'] == "level_var integer"
            ) {

                $code[] = $getLine('1b000000');

                // define the offset
                $code[] = $getLine($mapped['offset']);

                $code[] = $getLine('04000000');
                $code[] = $getLine('01000000');

            }else if (
                $mapped['section'] == "header" &&
                $mapped['type'] == "integer"
            ) {

                $code[] = $getLine('13000000');
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
                var_dump($mapped);
                throw new \Exception(sprintf("T_FUNCTION: section unknown %s", $mapped['section']));

            }


            $code[] = $getLine('10000000');
            $code[] = $getLine('01000000');
        }else{
            throw new \Exception(sprintf('T_ASSIGN: handleSimpleMath unknown leftHand: %s', $leftHand['type']));

        }


        if (
            $rightHand['type'] == Token::T_INT
        ){

            $code[] = $getLine('12000000');
            $code[] = $getLine('01000000');

            $resultCode = $emitter($rightHand);
            foreach ($resultCode as $line) {
                $code[] = $line;
            }

            $code[] = $getLine('0f000000');
            $code[] = $getLine('04000000');


        }else{
            throw new \Exception(sprintf('T_ASSIGN: handleSimpleMath unknown rightHand: %s', $rightHand['type']));
        }


        if ($operator['type'] == Token::T_ADDITION) {

            $code[] = $getLine('31000000');
            $code[] = $getLine('01000000');
            $code[] = $getLine('04000000');

        }else if ($operator['type'] == Token::T_SUBSTRACTION){

            $code[] = $getLine('33000000');
            $code[] = $getLine('04000000');
            $code[] = $getLine('01000000');

        }else{
            throw new \Exception(sprintf('T_ASSIGN: handleSimpleMath operator not supported: %s', $operator['type']));

        }


        return $code;
    }

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $code = [];

        if (!isset($data['variables'][$node['value']])){
            throw new \Exception(sprintf('T_ASSIGN: unable to detect variable: %s', $node['value']));
        }

        $mapped = $data['variables'][$node['value']];


        if (count($node['body']) == 3) {
            $resultCode = self::handleSimpleMath($node['body'], $getLine, $emitter, $data);
            foreach ($resultCode as $line) {
                $code[] = $line;
            }

            if (
                $mapped['section'] == "header" &&
                $mapped['type'] == "level_var integer"
            ) {

                $code[] = $getLine('1a000000');
                $code[] = $getLine('01000000');

                // define the offset
                $code[] = $getLine($mapped['offset']);

                $code[] = $getLine('04000000');

            }else if (
                $mapped['section'] == "header" &&
                $mapped['type'] == "integer"
            ) {

                $code[] = $getLine('11000000');
                $code[] = $getLine('01000000');
                $code[] = $getLine('04000000');
                $code[] = $getLine('15000000');
                $code[] = $getLine('04000000');

                // define the offset
                $code[] = $getLine($mapped['offset']);

                $code[] = $getLine('01000000');


            }else{
                throw new \Exception(sprintf("T_FUNCTION: section unknown %s", $mapped['section']));

            }

        }else{



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
                if (
                    count($node['body']) == 1 &&
                    $node['body'][0]['type'] == Token::T_FUNCTION
                ) {

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
                    $code[] = $getLine('12000000');
                    $code[] = $getLine('01000000');

                    //evaluate the boolean
                    $resultCode = $emitter($node['body'][0]);
                    foreach ($resultCode as $line) {
                        $code[] = $line;
                    }

                    if ($mapped['section'] == "script"){

                        $code[] = $getLine('15000000'); // read from script header
                    }else{
                        $code[] = $getLine('16000000'); // read from global header
                    }

                    $code[] = $getLine('04000000');
                    $code[] = $getLine($mapped['offset']);
                    $code[] = $getLine('01000000');

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
            }else if (
                $mapped['type'] == "level_var boolean" ||
                $mapped['type'] == "level_var integer"
            ){

                // case 1: var := true
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
        }

        return $code;

    }

}