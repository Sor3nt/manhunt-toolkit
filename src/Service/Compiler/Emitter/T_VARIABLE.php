<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Compiler\Evaluate;
use App\Service\Compiler\FunctionMap\Manhunt;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\FunctionMap\ManhuntDefault;

class T_VARIABLE {

    static public function getMapping( $node, \Closure $emitter = null , $data ){

        $constantsDefault = ManhuntDefault::$constants;
        $constants = Manhunt2::$constants;
        if (GAME == "mh1") $constants = Manhunt::$constants;


        $value = $node['value'];

        if (isset($data['variables'][ $value ])){
            $mapped = $data['variables'][ $value ];

        }else if (isset($constantsDefault[ $value ])) {
            $mapped = $constantsDefault[ $value ];
            $mapped['section'] = "header";
            $mapped['type'] = "constant";

        }else if (isset($constants[ $value ])) {
            $mapped = $constants[ $value ];
            $mapped['section'] = "header";
            $mapped['type'] = "constant";

        }else if (isset($data['const'][ $value ])){
            $mapped = $data['const'][ $value ];
            $mapped['section'] = "script";
            $mapped['type'] = "constant";

        }else if (strpos($value, '.') !== false){

            $mapped = Evaluate::getObjectToAttributeSplit($value, $data);

        }else if (
            isset($node['target']) &&
            isset($data['types'][ $node['target'] ])
        ){

            $variableType = $data['types'][$node['target']];
            $mapped = $variableType[ strtolower($value) ];
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

        if (isset($mapped['abstract'])){
            $typeHandler .= "_" . strtoupper($mapped['abstract']);

        }else{
            $typeHandler .= "_" . strtoupper($mapped['type']);
        }

        $typeHandler = str_replace(' ', '_', $typeHandler);

        if (class_exists($typeHandler)){
            $code = $typeHandler::map($node, $getLine, $emitter, $data);
        }else{
            throw new \Exception($typeHandler . " Not implemented!");
        }

        return $code;
    }

}