<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;
use App\Service\Compiler\Evaluate;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\Token;

class T_VARIABLE {

    static public function getMapping( $node, \Closure $emitter = null , $data ){
        $value = $node['value'];
//var_dump($node);
//exit;
        if (isset(Manhunt2::$constants[ $value ])) {
            $mapped = Manhunt2::$constants[ $value ];
            $mapped['section'] = "header";
            $mapped['type'] = "constant";

        }else if (isset(Manhunt2::$levelVarBoolean[ $value ])) {
            $mapped = Manhunt2::$levelVarBoolean[ $value ];
            $mapped['section'] = "header";
            $mapped['type'] = "level_var boolean";

        }else if (isset($data['const'][ $value ])){
            $mapped = $data['const'][ $value ];
            $mapped['section'] = "script";
            $mapped['type'] = "constant";

        }else if (isset($data['variables'][ $value ])){
            $mapped = $data['variables'][ $value ];

        }else if (strpos($value, '.') !== false){

//            if (isset($data['customData']['conditionVariable'])) {
                $mapped = Evaluate::getObjectToAttributeSplit($value, $data);
//            } else {
//                throw new \Exception(sprintf("T_FUNCTION: (numeric) unable to find variable offset for %s", $value));
//
//            }

        }else if (
            isset($node['target']) &&
            isset($data['types'][ $node['target'] ])
        ){

            $variableType = $data['types'][$node['target']];
            $mapped = $variableType[$value];
        }else{

            throw new \Exception(sprintf("T_VARIABLE: unable to find variable offset for %s", $value));
        }

        return $mapped;
    }


    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $mapped = self::getMapping($node, $emitter, $data);

        $typeHandler = "App\\Service\\Compiler\\Emitter\\Types\\";
        $typeHandler .= "T_";
        $typeHandler .= strtoupper($mapped['section']);
        $typeHandler .= "_" . strtoupper($mapped['type']);
        $typeHandler = str_replace(' ', '_', $typeHandler);

        if (class_exists($typeHandler)){
            $code = $typeHandler::map($node, $getLine, $emitter, $data);
        }else{
            throw new \Exception($typeHandler . " Not implemented!");
        }

        return $code;


    }

//
//    static public function initialize( $type, &$code, \Closure $getLine ){
//
//        if (
//            $type == 'level_var tLevelState'
//        ) {
//            $code[] = $getLine('12000000');
//            $code[] = $getLine('01000000');
//        }else{
//            throw new \Exception("Not implemented!");
//        }
//
//    }

}