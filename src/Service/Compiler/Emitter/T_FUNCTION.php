<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\Token;

class T_FUNCTION {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){


        $code = [ ];


        if (isset($node['params']) && count($node['params'])){

            foreach ($node['params'] as $param) {


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

                    $code[] = $getLine('12000000');
                    $code[] = $getLine('01000000');

                    $resultCode = $emitter( $param );

                    foreach ($resultCode as $line) {
                        $code[] = $line;
                    }

                    $code[] = $getLine('10000000');
                    $code[] = $getLine('01000000');

                }else if ($param['type'] == Token::T_STRING){

                    // initialize string
                    $code[] = $getLine('21000000');
                    $code[] = $getLine('04000000');
                    $code[] = $getLine('01000000');
                    // we have quotes around the string, come from the tokenizer
                    $value = substr($param['value'], 1, -1);

                    if (!isset($data['strings'][$value])){
                        throw new \Exception(sprintf('String %s is not in the map !', $value));
                    }

                    $code[] = $getLine($data['strings'][$value]['offset']);


                    $code[] = $getLine('12000000');
                    $code[] = $getLine('02000000');

                    $resultCode = $emitter( $param );

                    foreach ($resultCode as $line) {
                        $code[] = $line;
                    }

                    $code[] = $getLine('10000000');
                    $code[] = $getLine('01000000');

                    //move string pointer ?
                    $code[] = $getLine('10000000');
                    $code[] = $getLine('02000000');
                }else if ($param['type'] == Token::T_VARIABLE){

                    if (isset(Manhunt2::$functions[ strtolower($param['value']) ])) {
                        // mismatch, some function has no params and looks loke variables
                        // just redirect to the function handler
                        return $emitter( [
                            'type' => Token::T_FUNCTION,
                            'value' => $param['value']
                        ] );

                    }else if (isset(Manhunt2::$constants[ $param['value'] ])) {
                        $mapped = Manhunt2::$constants[$param['value']];
                        $mapped['section'] = "constant";

                    }else if (isset(Manhunt2::$levelVarBoolean[ $param['value'] ])) {
                        $mapped = Manhunt2::$levelVarBoolean[$param['value']];

                    }else if (isset($data['variables'][$param['value']])){
                        $mapped = $data['variables'][$param['value']];

                    }else{
                        throw new \Exception(sprintf("T_FUNCTION: unable to find variable offset for %s", $param['value']));
                    }

                    // initialize string
                    if ($mapped['section'] == "script"){
                        $code[] = $getLine('22000000');
                    }else{
                        $code[] = $getLine('21000000');
                    }

                    $code[] = $getLine('04000000');
                    $code[] = $getLine('01000000');

                    // define the offset
                    $code[] = $getLine($mapped['offset']);

                    $code[] = $getLine('10000000');
                    $code[] = $getLine('01000000');


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

                    $code[] = $getLine('4f000000');
                    $code[] = $getLine('32000000');
                    $code[] = $getLine('09000000');
                    $code[] = $getLine('04000000');
                    $code[] = $getLine('10000000');
                    $code[] = $getLine('01000000');

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

        // the writedebug call has a secret additional call, maybe a flush command ?
        if (strtolower($node['value']) == 'writedebug'){
            $code[] = $getLine('74000000');
        }

        /**
         * when we are inside a nested call, tell the interpreter to return the current value
         */

        if (isset($node['nested']) && $node['nested'] === true){
            $code[] = $getLine('10000000');
            $code[] = $getLine('01000000');
        }

        return $code;
    }

}