<?php
namespace App\Service\Compiler;

class Evaluate {


    static public function setStatementOperator($node, &$code, \Closure $getLine ){

        $code[] = $getLine('0f000000');
        $code[] = $getLine('04000000');

        switch ($node['operator']){

            case Token::T_OR:
                $code[] = $getLine('27000000');
                break;
            case Token::T_AND:
                $code[] = $getLine('25000000');
                break;
            default:
                throw new \Exception(sprintf('Evaluate: setStatementOperator =>  %s is not a valid operator !', $node['operator']));
        }

        $code[] = $getLine('01000000');
        $code[] = $getLine('04000000');
    }


    static public function getObjectToAttributeSplit( $value, $data ){
        list($originalObject, $attribute) = explode('.', $value);

        $originalObject = strtolower($originalObject);
        $attribute = strtolower($attribute);

        if (!isset($data['combinedVariables'][$originalObject])){
            throw new \Exception('Evaluate fail for ' . $originalObject);
        }
        $originalMap = $data['combinedVariables'][$originalObject];

        if (strtolower($originalMap['type']) == "vec3d"){

            $mapped = [
                'section' => $originalMap['section'],
                'type' => 'object',
                'object' => $originalMap,
                'size' => 4
            ];

            switch ($attribute){
                case 'x':
                    $mapped['offset'] = $originalMap['offset'];
                    break;
                case 'y':
                    $mapped['offset'] = '04000000';
                    break;
                case 'z':
                    $mapped['offset'] = '08000000';
                    break;
            }

            return $mapped;

        }else{
            throw new \Exception(sprintf("unknown object type %s", $originalMap['type']));
        }
    }

}