<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Compiler\Evaluate;
use App\Service\Compiler\FunctionMap\Manhunt;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\FunctionMap\ManhuntDefault;

class T_VARIABLE extends TAbstract {

    static public function getMapping( $node, \Closure $emitter = null , $data ){

        $hardCodedConstants = array_merge(
            ManhuntDefault::$constants,
            Manhunt2::$constants
        );

        if (GAME == "mh1"){
            $hardCodedConstants = array_merge(
                ManhuntDefault::$constants,
                Manhunt::$constants
            );
        }


        $value = $node['value'];
        $valueLower = strtolower($value);

        if (
            isset($data['customData']['customFunctions']) &&
            isset($data['customData']['customFunctions'][ $valueLower ])
        ) {

            $mapped = $data['customData']['blockOffsets'][$valueLower];

        }else if (isset($data['customData']['procedureVars']) && isset($data['customData']['procedureVars'][ $value ])) {
            $mapped = $data['customData']['procedureVars'][$value];


        }else if (isset($data['customData']['customFunctionVars']) && isset($data['customData']['customFunctionVars'][ $value ])) {
            $mapped = $data['customData']['customFunctionVars'][$value];



        }else if (isset($data['variables'][ $value ])){
            $mapped = $data['combinedVariables'][ $value ];
        }else if (isset($hardCodedConstants[ $value ])) {
            $mapped = $data['combinedVariables'][ $value ];
        }else if (isset($data['const'][ $value ])){
            $mapped = $data['combinedVariables'][ $value ];

            //todo: das hat hier nix zusuchen, das muss schon im mapped drin sein!!
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

    public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $mapped = self::getMapping($node, $emitter, $data);

        $typeHandler = "App\\Service\\Compiler\\Emitter\\Types\\";
        $typeHandler .= "T_";
        $typeHandler .= strtoupper($mapped['section']);

        if (isset($mapped['isLevelVar']) && $mapped['isLevelVar'] == true){
            $typeHandler .= "_LEVEL_VAR";

        }

        if (isset($mapped['abstract'])){
            $typeHandler .= "_" . strtoupper($mapped['abstract']);

        }else{
            $typeHandler .= "_" . strtoupper($mapped['type']);
        }

        $typeHandler = str_replace(' ', '_', $typeHandler);

        if (class_exists($typeHandler)){
            $code = $typeHandler::map($node, $getLine, $emitter, $data);
        }else{
            var_dump($mapped, $data);
            throw new \Exception($typeHandler . " Not implemented!");
        }

        return $code;
    }

}