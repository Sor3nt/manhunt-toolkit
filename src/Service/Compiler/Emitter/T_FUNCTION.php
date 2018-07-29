<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;
use App\Service\Compiler\Evaluate;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\Token;

class T_FUNCTION {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){


        $code = [ ];

        if (isset($node['params']) && count($node['params'])){
            $skipNext = false;

            foreach ($node['params'] as $index => $param) {

                if ($skipNext){
                    $skipNext = false;
                    continue;
                }

                /**
                 * Define for INT, FLOAT and STRING a construct and destruct sequence
                 */
                if (
                    $param['type'] == Token::T_INT ||
                    $param['type'] == Token::T_FLOAT ||
                    $param['type'] == Token::T_TRUE ||
                    $param['type'] == Token::T_FALSE ||
                    $param['type'] == Token::T_SELF
                ) {

                    Evaluate::processNumeric($param, $code, $data, $getLine, $emitter);

                }else if ($param['type'] == Token::T_STRING){

                    Evaluate::initializeReadHeaderString($code, $getLine);
                    Evaluate::processString($param, $code, $getLine, $data);
                    Evaluate::initializeParameterString($code, $getLine);

                    $resultCode = $emitter( $param );
                    foreach ($resultCode as $line) {
                        $code[] = $line;
                    }

                    Evaluate::returnResult($code, $getLine);
                    Evaluate::returnStringResult($code, $getLine);

                }else if ($param['type'] == Token::T_VARIABLE){

                    $mapped = Evaluate::processVariable(
                        $param,
                        $code,
                        array_merge($data, ['customData' => $node ]),
                        $getLine,
                        $emitter
                    );

                    if ($mapped == false) {
                        Evaluate::returnResult($code, $getLine);
                    }


                }else if ($param['type'] == Token::T_ADDITION){
                    $result = Evaluate::handleSimpleMath([
                        false,
                        $param,
                        $node['params'][$index + 1]
                    ], $getLine, $emitter, $data);

                    foreach ($result as $item) {
                        $code[] = $item;
                    }

                    Evaluate::returnResult($code, $getLine);

                    $skipNext = true;


                }else if ($param['type'] == Token::T_FUNCTION){
                    $resultCode = $emitter( $param );

                    foreach ($resultCode as $line) {
                        $code[] = $line;
                    }

                }else{
                    throw new \Exception(sprintf('Unknown type %s', $param['type']));
                }


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