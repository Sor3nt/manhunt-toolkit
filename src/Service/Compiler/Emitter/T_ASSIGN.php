<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Compiler\Evaluate;
use App\Service\Helper;
use App\Service\Compiler\Token;

class T_ASSIGN {


    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){


        $debugMsg = sprintf('[T_ASSIGN] map ' . $node['value']);

        $code = [];
        $mapped = T_VARIABLE::getMapping($node, $data);

        $leftHand = $node['body'][0];

        //we do here some math...
        $rightHandNew = $node['body'][0];
        if (isset($node['body'][2])) {
            $rightHandNew = $node['body'][2];
        }

        $rightHandNewMapped = false;

        if ($rightHandNew['type'] == Token::T_FUNCTION || $rightHandNew['type'] == Token::T_VARIABLE){
            try{
                $rightHandNewMapped = T_VARIABLE::getMapping($rightHandNew, $data);
            }catch(\Exception $e){

            }
        }


        if (isset($data['customData']['customFunctions'][$node['value']])){
            Evaluate::fromCustomFunction($node['value'], $code, $getLine);
        }

        //HACK
        //when we have a type usage, we have no variable entry
        //so the compiler think its a function...
        if ($leftHand['type'] == Token::T_FUNCTION ){

            $stateVar = $mapped['objectType'];

            if (isset($data['types'][$stateVar])){
                $leftHand['target'] = $stateVar;
                $leftHand['type'] = Token::T_VARIABLE;
            }
        }

        $mappedRecord = false;

        if ($rightHandNewMapped && isset($rightHandNewMapped['isArg']) && $rightHandNewMapped['isArg']) {

        }else if ($mapped['type'] == Token::T_VEC3D){
            Evaluate::fromObject($mapped, $code, $getLine, $node['value']);

//        }else if ($mapped['type'] == 'entityptr'){
//            Evaluate::fromObject($mapped, $code, $getLine, $node['value']);

        }else if($mapped['type'] == "object"){
            Evaluate::fromObjectAttribute($mapped, $code, $getLine, $node['value']);

        }else if($mapped['type'] == "array"){

            Evaluate::fromObject($mapped, $code, $getLine, $node['value']);

            $indexName = explode('[', $node['value'])[1];
            $indexName = explode(']', $indexName)[0];

            $ofVarSize = 4;

            switch ($mapped['ofVar']){
                case 'integer':
                case 'boolean':

                    Evaluate::readIndex((int) $indexName, $code, $getLine);
                    break;
                default:

                    //we access a record
                    if (strpos($node['value'], ".") !== false){
                        $mappedRecord = T_VARIABLE::getMapping(
                            ['value' => strtolower($mapped['ofVar'])],
                            $data
                        );

                        $ofVarSize = Helper::calcTypeSize($mappedRecord);

                        $wantedVariable = strtolower(explode('.', $node['value'])[1]);
                        $mappedRecord = $mappedRecord[$wantedVariable];

                        Evaluate::fromFinedANameforMeTodoSecond([
                            'section' => 'script',
                            'offset' => Helper::fromIntToHex($mappedRecord['size'])
                        ], $code, $getLine);

                    }else{
                        throw new \Exception('T_ASSIGN: array Handler missed for ' . $mapped['ofVar']);
                    }
            }


            $code[] = $getLine('34000000', false, $debugMsg);
            $code[] = $getLine('01000000', false, $debugMsg);
            $code[] = $getLine('01000000', false, $debugMsg);

            Evaluate::readArray($ofVarSize, $code, $getLine);

            $code[] = $getLine('35000000', false, $debugMsg);
            $code[] = $getLine('04000000', false, $debugMsg);

            Evaluate::returnCache($code, $getLine);

            $code[] = $getLine('31000000', false, $debugMsg);
            $code[] = $getLine('04000000', false, $debugMsg);
            $code[] = $getLine('01000000', false, $debugMsg);

            Evaluate::forward($code, $getLine);

            if ($mappedRecord['index'] > 0){
                Evaluate::fromAttribute($mappedRecord, $code, $getLine);
            }

        }

        /**
         * Evaluate the left hand
         */

        Evaluate::emit($leftHand, $code, $emitter, $debugMsg );

        //we do here some math. [token] [operator] [token]
        if (count($node['body']) == 3){

            list(, $operator, $rightHand) = $node['body'];

            Evaluate::regularReturn($code, $getLine);

            /**
             * Evaluate the right hand
             */
            Evaluate::emit($rightHand, $code, $emitter, $debugMsg);

            if ($rightHand['type'] == Token::T_INT) {
                Evaluate::setIntMathOperator($operator['type'], $code, $getLine);


            }else if ($rightHand['type'] == Token::T_FLOAT){
                Evaluate::setFloatMathOperator($operator['type'], $code, $getLine);


            }else if (
                $rightHand['type'] == Token::T_FUNCTION ||
                $rightHand['type'] == Token::T_VARIABLE
            ){

                $rightMapped = T_VARIABLE::getMapping($rightHand, $data);

                Evaluate::emit($rightMapped, $code, $emitter, $debugMsg . ' function/variable ');

                //todo: int math missed
                Evaluate::setFloatMathOperator($operator['type'], $code, $getLine);

            }else{
                throw new \Exception(sprintf('T_ASSIGN: rightHand operator not supported: %s', $rightHand['type']));
            }
        }else if ($node['body'][0]['type'] == Token::T_BRACKET_OPEN){
            $result = self::handleBracketOpen($node['body'], $getLine, $emitter);
            foreach ($result as $item) {
                $item->debug = $debugMsg . ' _eval_3_ '. $item->debug;
                $code[] = $item;
            }
        }

        /*
         * Assign TO variable handling
         */
        if ($mapped['type'] == Token::T_VEC3D) {
            Evaluate::toVec3D($code, $getLine);
        }else if (
            $mapped['type'] == "object" ||
            $mapped['type'] == "custom_functions" ||
            $mapped['type'] == "array"
        ){
            Evaluate::toObject( $code, $getLine);

        }else if ($mapped['objectType'] == Token::T_STRING_ARRAY){
            Evaluate::toHeaderStringArray($mapped, $mapped['offset'], $mapped['size'], $code, $getLine);

        }else if($mapped['isGameVar']) {
            Evaluate::toGameVar( $node, $code, $getLine);

        }else if($mapped['isLevelVar']) {
            Evaluate::toLevelVar($mapped['offset'], $code, $getLine);
        }else {
            Evaluate::toNumeric($mapped, $code, $getLine);
        }

        return $code;
    }

    static function handleBracketOpen($params, \Closure $getLine, \Closure $emitter, $operator = false, $nested = false ){
        $debugMsg = "[T_ASSIGN] handleBracketOpen ";

        $code = [];

        $current = 0;

        $lastOperation = false;


        while($current < count($params)) {
            $node = $params[$current];

            if ($node['type'] == Token::T_BRACKET_OPEN) {


                $result = self::handleBracketOpen($node['params'], $getLine, $emitter, $operator, $node['nested']);
                foreach ($result as $item) {
                    $item->debug = $debugMsg . ' _eval_1_ ' . $item->debug;
                    $code[] = $item;
                }


                if($current + 1 != count($params)){
//                    var_dump($node);
                    Evaluate::regularReturn($code, $getLine, ' after ');
                }

//                if(isset($params[$current ])) var_dump($params[$current ]);

            }else{

                if (
                    $node['type'] == Token::T_MULTIPLY ||
                    $node['type'] == Token::T_ADDITION
                ){
                    $lastOperation = $node;

                }

                $result = $emitter($node, true, [ ]);
                foreach ($result as $item) {
                    $item->debug = $debugMsg . ' _eval_2_ '. $item->debug;
                    $code[] = $item;
                }

                //todo: looks like a hack.... *g*

//                var_dump($node, $nested, "\n\n");

                if ($node['type'] == Token::T_MULTIPLY) {

                    Evaluate::regularReturn($code, $getLine, $debugMsg . ' inner ');

                }

            }
            $current++;
        }

        if ($lastOperation !== false ){

            Evaluate::setFloatMathOperator($lastOperation['type'], $code, $getLine);
        }



//            var_dump($lastOperation);
        return $code;
    }
}