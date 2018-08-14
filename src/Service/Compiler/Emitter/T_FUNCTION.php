<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Compiler\Evaluate;
use App\Service\Compiler\FunctionMap\Manhunt;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\FunctionMap\ManhuntDefault;
use App\Service\Compiler\Token;

class T_FUNCTION {

    static public function finalize( $node, $data, &$code, \Closure $getLine ){

        switch ($node['type']){
            case Token::T_ADDITION:
            case Token::T_FUNCTION:
                break;
            case Token::T_FLOAT:
            case Token::T_SELF:
            case Token::T_FALSE:
            case Token::T_TRUE:
                $code[] = $getLine('10000000');
                $code[] = $getLine('01000000');
                break;

            case Token::T_INT:

                if ($node['value'] >= 0){
                    $code[] = $getLine('10000000');
                    $code[] = $getLine('01000000');
                }else{
                    $code[] = $getLine('2a000000');
                    $code[] = $getLine('01000000');
                    $code[] = $getLine('10000000');
                    $code[] = $getLine('01000000');
                }

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
                            case 'integer';
                                $code[] = $getLine('10000000');
                                $code[] = $getLine('01000000');
                                break;
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
                            case 'real':
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


        //HACK
        //todo: das hier müsste custom function calls code sein...
        if ($node['value'] == "InitAI"){

            return [

                $getLine('10000000'), //unknown
                $getLine('04000000'), //unknown
                $getLine('11000000'), //unknown
                $getLine('02000000'), //unknown
                $getLine('00000000'), //unknown
                $getLine('32000000'), //unknown
                $getLine('02000000'), //unknown
                $getLine('1c000000'), //unknown
                $getLine('10000000'), //unknown
                $getLine('02000000'), //unknown
                $getLine('39000000'), //unknown
                $getLine('00000000'), //unknown
            ];

        }

        try {
            T_VARIABLE::getMapping($node, null, $data);
            return $emitter([
                'type' => Token::T_VARIABLE,
                'value' => $node['value']
            ]);
        }catch(\Exception $e){

            if (strpos($e->getMessage(), 'unable to find variable') == false){
                throw $e;
            }
        }

        $functionForceFloarDefault = ManhuntDefault::$functionForceFloar;
        $functionForceFloar = Manhunt2::$functionForceFloar;
        if (GAME == "mh1") $functionForceFloar = Manhunt::$functionForceFloar;


        $forceFloatOrder = [];
        if (isset( $functionForceFloarDefault[strtolower($node['value'])] )) {
            $forceFloatOrder = $functionForceFloarDefault[strtolower($node['value'])];
        }else if (isset( $functionForceFloar[strtolower($node['value'])] )){
            $forceFloatOrder = $functionForceFloar[strtolower($node['value'])];
        }


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
                 * When the input value is a negative float
                 * we assign the positive value and negate them with this sequence
                 */
                if (
                    ( $param['type'] == Token::T_FLOAT) &&
                    $param['value'] < 0
                ) {

                    $code[] = $getLine('4f000000');
                    $code[] = $getLine('32000000');
                    $code[] = $getLine('09000000');
                    $code[] = $getLine('04000000');
                    $code[] = $getLine('10000000');
                    $code[] = $getLine('01000000');
//
                }

                if (
                    count($forceFloatOrder) > 0 &&
                    $param['type'] == Token::T_INT
                ) {

                    if (count($forceFloatOrder)){
                        if ($forceFloatOrder[$index] === true){
                            $code[] = $getLine('4d000000');
                            $code[] = $getLine('10000000');
                            $code[] = $getLine('01000000');

                        }
                    }
                }
            }
        }

        /**
         * Translate function call
         */
        $funtionsDefault = ManhuntDefault::$functions;
        $funtions = Manhunt2::$functions;
        if (GAME == "mh1") $funtions = Manhunt::$functions;

        if (
            !isset($funtionsDefault[ strtolower($node['value']) ]) &&
            !isset($funtions[ strtolower($node['value']) ])
        ){
            throw new \Exception(sprintf('Unknown function %s', $node['value']));
        }


        if (isset($funtionsDefault[ strtolower($node['value']) ])) {
            $code[] = $getLine($funtionsDefault[strtolower($node['value'])]['offset']);
        }else if (isset($funtions[ strtolower($node['value']) ])){
            $code[] = $getLine( $funtions[ strtolower($node['value']) ]['offset'] );
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

            $functionNoReturnDefault = ManhuntDefault::$functionNoReturn;
            $functionNoReturn = Manhunt2::$functionNoReturn;
            if (GAME == "mh1") $functionNoReturn = Manhunt::$functionNoReturn;

            if (
                !in_array(strtolower($node['value']), $functionNoReturnDefault ) &&
                !in_array(strtolower($node['value']), $functionNoReturn )
            ){

                $code[] = $getLine('10000000');
                $code[] = $getLine('01000000');
            }
        }

        return $code;
    }

}