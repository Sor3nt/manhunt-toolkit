<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Compiler\Evaluate;
use App\Service\Compiler\FunctionMap\Manhunt;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\FunctionMap\ManhuntDefault;
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

        $debugMsg = "[T_VARIABLE] map type " . $mapped['type'];


        switch ($mapped['type']){
            case 'vec3d':
                $code = $this->fromVec3d($mapped);
                break;

            case 'object':
                $code = $this->fromObject($node, $data);
                break;

            case 'stringarray':
                $code = $this->fromStringArray($mapped);
                break;

            case 'level_var stringarray':
                $code = $this->fromLevelVarStringArray($mapped);
                break;

            case 'custom_functions':
                $code = $this->fromCustomFunctions($node['value'], $data);
                break;

            case 'constant':
                $code = $this->fromConstant($mapped);
                break;

            case 'level_var state':
            case 'level_var tlevelstate':
                $code = $this->fromLevelVarState($node, $data);
                break;

            default:

                if(substr($mapped['type'], 0, 9) == "level_var") {
                    $code = $this->fromLevelVar($mapped);

                }else if ($mapped['section'] == "header"){
                    $code = $this->fromHeader($mapped);

                }else if ($mapped['section'] == "script" || $mapped['type'] == "procedure"){

                    if (isset($mapped['isArg']) && $mapped['isArg']){

                        $code[] = '10030000';
                        $code[] = '24000000';
                        $code[] = '01000000';

//                        $code = $this->fromScript($mapped);
                    }else{
                        $code = $this->fromScript($mapped);
                    }

                }else{
                    throw new \Exception(sprintf('T_VARIABLE: unhandled read '));
                }
        }

        $result = [];
        foreach ($code as $item) {
            $result[] = $getLine($item, false, $debugMsg);
        }

        return $result;
    }


    private function fromCustomFunctions($value, $data){
        return [
            $data['customData']['customFunctions'][strtolower($value)]
        ];
    }

    private function fromConstant($mapped){
        return [
            '12000000',
            '01000000',
            $mapped['offset']
        ];
    }

    private function fromScript($mapped){
        return [
            '13000000',
            '01000000',
            '04000000',
            $mapped['offset']
        ];
    }

    private function fromHeader($mapped){
        return [
            '14000000',
            '01000000',
            '04000000',
            $mapped['offset']
        ];
    }

    private function fromLevelVar($mapped){
        return [
            '1b000000',
            $mapped['offset'],
            '04000000',
            '01000000'
        ];
    }

    private function fromLevelVarState($node, $data){
        if (!isset($node['target'])){

            $mapped = $data['combinedVariables'][$node['value']];

            return $this->fromLevelVar($mapped);
        }

        $variableType = $data['types'][$node['target']];
        $mapped = $variableType[ strtolower($node['value']) ];

        return [
            '12000000',
            '01000000',
            $mapped['offset']
        ];
    }

    private function fromVec3d($mapped){
        return [
            $mapped['section'] == "header" ? '21000000' : '22000000',
            '04000000',
            '01000000',
            $mapped['offset']
        ];
    }

    private function fromLevelVarStringArray($mapped){

        return [
            '1c000000',
            '01000000',
            $mapped['offset'],
            '1e000000',
            '12000000',
            '02000000',
            Helper::fromIntToHex( $mapped['size']  )
        ];
    }

    private function fromStringArray($mapped){
        return [
            $mapped['section'] == "header" ? '21000000' : '22000000',
            '04000000',
            '01000000',
            $mapped['offset'],
            '12000000',
            '02000000',
            Helper::fromIntToHex( $mapped['size']  )
        ];
    }

    private function fromObject($node, $data){

        $mapped = Evaluate::getObjectToAttributeSplit($node['value'], $data);

        $code =  [
            $mapped['section'] == "header" ? '21000000' : '22000000',
            '04000000',
            '01000000',
            $mapped['object']['offset'],
            '10000000',
            '01000000',
            '0f000000'
        ];

        if ($mapped['offset'] !== $mapped['object']['offset']) {
            $code[] = '01000000';

            $code[] = '32000000';
            $code[] = '01000000';

            $code[] = $mapped['offset'];

            $code[] = '10000000';
            $code[] = '01000000';
            $code[] = '0f000000';
        }

        $code[] = '02000000';
        $code[] = '18000000';
        $code[] = '01000000';
        $code[] = '04000000';
        $code[] = '02000000';

        return $code;
    }

}