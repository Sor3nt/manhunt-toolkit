<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Compiler\Evaluate;
use App\Service\Compiler\FunctionMap\Manhunt;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\FunctionMap\ManhuntDefault;
use App\Service\Compiler\Line;
use App\Service\Helper;

class T_VARIABLE extends TAbstract {

    static public function getMapping( $node, $data ){

        $value = $node['value'];
        $valueLower = strtolower($value);

        if (
            isset($data['customData']['customFunctions']) &&
            isset($data['customData']['customFunctions'][ $valueLower ])
        ) {

            $mapped = $data['customData']['blockOffsets'][$valueLower];

        }else

            if (isset($data['customData']['procedureVars']) && isset($data['customData']['procedureVars'][ $value ])) {
            $mapped = $data['customData']['procedureVars'][$value];


        }else if (isset($data['customData']['customFunctionVars']) && isset($data['customData']['customFunctionVars'][ $value ])) {
            $mapped = $data['customData']['customFunctionVars'][$value];

            //array index access
        }else if (strpos($value, '[') !== false){

            $variableName = explode('[', $value)[0];

            $mapped = $data['combinedVariables'][$variableName];


        }else if (strpos($value, '.') !== false){

            //vec3d object pos.x
            $mapped = Evaluate::getObjectToAttributeSplit($value, $data);

        }else if (
            isset($node['target']) &&
            isset($data['types'][ $node['target'] ])
        ){
            $variableType = $data['types'][$node['target']];
            $mapped = $variableType[ strtolower($value) ];

        }else if (isset($data['combinedVariables'][ $value ])){
            $mapped = $data['combinedVariables'][ $value ];

        }else{
            throw new \Exception(sprintf("T_VARIABLE: unable to find variable offset for %s", $value));
        }
        return $mapped;
    }

    public function map( $node, \Closure $getLine, \Closure $emitter, $data ){


        $mapped = self::getMapping($node, $data);

        $debugMsg = "[T_VARIABLE] map type " . $mapped['type'] . ' ' . $node['value'] . " ";

        $code = [];

        switch ($mapped['type']){
            case 'vec3d':
                Evaluate::fromFineANameforMeTodo($mapped, $code, $getLine);
                break;

            case 'object':
                $code = $this->fromObjectAttribute($node, $data, $getLine);
                break;

            case 'stringarray':
                Evaluate::fromFineANameforMeTodo($mapped, $code, $getLine);
                Evaluate::readObject($mapped['size'], $code, $getLine);

                break;

            case 'level_var stringarray':
                Evaluate::fromLevelVarStringArray($mapped, $code, $getLine);
                Evaluate::readObject($mapped['size'], $code, $getLine);
                break;

            case 'custom_functions':
                $code = $this->fromCustomFunctions($node['value'], $data);
                break;

            case 'constant':
                Evaluate::readIndex($mapped['offset'], $code, $getLine);
                break;

            case 'level_var state':
            case 'level_var tlevelstate':
                $code = $this->fromLevelVarState($node, $data, $getLine);

                break;

            default:

                if(substr($mapped['type'], 0, 9) == "level_var") {
                    Evaluate::fromLevelVar($mapped, $code, $getLine);
                }else if(substr($mapped['type'], 0, 8) == "game_var") {
                    Evaluate::fromGameVar($mapped, $code, $getLine);

                }else if ($mapped['section'] == "header"){
                    Evaluate::fromFinedANameforMeTodoSecond($mapped, $code, $getLine);

                }else if ($mapped['section'] == "script" || $mapped['type'] == "procedure"){
                    Evaluate::fromFinedANameforMeTodoSecond($mapped, $code, $getLine);

                }else{
                    throw new \Exception(sprintf('T_VARIABLE: unhandled read '));
                }
        }

        $result = [];
        foreach ($code as $item) {
            if ($item instanceof Line){
                $result[] = $item;
            }else{
                $result[] = $getLine($item, false, $debugMsg);
            }
        }

        return $result;
    }


    private function fromCustomFunctions($value, $data){
        return [
            //TODO: unknown code sequence... lookup needed
            '10000000',
            '04000000',
            '11000000',
            '02000000',
            '00000000',
            '32000000',
            '02000000',
            '1c000000',
            '10000000',
            '02000000',
            '39000000',

            $data['customData']['customFunctions'][strtolower($value)]
        ];
    }

    private function fromLevelVarState($node, $data, $getLine){
        if (!isset($node['target'])){

            $mapped = $data['combinedVariables'][$node['value']];


            $code = [];
            Evaluate::fromLevelVar($mapped, $code, $getLine);

            return $code;
        }

        $variableType = $data['types'][$node['target']];
        $mapped = $variableType[ strtolower($node['value']) ];

        $code = [];
        Evaluate::readIndex($mapped['offset'], $code, $getLine);
        return $code;
    }

    private function fromObjectAttribute($node, $data, $getLine){

        $mapped = Evaluate::getObjectToAttributeSplit($node['value'], $data);

        $code = [];

        if ($mapped['offset'] !== $mapped['object']['offset']) {
            Evaluate::fromObjectAttribute($mapped, $code, $getLine);
        }else{
            Evaluate::fromObject($mapped, $code, $getLine);
        }

        //TODO: unknown code sequence... lookup needed
        $code[] = '0f000000';
        $code[] = '02000000';
        $code[] = '18000000';
        $code[] = '01000000';
        $code[] = '04000000';
        $code[] = '02000000';

        return $code;

    }

}