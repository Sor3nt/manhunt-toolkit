<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Compiler\Evaluate;
use App\Service\Compiler\FunctionMap\Manhunt;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\FunctionMap\ManhuntDefault;
use App\Service\Helper;

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
        $code = [];

        if ($mapped['type'] == "vec3d") {
            $this->fromVec3d($mapped, $code, $getLine);
        }else if ($mapped['type'] == "object") {
           $this->fromObject($node, $data, $code, $getLine);
        }else if ($mapped['type'] == "stringarray") {
           $this->fromHeaderStringArray($mapped, $code, $getLine);
        }else if ($mapped['type'] == "custom_functions") {
           $this->fromCustomFunctions($node['value'], $data, $code, $getLine);
        }else if ($mapped['type'] == "level_var state") {
            $this->fromLevelVarState($node, $data, $code, $getLine);
//        }else if(substr($mapped['type'], 0, 9) == "level_var") {
//            $this->fromLevelVar($mapped, $code, $getLine);

        }else{

            if (
                $mapped['section'] == "script" &&
                (
                    $mapped['type'] == "boolean" ||
                    $mapped['type'] == "integer" ||
                    $mapped['type'] == "real" ||
                    $mapped['type'] == "entityptr"
                )
            ) {

                $this->fromScript($mapped, $code, $getLine);

            }else if (
                    $mapped['section'] == "header" &&
                    (
                        $mapped['type'] == "constant"
                    )
                ){

                $this->fromConstant($mapped, $code, $getLine);

            }else if (
                    $mapped['section'] == "header" &&
                    (
                        $mapped['type'] == "boolean" ||
                        $mapped['type'] == "entityptr" ||
                        $mapped['type'] == "real" ||
                        $mapped['type'] == "integer"
                    )
                ){

                $this->fromHeader($mapped, $code, $getLine);

            }else{
//                    var_dump($mapped);
//                    exit;
                $typeHandler = "App\\Service\\Compiler\\Emitter\\Types\\";
                $typeHandler .= "T_";
                $typeHandler .= strtoupper($mapped['section']);
//var_dump($typeHandler);
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
                    throw new \Exception($typeHandler . " Not implemented!");
                }
            }



        }



        return $code;
    }


    private function fromCustomFunctions($value, $data, &$code, \Closure $getLine){
        $offset = $data['customData']['customFunctions'][strtolower($value)];
        $code[] = $getLine(Helper::fromIntToHex($offset));
    }

    private function fromConstant($mapped, &$code, \Closure $getLine){
        $code[] = $getLine('12000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine($mapped['offset']);
    }

    private function fromScript($mapped, &$code, \Closure $getLine){
        $code[] = $getLine('13000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine($mapped['offset']);
    }

    private function fromHeader($mapped, &$code, \Closure $getLine){
        $code[] = $getLine('14000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine($mapped['offset']);
    }


    private function fromLevelVar($mapped, &$code, \Closure $getLine){
        $code[] = $getLine('1b000000');
        $code[] = $getLine($mapped['offset']);
        $code[] = $getLine('04000000');
        $code[] = $getLine('01000000');
    }

    private function fromLevelVarState($node, $data, &$code, \Closure $getLine){
        if (!isset($node['target'])){

            $mapped = $data['combinedVariables'][$node['value']];

            $code[] = $getLine('1b000000');
            $code[] = $getLine($mapped['offset']);
            $code[] = $getLine('04000000');
            $code[] = $getLine('01000000');
            return;
        }

        $variableType = $data['types'][$node['target']];
        $mapped = $variableType[ strtolower($node['value']) ];

        $code[] = $getLine('12000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine($mapped['offset']);

    }


    private function fromVec3d($mapped, &$code, \Closure $getLine){
        $code[] = $getLine( $mapped['section'] == "header" ? '21000000' : '22000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine($mapped['offset']);
    }


    private function fromState($mapped, &$code, \Closure $getLine){
        $code[] = $getLine('14000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine($mapped['offset']);
    }


    private function fromHeaderStringArray($mapped, &$code, \Closure $getLine){
        $code[] = $getLine($mapped['section'] == "header" ? '21000000' : '22000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine($mapped['offset']);
        $code[] = $getLine('12000000');
        $code[] = $getLine('02000000');

        $code[] = $getLine(Helper::fromIntToHex( $mapped['size']  ));
    }

    private function fromObject($node, $data, &$code, \Closure $getLine){

        $mapped = Evaluate::getObjectToAttributeSplit($node['value'], $data);

        $code = [];

        $code[] = $getLine($mapped['section'] == "header" ? '21000000' : '22000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine($mapped['object']['offset']);

        $code[] = $getLine('10000000');
        $code[] = $getLine('01000000');

        $code[] = $getLine('0f000000');

        if ($mapped['offset'] == $mapped['object']['offset']) {
            $code[] = $getLine('02000000');

            $code[] = $getLine('18000000');
            $code[] = $getLine('01000000');
            $code[] = $getLine('04000000');
            $code[] = $getLine('02000000');
        }else{
            $code[] = $getLine('01000000');

            $code[] = $getLine('32000000');
            $code[] = $getLine('01000000');

            $code[] = $getLine($mapped['offset']);

            $code[] = $getLine('10000000');
            $code[] = $getLine('01000000');
            $code[] = $getLine('0f000000');
            $code[] = $getLine('02000000');
            $code[] = $getLine('18000000');
            $code[] = $getLine('01000000');
            $code[] = $getLine('04000000');
            $code[] = $getLine('02000000');
        }
    }

}