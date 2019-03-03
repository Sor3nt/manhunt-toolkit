<?php
namespace App\Service\Compiler;

use App\Service\Helper;

class Evaluate {

//Evaluate::regularReturn($code, $getLine);

    static public function gotoBlock($name, $offset, &$code, \Closure $getLine ){

        $debugMsg = sprintf('[T_FUNCTION] map: call procedure/customFunction %s', $name);

        $code[] = $getLine('10000000', false, $debugMsg); //procedure
        $code[] = $getLine('04000000', false, $debugMsg); //procedure
        $code[] = $getLine('11000000', false, $debugMsg); //procedure
        $code[] = $getLine('02000000', false, $debugMsg); //procedure
        $code[] = $getLine('00000000', false, $debugMsg); //procedure
        $code[] = $getLine('32000000', false, $debugMsg); //procedure
        $code[] = $getLine('02000000', false, $debugMsg); //procedure
        $code[] = $getLine('1c000000', false, $debugMsg); //procedure
        $code[] = $getLine('10000000', false, $debugMsg); //procedure
        $code[] = $getLine('02000000', false, $debugMsg); //procedure
        $code[] = $getLine('39000000', false, $debugMsg); //procedure
        $code[] = $getLine(Helper::fromIntToHex($offset), false, $debugMsg . ' (offset)');
    }

    static public function goto($linePos, &$code, \Closure $getLine ){
        $debugMsg = sprintf('[goto] ');
        $code[] = $getLine('3c000000', false, $debugMsg);
        $code[] = $getLine(Helper::fromIntToHex($linePos), false, $debugMsg . 'offset');

    }

    static public function int2float(&$code, \Closure $getLine ){
        $code[] = $getLine('4d000000', false, 'integer to float');

        self::regularReturn($code, $getLine);

    }




    static public function compareString(&$code, \Closure $getLine ){
        $code[] = $getLine('49000000', false, 'compareString');
    }

    static public function compareFloat(&$code, \Closure $getLine ){
        $code[] = $getLine('4e000000', false, 'compareFloat');
    }

    static public function compareInteger(&$code, \Closure $getLine ){
        $code[] = $getLine('23000000', false, 'compareInteger');
        $code[] = $getLine('04000000', false, 'compareInteger');
        $code[] = $getLine('01000000', false, 'compareInteger');
    }






    static public function regularReturn(&$code, \Closure $getLine ){
        $code[] = $getLine('10000000', false, 'Return result');
        $code[] = $getLine('01000000', false, 'Return result');
    }

    static public function stringReturn(&$code, \Closure $getLine ){

        self::regularReturn($code, $getLine);

        $code[] = $getLine('10000000', false, 'Return result');
        $code[] = $getLine('02000000', false, 'Return result');
    }

    static public function returnCache(&$code, \Closure $getLine){
        $debugMsg = sprintf('[returnCache] ');

        $code[] = $getLine('0f000000', false, $debugMsg);
        $code[] = $getLine('04000000', false, $debugMsg);
    }






    static public function forward(&$code, \Closure $getLine ){
        $debugMsg = sprintf('[forward] ');
        $code[] = $getLine('10000000', false, $debugMsg);
        $code[] = $getLine('04000000', false, $debugMsg);
    }




    static public function setStatementNot( &$code, \Closure $getLine ){
        $debugMsg = sprintf('[T_CONDITION] setStatementNot: NOT');
        $code[] = $getLine('29000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);
    }

    static public function setOperation($type, &$code, \Closure $getLine ){
        $debugMsg = sprintf('[T_CONDITION] map: operation ' . $type);

        switch ($type){
            case Token::T_IS_EQUAL:
                $code[] = $getLine('3f000000', false, $debugMsg);
                break;
            case Token::T_IS_NOT_EQUAL:
                $code[] = $getLine('40000000', false, $debugMsg);
                break;
            case Token::T_IS_SMALLER:
                $code[] = $getLine('3d000000', false, $debugMsg);
                break;
            case Token::T_IS_GREATER:
                $code[] = $getLine('42000000', false, $debugMsg);
                break;
            case Token::T_IS_GREATER_EQUAL:
                $code[] = $getLine('41000000', false, $debugMsg);
                break;
            default:
                throw new \Exception(sprintf('Evaluate:: Unknown statement operator %s', $type));
                break;
        }
    }

    static public function setIntMathOperator($type, &$code, \Closure $getLine ){
        $debugMsg = sprintf('[setIntMathOperator] ' . $type);

        self::returnCache($code, $getLine);

        if ($type == Token::T_ADDITION) {

            $code[] = $getLine('31000000', false, $debugMsg . 'int T_ADDITION');

        }else if ($type == Token::T_MULTIPLY){
            $code[] = $getLine('35000000', false, $debugMsg . 'int T_MULTIPLY');
            $code[] = $getLine('04000000', false, $debugMsg . 'int T_MULTIPLY');

        }else if ($type == Token::T_SUBSTRACTION){

            $code[] = $getLine('33000000', false, $debugMsg . 'int T_SUBSTRACTION');

            $code[] = $getLine('04000000', false, $debugMsg . 'int T_SUBSTRACTION');
            $code[] = $getLine('01000000', false, $debugMsg . 'int T_SUBSTRACTION');
            $code[] = $getLine('11000000', false, $debugMsg . 'int T_SUBSTRACTION');
        }else{
            throw new \Exception(sprintf('setIntMathOperator: operator not supported: %s', $type));
        }

        $code[] = $getLine('01000000', false, $debugMsg . 'operation end');
        $code[] = $getLine('04000000', false, $debugMsg . 'operation end');

    }

    static public function setFloatMathOperator($type, &$code, \Closure $getLine ){
        $debugMsg = sprintf('[T_ASSIGN] setFloatMathOperator ' . $type);

        //TODO: das return gehört hier garnicht hin, oder ?!
        self::regularReturn($code, $getLine);

        if ($type == Token::T_ADDITION) {
            $code[] = $getLine('50000000', false, $debugMsg);
        }else if ($type == Token::T_SUBSTRACTION) {
            $code[] = $getLine('51000000', false, $debugMsg);
        }else if ($type == Token::T_MULTIPLY) {
            $code[] = $getLine('52000000', false, $debugMsg);
        }else{
            throw new \Exception('divide not implemented');
        }
    }

    static public function setStatementOperator($operator, &$code, \Closure $getLine ){

        switch ($operator){

            case Token::T_OR:
                $code[] = $getLine('27000000');
                break;
            case Token::T_AND:
                $code[] = $getLine('25000000');
                break;
            default:
                throw new \Exception(sprintf('Evaluate: setStatementOperator =>  %s is not a valid operator !', $operator));
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
                'objectType' => 'object',
                'object' => $originalMap,
                'size' => 4,
                'isArg' => $originalMap['isArg'],
                'isLevelVar' => $originalMap['isLevelVar'],
                'isGameVar' => $originalMap['isGameVar'],
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






    static public function toLevelVar( $offset, &$code, \Closure $getLine){
        $debugMsg = sprintf('[T_ASSIGN] toLevelVar ');

        $code[] = $getLine('1a000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);
        $code[] = $getLine( $offset, false, $debugMsg . 'offset' );
        $code[] = $getLine('04000000', false, $debugMsg);
    }


    static public function fromCustomFunction($name, &$code, \Closure $getLine){
        $debugMsg = sprintf('[fromCustomFunction] ' . $name);

        $code[] = $getLine('10000000', false, $debugMsg);
        $code[] = $getLine('02000000', false, $debugMsg);

        $code[] = $getLine('11000000', false, $debugMsg);
        $code[] = $getLine('02000000', false, $debugMsg);
        $code[] = $getLine('0a000000', false, $debugMsg);

        $code[] = $getLine('34000000', false, $debugMsg);
        $code[] = $getLine('02000000', false, $debugMsg);
        $code[] = $getLine('04000000', false, $debugMsg);
        $code[] = $getLine('20000000', false, $debugMsg);

        $code[] = $getLine('01000000', false, $debugMsg);
        $code[] = $getLine('04000000', false, $debugMsg);
        $code[] = $getLine('02000000', false, $debugMsg);

        $code[] = $getLine('0f000000', false, $debugMsg);
        $code[] = $getLine('02000000', false, $debugMsg);

        self::regularReturn($code, $getLine);
    }


    static public function fromLevelVar($mapped, &$code, \Closure $getLine){
        $debugMsg = sprintf('[fromLevelVar] ');
        $code[] = $getLine('1b000000', false, $debugMsg);
        $code[] = $getLine($mapped['offset'], false, $debugMsg);
        $code[] = $getLine('04000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);
    }

    static public function fromLevelVarStringArray($mapped, &$code, \Closure $getLine){
        $debugMsg = sprintf('[fromLevelVarStringArray] ');
        $code[] = $getLine('1c000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);
        $code[] = $getLine($mapped['offset'], false, $debugMsg);
        $code[] = $getLine('1e000000', false, $debugMsg);

    }

    static public function toGameVar( $node, &$code, \Closure $getLine){
        $debugMsg = sprintf('[T_ASSIGN] toGameVar ');

        $code[] = $getLine('1d000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);

        //todo hier muss der offset verbaut werden
        if ($node['value'] == "willie_game_int"){
            $code[] = $getLine('30000000', false, $debugMsg . 'right offset missed');
        }else{
            $code[] = $getLine('34000000', false, $debugMsg . 'right offset missed');
        }

        $code[] = $getLine('04000000', false, $debugMsg);

    }

    static public function fromGameVar($mapped, &$code, \Closure $getLine){
        $debugMsg = sprintf('[fromGameVar] ');
        $code[] = $getLine('1e000000', false, $debugMsg);
//        $code[] = $getLine($mapped['offset'], false, $debugMsg);
        //todo hier muss der offset verbaut werden
        $code[] = $getLine('34000000', false, $debugMsg . 'right offset missed');
        $code[] = $getLine('04000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);
    }





    //verwendet t_var vec3d; t_var stringarray; fromObject;  t_string
    static public function fromFineANameforMeTodo($mapped, &$code, \Closure $getLine){
        $debugMsg = sprintf('[fromFineANameforMeTodo] ');

        $code[] = $getLine($mapped['section'] == "header" ? '21000000' : '22000000');

        $code[] = $getLine('04000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);
        $code[] = $getLine($mapped['offset'], false, $debugMsg);

    }

    // gehört zu der return code familie
    // wird am ende von conditions verwendet
    //

    static public function fromFinedANameforMeTodoSecond($mapped, &$code, \Closure $getLine){

        $debugMsg = sprintf('[fromFinedANameforMeTodoSecond] ');

        $code[] = $getLine($mapped['section'] == "header" ? '14000000' : '13000000');

        $code[] = $getLine('01000000', false, $debugMsg);
        $code[] = $getLine('04000000', false, $debugMsg);
        $code[] = $getLine($mapped['offset'], false, $debugMsg);

    }

    static public function fromFinedANameforMeTodoSecondAgain($mapped, &$code, \Closure $getLine){

        $debugMsg = sprintf('[fromFinedANameforMeTodoSecond] ');

        $code[] = $getLine($mapped['section'] == "header" ? '14000000' : '13000000');

        $code[] = $getLine('02000000', false, $debugMsg);
        $code[] = $getLine('04000000', false, $debugMsg);
        $code[] = $getLine($mapped['offset'], false, $debugMsg);

    }

    static public function fromFinedANameforMeTodoThird($mapped, &$code, \Closure $getLine){
        $debugMsg = sprintf('[fromFinedANameforMeTodoSecond] ');

        $code[] = $getLine($mapped['section'] == "header" ? '16000000' : '15000000');

        $code[] = $getLine('04000000', false, $debugMsg);
        $code[] = $getLine($mapped['offset'], false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);

    }

    static public function fromObject($mapped, &$code, \Closure $getLine){

        self::fromFineANameforMeTodo($mapped, $code, $getLine);

        self::regularReturn($code, $getLine);
    }

    static public function fromAttribute($mapped, &$code, \Closure $getLine){
        $debugMsg = sprintf('[fromAttribute] ');
        $code[] = $getLine('0f000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);

        $code[] = $getLine('32000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);

        $code[] = $getLine($mapped['offset'], false, $debugMsg . 'offset');

        //TODO: das return gehört hier garnicht hin
        Evaluate::regularReturn($code, $getLine);

    }

    /**
     * @deprecated
     */
    static public function fromObjectAttribute($mapped, &$code, \Closure $getLine){

        self::fromObject([
            'offset' => $mapped['object']['offset'],
            'section' => $mapped['section']
        ], $code, $getLine);

        if ($mapped['offset'] != $mapped['object']['offset']){

            self::fromAttribute($mapped, $code, $getLine);
        }
    }


    static public function toObject( &$code, \Closure $getLine){
        $debugMsg = sprintf('[T_ASSIGN] toObject ');

        $code[] = $getLine('0f000000', false, $debugMsg);
        $code[] = $getLine('02000000', false, $debugMsg);

        $code[] = $getLine('17000000', false, $debugMsg);
        $code[] = $getLine('04000000', false, $debugMsg);
        $code[] = $getLine('02000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);
    }

    static public function toVec3D( &$code, \Closure $getLine){
        $debugMsg = sprintf('[T_ASSIGN] toVec3D ');

        self::readPosition(12, $code, $getLine);

        $code[] = $getLine('0f000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);

        self::returnCache($code, $getLine);
        $code[] = $getLine('44000000', false, $debugMsg);
    }




    static public function toHeaderStringArray( $offset, $size, &$code, \Closure $getLine){

        $debugMsg = sprintf('[T_ASSIGN] toHeaderStringArray ');

        //define target offset
        $code[] = $getLine('21000000', false, $debugMsg);
        $code[] = $getLine('04000000', false, $debugMsg);
        $code[] = $getLine('04000000', false, $debugMsg);
        $code[] = $getLine( $offset, false, $debugMsg . 'offset' );

        //define the length
        self::readPosition($size, $code, $getLine);

        //forward the result
        self::forward($code, $getLine);

        // save result
        $code[] = $getLine('10000000', false, $debugMsg);
        $code[] = $getLine('03000000', false, $debugMsg);
        $code[] = $getLine('48000000', false, $debugMsg);
    }








    static public function readIndex($index, &$code, \Closure $getLine){
        $debugMsg = sprintf('[readIndex] ' . $index);

        $code[] = $getLine('12000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);
        $code[] = $getLine( strlen($index) != 8 ? Helper::fromIntToHex($index) : $index, false, $debugMsg );

    }

    static public function readObject($size, &$code, \Closure $getLine){
        $debugMsg = sprintf('[readObject] ' . $size);

        $code[] = $getLine('12000000', false, $debugMsg);
        $code[] = $getLine('02000000', false, $debugMsg);
        $code[] = $getLine( Helper::fromIntToHex($size), false, $debugMsg );

        self::stringReturn($code, $getLine);
    }

    static public function readPosition($size, &$code, \Closure $getLine){
        $debugMsg = sprintf('[readPosition] ' . $size);

        $code[] = $getLine('12000000', false, $debugMsg);
        $code[] = $getLine('03000000', false, $debugMsg);
        $code[] = $getLine( Helper::fromIntToHex($size), false, $debugMsg );

    }

    static public function readArray($size, &$code, \Closure $getLine){
        $debugMsg = sprintf('[readPosition] ' . $size);

        $code[] = $getLine('12000000', false, $debugMsg);
        $code[] = $getLine('04000000', false, $debugMsg);
        $code[] = $getLine( Helper::fromIntToHex($size), false, $debugMsg );

    }








    static public function reserveBytes($size, &$code, \Closure $getLine){
        $debugMsg = sprintf('[reserveBytes] ' . $size);

        $code[] = $getLine('34000000', false, $debugMsg);
        $code[] = $getLine('09000000', false, $debugMsg);
        $code[] = $getLine( Helper::fromIntToHex($size), false, $debugMsg );

    }

    static public function reserveBytesUnknown($size, &$code, \Closure $getLine){
        $debugMsg = sprintf('[reserveBytes] ' . $size);

        $code[] = $getLine('32000000', false, $debugMsg);
        $code[] = $getLine('09000000', false, $debugMsg);
        $code[] = $getLine( Helper::fromIntToHex($size), false, $debugMsg );

    }

    static public function negate( $type, &$code, \Closure $getLine){
        $debugMsg = '[negate] ' . $type;

        if ($type == Token::T_FLOAT) {

            $code[] = $getLine('4f000000', false, $debugMsg);

            self::reserveBytesUnknown(4, $code, $getLine);

        }else if ($type == Token::T_INT){

            // * -1 ?
            $code[] = $getLine('2a000000', false, $debugMsg);
            $code[] = $getLine('01000000', false, $debugMsg);
        }
    }


    static public function scriptStart( &$code, \Closure $getLine){
        $debugMsg = "[T_SCRIPT] START ";

        $code[] = $getLine('10000000', false, $debugMsg);
        $code[] = $getLine('0a000000', false, $debugMsg);
        $code[] = $getLine('11000000', false, $debugMsg);
        $code[] = $getLine('0a000000', false, $debugMsg);
        $code[] = $getLine('09000000', false, $debugMsg);

    }

    static public function scriptEnd($type, $offset, &$code, \Closure $getLine){
        $debugMsg = "[T_SCRIPT] END ";

        $code[] = $getLine('11000000', false, $debugMsg);
        $code[] = $getLine('09000000', false, $debugMsg);
        $code[] = $getLine('0a000000', false, $debugMsg);
        $code[] = $getLine('0f000000', false, $debugMsg);
        $code[] = $getLine('0a000000', false, $debugMsg);

        if ($type == Token::T_SCRIPT) {
            $code[] = $getLine('3b000000', false, $debugMsg);
        }else if ($type == Token::T_CUSTOM_FUNCTION){
            $code[] = $getLine('3a000000', false, $debugMsg);
        }

        $code[] = $getLine($offset, false, $debugMsg);

    }



}