<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;
use App\Service\Compiler\Evaluate;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\Token;

class T_VARIABLE {

    static public function getMapping( $node, \Closure $emitter = null , $data ){

        $value = $node['value'];

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

            $mapped = Evaluate::getObjectToAttributeSplit($value, $data);

        }else{


            /**
             *
             * well this is not a good way, i just search the key...
             * it should be ok because the name of the type can not be a variable name
             * or function name....
             *
             */
            foreach ($data['types'] as $type) {
                foreach ($type as $name => $map) {

                    if ($name == strtolower($value)){

                        return $map;
                    }
                }
            }

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


            if (isset($data['types'][$mapped['type']])){

                $typeHandler = "App\\Service\\Compiler\\Emitter\\Types\\T_HEADER_TYPES";
                $code = $typeHandler::map($node, $getLine, $emitter, $data);

            }else{
                throw new \Exception($typeHandler . " Not implemented!");
            }
        }

        return $code;
    }

}