<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\Token;

class T_VARIABLE {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){
        $code = [];


        if (isset(Manhunt2::$functions[ strtolower($node['value']) ])) {

            // mismatch, some function has no params and looks loke variables
            // just redirect to the function handler
            return $emitter( [
                'type' => Token::T_FUNCTION,
                'value' => $node['value']
            ] );

        }else if (isset(Manhunt2::$levelVarBoolean[ $node['value'] ])) {
            $mapped = Manhunt2::$levelVarBoolean[$node['value']];
        }else if (isset(Manhunt2::$constants[ $node['value'] ])) {
            $mapped = Manhunt2::$constants[$node['value']];

        }else if (isset($data['variables'][$node['value']])){
            $mapped = $data['variables'][$node['value']];
            $mapped['offset'] = Helper::fromIntToHex($mapped['offset']);

        }else{
            throw new \Exception(sprintf("unable to find variable offset for %s", $node['value']));
        }

        $code[] = $getLine($mapped['offset']);

        return $code;


    }

}