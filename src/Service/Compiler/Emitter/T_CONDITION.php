<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;
use App\Service\Compiler\Evaluate;
use App\Service\Compiler\Token;

class T_CONDITION {


    static public function finalize( $node, $data, &$code, \Closure $getLine ){
        $mappedTo = [];

        switch ($node['type']){

            case Token::T_FUNCTION:
                break;
            case Token::T_INT:
            case Token::T_NIL:
                $code[] = $getLine('0f000000');
                $code[] = $getLine('04000000');


                break;
            case Token::T_TRUE:
            case Token::T_FALSE:

                if ($data['customData']['isWhile'] == true){
                    $code[] = $getLine('10000000');
                    $code[] = $getLine('01000000');

                }else if ($data['customData']['isWhile'] == false){
                    $code[] = $getLine('0f000000');
                    $code[] = $getLine('04000000');

                }
            break;

            case Token::T_FLOAT:
                $code[] = $getLine('10000000');
                $code[] = $getLine('01000000');

                break;

            case Token::T_VARIABLE:
                $mappedTo = T_VARIABLE::getMapping(
                    $node,
                    null,
                    $data
                );

                switch ($mappedTo['section']) {

                    case 'script':

                        switch ($mappedTo['type']) {

                            case 'entityptr':
                                $code[] = $getLine('0f000000');
                                $code[] = $getLine('04000000');
                                break;

                            case 'object':
//                                $code[] = $getLine('0f000000');
//                                $code[] = $getLine('02000000');

                                break;

                            default:
                                throw new \Exception($mappedTo['type'] . " Not implemented!");
                                break;
                        }
                        break;
                    case 'header':

                        switch ($mappedTo['type']) {

                            case 'constant':
                                $code[] = $getLine('0f000000');
                                $code[] = $getLine('04000000');

                                break;
                            case 'boolean':
                                // has only a return code (0x10 and 0x01)
                                // will assigned anyway
                                break;
                            case 'level_var boolean':

                                $code[] = $getLine('04000000');
                                $code[] = $getLine('01000000');

                                break;

                            default:
                                throw new \Exception($mappedTo['type'] . " Not implemented!");
                                break;
                        }
                        break;

                    default:
                        throw new \Exception($mappedTo['section'] . " Not implemented!");
                        break;
                }

                break;
            default:
                throw new \Exception($node['type'] . " Not implemented!");
                break;


        }

        return $mappedTo;
    }

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $code = [];

        if (count($node['body']) == 3) {
            if ($node['isNot']){
                throw new \Exception('T_CONDITION: The expression NOT can not be combined with an operator!');
            }

            list($variable, $operation, $value) = $node['body'];
            $result = $emitter($variable, true, [ 'conditionVariable' => $variable] );
            foreach ($result as $item) {
                $code[] = $item;
            }

//            $mappedTo = self::finalize( $variable, $data, $code, $getLine );

            $mappedTo = [];
            if ($variable['type'] == Token::T_VARIABLE){
                $mappedTo = T_VARIABLE::getMapping(
                    $variable,
                    null,
                    $data
                );

            }


            $code[] = $getLine('10000000');
            $code[] = $getLine('01000000');

            $result = $emitter($value, true, [ 'conditionVariable' => $variable] );
            foreach ($result as $item) {
                $code[] = $item;
            }


            self::finalize( $value, $data, $code, $getLine );

            // not sure about this part
            if (isset($mappedTo['type']) && $mappedTo['type'] == "object"){
                $code[] = $getLine('4e000000');
                $code[] = $getLine('12000000');
                $code[] = $getLine('01000000');
                $code[] = $getLine('01000000');
            }else{
                $code[] = $getLine('23000000');
                $code[] = $getLine('04000000');
                $code[] = $getLine('01000000');
                $code[] = $getLine('12000000');
                $code[] = $getLine('01000000');
                $code[] = $getLine('01000000');


            }


            Evaluate::statementOperator($operation, $code, $getLine);


            $lastLine = end($code)->lineNumber + 4;

            // line offset for the IF start (or so)
            $code[] = $getLine( Helper::fromIntToHex($lastLine * 4) );

            Evaluate::setStatementFullCondition($code, $getLine);


        }else if (count($node['body']) == 1){


            $result = $emitter($node['body'][0]);
            foreach ($result as $item) {
                $code[] = $item;
            }


            if ($node['isNot']){
                Evaluate::setStatementNot($code, $getLine);
            }

        }else if (count($node['body']) == 4){



            if ($node['isNot']){
                throw new \Exception('T_CONDITION: The expression NOT can not be combined with an operator!');
            }

            list($variable, $operation, $value, $addon) = $node['body'];
            $result = $emitter($variable, true, [ 'conditionVariable' => $variable] );
            foreach ($result as $item) {
                $code[] = $item;
            }

            $mappedTo = self::finalize( $variable, $data, $code, $getLine );

            $code[] = $getLine('10000000');
            $code[] = $getLine('01000000');

            $result = $emitter($value, true, [ 'conditionVariable' => $variable] );
            foreach ($result as $item) {
                $code[] = $item;
            }

            self::finalize( $value, $data, $code, $getLine );

            // not sure about this part
            if (isset($mappedTo['type']) && $mappedTo['type'] == "object"){
                $code[] = $getLine('4e000000');
                $code[] = $getLine('12000000');
                $code[] = $getLine('01000000');
                $code[] = $getLine('01000000');
            }

            $result = $emitter($addon, true, [ 'conditionVariable' => $variable] );
            foreach ($result as $item) {
                $code[] = $item;
            }

            //TODO: OR verbauen
            Evaluate::setStatementAnd($code, $getLine);
            Evaluate::initializeStatementInteger($code, $getLine);
            Evaluate::statementOperator($operation, $code, $getLine);

            $lastLine = end($code)->lineNumber + 4;

            // line offset for the IF start (or so)
            $code[] = $getLine( Helper::fromIntToHex($lastLine * 4) );

            Evaluate::setStatementFullCondition($code, $getLine);
        }


        return $code;
    }

}