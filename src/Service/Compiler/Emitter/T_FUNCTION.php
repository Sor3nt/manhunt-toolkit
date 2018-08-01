<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;
use App\Service\Compiler\Evaluate;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\Token;

class T_FUNCTION {

    static public function finalize( $node, $data, &$code, \Closure $getLine ){

        switch ($node['type']){
            case Token::T_ADDITION:
            case Token::T_FUNCTION:
                break;
            case Token::T_FLOAT:
            case Token::T_INT:
            case Token::T_SELF:
            case Token::T_FALSE:
            case Token::T_TRUE:
                $code[] = $getLine('10000000');
                $code[] = $getLine('01000000');
                break;
            case Token::T_STRING:

                $code[] = $getLine('10000000');
                $code[] = $getLine('01000000');

                $code[] = $getLine('10000000');
                $code[] = $getLine('02000000');
                break;

            case Token::T_VARIABLE:
                $mappedTo = T_VARIABLE::getMapping(
                    $node,
                    null,
                    $data
                );

                switch ($mappedTo['section']) {
                    case 'header':


                        switch ($mappedTo['type']) {
                            case 'constant';
                                $code[] = $getLine('10000000');
                                $code[] = $getLine('01000000');
                                break;
                            case 'stringarray':
                                $code[] = $getLine('10000000');
                                $code[] = $getLine('01000000');

                                $code[] = $getLine('10000000');
                                $code[] = $getLine('02000000');

                                break;
                            case 'vec3d':
                                $code[] = $getLine('10000000');
                                $code[] = $getLine('01000000');
                                break;
                            default:
                                throw new \Exception($mappedTo['type'] . " Not implemented!");
                                break;
                        }


                        break;
                    case 'script':


                        switch ($mappedTo['type']) {

                            case 'entityptr':
                                $code[] = $getLine('10000000');
                                $code[] = $getLine('01000000');

                                break;
                            case 'vec3d':
                                $code[] = $getLine('10000000');
                                $code[] = $getLine('01000000');
                                break;
                            case 'integer':
                                $code[] = $getLine('10000000');
                                $code[] = $getLine('01000000');
                                break;
                            case 'constant':
                                $code[] = $getLine('10000000');
                                $code[] = $getLine('01000000');

                                $code[] = $getLine('10000000');
                                $code[] = $getLine('02000000');

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

    }

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){


        $code = [ ];

        if (isset($node['params']) && count($node['params'])){
            $skipNext = false;

            foreach ($node['params'] as $index => $param) {

                if ($skipNext){
                    $skipNext = false;
                    continue;
                }

                if ($param['type'] == Token::T_ADDITION){
                    $mathValue = $node['params'][$index + 1];

                    $resultCode = $emitter( $mathValue );
                    foreach ($resultCode as $line) {
                        $code[] = $line;
                    }

                    $code[] = $getLine('0f000000');
                    $code[] = $getLine('04000000');


                    $code[] = $getLine('31000000');
                    $code[] = $getLine('01000000');
                    $code[] = $getLine('04000000');

                    $code[] = $getLine('10000000');
                    $code[] = $getLine('01000000');

                    $skipNext = true;
                }else{
                    $resultCode = $emitter( $param );
                    foreach ($resultCode as $line) {
                        $code[] = $line;
                    }

                }


                self::finalize($param, $data, $code, $getLine);

                /**
                 * When the input value is a negative float or int
                 * we assign the positive value and negate them with this sequence
                 */
                if (
                    ($param['type'] == Token::T_INT || $param['type'] == Token::T_FLOAT) &&
                    $param['value'] < 0
                ) {

                    Evaluate::negateLastValue($code, $getLine);
                }
            }
        }

        /**
         * Translate function call
         */
        if (!isset(Manhunt2::$functions[ strtolower($node['value']) ])){
            throw new \Exception(sprintf('Unknown function %s', $node['value']));
        }


        $code[] = $getLine( Manhunt2::$functions[ strtolower($node['value']) ]['offset'] );

        // the setpedorientation call has a secret additional call
        if (
            strtolower($node['value']) == 'setpedorientation'
        ){

            Evaluate::returnResult($code, $getLine);

            $code[] = $getLine('b0020000');

        }

        // the writedebug call has a secret additional call, maybe a flush command ?
        if (
            strtolower($node['value']) == 'writedebug' //&&
        ){

            if (!isset($node['last']) || $node['last'] === true) {
                $code[] = $getLine('74000000');
            }
        }

        /**
         * when we are inside a nested call, tell the interpreter to return the current value
         */

        if (isset($node['nested']) && $node['nested'] === true){
            Evaluate::returnResult($code, $getLine);
        }

        return $code;
    }

}