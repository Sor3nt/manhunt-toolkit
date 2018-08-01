<?php
namespace App\Service\Compiler;

class Evaluate {


    /**
     * Initialize commands
     *
     */
    static public function initializeParameterInteger( &$code, \Closure $getLine ){

        $code[] = $getLine('12000000');
        $code[] = $getLine('01000000');
    }

    static public function initializeParameterString( &$code, \Closure $getLine ){

        $code[] = $getLine('12000000');
        $code[] = $getLine('02000000');
    }

    static public function initializeReadHeaderString( &$code, \Closure $getLine ){

        $code[] = $getLine('21000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('01000000');
    }

    static public function initializeReadHeaderStringArray( &$code, \Closure $getLine ){

        $code[] = $getLine('21000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('04000000');
    }

    static public function initializeReadHeaderBoolean( &$code, \Closure $getLine ){

        $code[] = $getLine('14000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('04000000');
    }

    static public function initializeReadHeaderInteger( &$code, \Closure $getLine ){

        $code[] = $getLine('13000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('04000000');
    }

    static public function initializeReadLevelVar( &$code, \Closure $getLine ){

        $code[] = $getLine('1b000000');
    }

    static public function initializeReadScriptVec3d( &$code, \Closure $getLine ){

        $code[] = $getLine('22000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('01000000');
    }


    static public function initializeStatementInteger( &$code, \Closure $getLine ){
        $code[] = $getLine('23000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('12000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('01000000');
    }




    /**
     * Return commands
     */

    //todo: das stimmt ggf nicht, das wird auch als init verwendet...
    static public function returnStringArrayResult( &$code, \Closure $getLine ){
        $code[] = $getLine('12000000');
        $code[] = $getLine('03000000');
    }

    static public function returnResult( &$code, \Closure $getLine ){
        $code[] = $getLine('10000000');
        $code[] = $getLine('01000000');
    }

    static public function returnStringResult( &$code, \Closure $getLine ){
        $code[] = $getLine('10000000');
        $code[] = $getLine('02000000');
    }

    static public function returnConstantResult( &$code, \Closure $getLine ){
        $code[] = $getLine('0f000000');
        $code[] = $getLine('04000000');
    }

    static public function returnLevelVarResult( &$code, \Closure $getLine ){
        $code[] = $getLine('04000000');
        $code[] = $getLine('01000000');
    }


    /**
     * Statement functions
     */

    static public function statementOperator( $node, &$code, \Closure $getLine ){

        switch ($node['type']){
            case Token::T_IS_EQUAL:
                $code[] = $getLine('3f000000');
                break;
            case Token::T_IS_NOT_EQUAL:
                $code[] = $getLine('40000000');
                break;
            case Token::T_IS_SMALLER:
                $code[] = $getLine('3d000000');
                break;
            case Token::T_IS_GREATER:
                $code[] = $getLine('42000000');
                break;
            default:
                throw new \Exception(sprintf('Evaluate:: Unknown statement operator %s', $node['type']));
                break;
        }
    }

    static public function setStatementFullCondition( &$code, \Closure $getLine ){
        $code[] = $getLine('33000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('01000000');
    }

    static public function setStatementNot( &$code, \Closure $getLine ){
        $code[] = $getLine('29000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('01000000');
    }

    static public function setStatementAnd( &$code, \Closure $getLine ){
        $code[] = $getLine('0f000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('25000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('0f000000');
        $code[] = $getLine('04000000');
    }

    static public function setStatementAddition( &$code, \Closure $getLine ){
        $code[] = $getLine('31000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('04000000');
    }

    static public function setStatementSubstraction( &$code, \Closure $getLine ){
        $code[] = $getLine('33000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('01000000');
    }

    static public function setStatementOperator($node, &$code, \Closure $getLine ){
        self::returnConstantResult($code, $getLine);

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
        $originalMap = $data['variables'][$originalObject];

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

    static public function negateLastValue( &$code, \Closure $getLine ){
        $code[] = $getLine('4f000000');
        $code[] = $getLine('32000000');
        $code[] = $getLine('09000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('10000000');
        $code[] = $getLine('01000000');
    }


}