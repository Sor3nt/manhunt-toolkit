<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Helper;
use App\Service\Compiler\Token;

class T_ASSIGN {


    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $debugMsg = sprintf('[T_ASSIGN] map ');

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



            //HACK
        //when we have a type usage, we have no variable entry
        //so the compiler think its a function...
        if ($leftHand['type'] == Token::T_FUNCTION ){

            if (isset($data['customData']['customFunctions'][strtolower($node['value'])])){
                $code[] = $getLine('10000000', false, $debugMsg . 'custom function call ' . strtolower($node['value']) . '(start)');
                $code[] = $getLine('02000000', false, $debugMsg . 'custom function call');
                $code[] = $getLine('11000000', false, $debugMsg . 'custom function call');
                $code[] = $getLine('02000000', false, $debugMsg . 'custom function call');
                $code[] = $getLine('0a000000', false, $debugMsg . 'custom function call');
                $code[] = $getLine('34000000', false, $debugMsg . 'custom function call');
                $code[] = $getLine('02000000', false, $debugMsg . 'custom function call');
                $code[] = $getLine('04000000', false, $debugMsg . 'custom function call');
                $code[] = $getLine('20000000', false, $debugMsg . 'custom function call');
                $code[] = $getLine('01000000', false, $debugMsg . 'custom function call');
                $code[] = $getLine('04000000', false, $debugMsg . 'custom function call');
                $code[] = $getLine('02000000', false, $debugMsg . 'custom function call');
                $code[] = $getLine('0f000000', false, $debugMsg . 'custom function call');
                $code[] = $getLine('02000000', false, $debugMsg . 'custom function call');
                $code[] = $getLine('10000000', false, $debugMsg . 'custom function call');
                $code[] = $getLine('01000000', false, $debugMsg . 'custom function call ' . strtolower($node['value']) . '(end)');
            }

            $stateVar = str_replace('level_var ', '', $mapped['type']);

            if (isset($data['types'][$stateVar])){
                $leftHand['target'] = $stateVar;
                $leftHand['type'] = Token::T_VARIABLE;
            }
        }

        $mappedRecord = false;
//var_dump($mapped);
//exit;
        if ($rightHandNewMapped && isset($rightHandNewMapped['isArg']) && $rightHandNewMapped['isArg']) {

            $code[] = $getLine('10030000', false, $debugMsg . 'argument init');
            $code[] = $getLine('24000000', false, $debugMsg . 'argument init');
            $code[] = $getLine('01000000', false, $debugMsg . 'argument init');
            $code[] = $getLine('00000000', false, $debugMsg . 'argument init');
            $code[] = $getLine('3f000000', false, $debugMsg . 'argument init');
            $code[] = $getLine('__END_OFFSET__', false, $debugMsg . 'argument end offset');
//
            $lastLineIndex = count($code) - 1;
//

            $code[] = $getLine('12000000', false, $debugMsg . 'read argument number...');
            $code[] = $getLine('01000000', false, $debugMsg . 'read argument number...');
            $code[] = $getLine(Helper::fromIntToHex($rightHandNewMapped['order']), false, $debugMsg . 'read argument number...');
            $code[] = $getLine('10000000', false, $debugMsg . 'read argument number...');
            $code[] = $getLine('01000000', false, $debugMsg . 'read argument number...');


            $code[] = $getLine('12000000', false, $debugMsg . 'read argument fallback...');
            $code[] = $getLine('01000000', false, $debugMsg . 'read argument fallback...');
            $code[] = $getLine('00000000', false, $debugMsg . 'read argument fallback (offset todo)...');
            $code[] = $getLine('10000000', false, $debugMsg . 'read argument fallback...');
            $code[] = $getLine('01000000', false, $debugMsg . 'read argument fallback...');


            $code[] = $getLine('0a030000', false, $debugMsg . 'read argument finish');


            $code[] = $getLine('15000000', false, $debugMsg . 'read argument unknown');
            $code[] = $getLine('04000000', false, $debugMsg . 'read argument unknown');
            $code[] = $getLine('04000000', false, $debugMsg . 'read argument unknown');
            $code[] = $getLine('01000000', false, $debugMsg . 'read argument unknown');


            $code[] = $getLine('0f030000', false, $debugMsg . 'read argument finish 2');

            $code[$lastLineIndex]->hex = Helper::fromIntToHex(count($code) - 1);

        }else if ($mapped['type'] == "vec3d"){
            self::fromObject($mapped, $code, $getLine);

        //Todo "object" also rename to objectAttribute ....
        }else if($mapped['type'] == "object"){
            self::fromObjectAttribute($mapped, $code, $getLine);

        }else if($mapped['type'] == "array"){

            $code[] = $getLine('21000000', false, $debugMsg . 'array (first)');
            $code[] = $getLine('04000000', false, $debugMsg . 'array');
            $code[] = $getLine('01000000', false, $debugMsg . 'array');

            $code[] = $getLine($mapped['offset'], false, $debugMsg . 'array offset');

            $code[] = $getLine('10000000', false, $debugMsg . 'array');
            $code[] = $getLine('01000000', false, $debugMsg . 'array (last)');

            $indexName = explode('[', $node['value'])[1];
            $indexName = explode(']', $indexName)[0];

            $ofVarSize = 4;

            switch ($mapped['ofVar']){
                case 'boolean':
                    //todo: change to evaluate
                    $code[] = $getLine('12000000', false, $debugMsg . 'boolean');
                    $code[] = $getLine('01000000', false, $debugMsg . 'boolean');
                    $code[] = $getLine(Helper::fromIntToHex( (int) $indexName), false, $debugMsg . 'boolean ' . $indexName);

                    break;
                default:

                    //we access a record
                    if (strpos($node['value'], ".") !== false){
                        $mappedRecord = T_VARIABLE::getMapping(
                            ['value' => strtolower($mapped['ofVar'])],
                            $data
                        );

                        $ofVarSize = 0;
                        foreach ($mappedRecord as $item) {
                            if ($item['type'] == "vec3d") $ofVarSize += 12;
                            else $ofVarSize += 4;
                        }

                        $wantedVariable = strtolower(explode('.', $node['value'])[1]);
                        $mappedRecord = $mappedRecord[$wantedVariable];


                        $code[] = $getLine('13000000', false, $debugMsg . '(default)');
                        $code[] = $getLine('01000000', false, $debugMsg . '(default)');
                        $code[] = $getLine('04000000', false, $debugMsg . '(default)');
                        $code[] = $getLine(Helper::fromIntToHex($mappedRecord['size']), false, $debugMsg . '(default) with size ' . $mappedRecord['size']);

                    }else{
                        throw new \Exception('T_ASSIGN: array Handler missed for ' . $mapped['ofVar']);
                    }
            }


            $code[] = $getLine('34000000', false, $debugMsg);
            $code[] = $getLine('01000000', false, $debugMsg);
            $code[] = $getLine('01000000', false, $debugMsg);
            $code[] = $getLine('12000000', false, $debugMsg);
            $code[] = $getLine('04000000', false, $debugMsg);

            $code[] = $getLine(Helper::fromIntToHex($ofVarSize), false, $debugMsg . ' size of ofVar ' . $ofVarSize);

            $code[] = $getLine('35000000', false, $debugMsg);
            $code[] = $getLine('04000000', false, $debugMsg);
            $code[] = $getLine('0f000000', false, $debugMsg);
            $code[] = $getLine('04000000', false, $debugMsg);
            $code[] = $getLine('31000000', false, $debugMsg);
            $code[] = $getLine('04000000', false, $debugMsg);
            $code[] = $getLine('01000000', false, $debugMsg);
            $code[] = $getLine('10000000', false, $debugMsg);
            $code[] = $getLine('04000000', false, $debugMsg);



            if ($mappedRecord['index'] > 0){
                $code[] = $getLine('0f000000', false, $debugMsg . 'access index ' . $mappedRecord['index']);
                $code[] = $getLine('01000000', false, $debugMsg . 'access index ');
                $code[] = $getLine('32000000', false, $debugMsg . 'access index ');
                $code[] = $getLine('01000000', false, $debugMsg . 'access index ');
                $code[] = $getLine($mappedRecord['offset'], false, $debugMsg . 'access index offset'); // offset
                $code[] = $getLine('10000000', false, $debugMsg . 'access index ');
                $code[] = $getLine('01000000', false, $debugMsg . 'access index ');
            }

//            var_dump($mapped);
//            exit;

        //hack: nil is detected as function, but its T_NIL actual....
        }else if ($leftHand['type'] == Token::T_FUNCTION && $leftHand['value'] == "nil"){
            $leftHand['type'] = Token::T_NIL;
        }

        /**
         * Evaluate the left hand
         */
        foreach ($emitter($leftHand) as $item){
            $item->debug = $debugMsg . ' ' . $item->debug;
            $code[] = $item;
        }

        //we do here some math...
        if (isset($node['body'][1]) && isset($node['body'][2])){

            $rightHand = $node['body'][2];
            $operator = $node['body'][1];

            $code[] = $getLine('10000000', false, $debugMsg);
            $code[] = $getLine('01000000', false, $debugMsg);

            /**
             * Evaluate the right hand
             */
            foreach ($emitter($rightHand) as $item){
                $item->debug = $debugMsg . ' ' . $item->debug;
                $code[] = $item;
            }

            if ($rightHand['type'] == Token::T_INT) {
                $code[] = $getLine('0f000000', false, $debugMsg . 'int');
                $code[] = $getLine('04000000', false, $debugMsg . 'int');

                if ($operator['type'] == Token::T_ADDITION) {

                    $code[] = $getLine('31000000', false, $debugMsg . 'int T_ADDITION');
                    $code[] = $getLine('01000000', false, $debugMsg . 'int T_ADDITION');
                    $code[] = $getLine('04000000', false, $debugMsg . 'int T_ADDITION');

                }else if ($operator['type'] == Token::T_SUBSTRACTION){

                    $code[] = $getLine('33000000', false, $debugMsg . 'int T_SUBSTRACTION');
                    $code[] = $getLine('04000000', false, $debugMsg . 'int T_SUBSTRACTION');
                    $code[] = $getLine('01000000', false, $debugMsg . 'int T_SUBSTRACTION');
                    $code[] = $getLine('11000000', false, $debugMsg . 'int T_SUBSTRACTION');
                    $code[] = $getLine('01000000', false, $debugMsg . 'int T_SUBSTRACTION');
                    $code[] = $getLine('04000000', false, $debugMsg . 'int T_SUBSTRACTION');
                }else{
                    throw new \Exception(sprintf('T_ASSIGN: handleSimpleMath operator not supported: %s', $operator['type']));
                }

            }else if ($rightHand['type'] == Token::T_FLOAT){
                self::applyFloatMath($operator['type'], $code, $getLine);


            }else if (
                $rightHand['type'] == Token::T_FUNCTION ||
                $rightHand['type'] == Token::T_VARIABLE
            ){

                $rightMapped = T_VARIABLE::getMapping($rightHand, $data);

                foreach ($emitter($rightMapped) as $item){
                    $item->debug = $debugMsg . ' function/variable ' . $item->debug;
                    $code[] = $item;
                }

                self::applyFloatMath($operator['type'], $code, $getLine);

            }else{
                throw new \Exception(sprintf('T_ASSIGN: rightHand operator not supported: %s', $rightHand['type']));
            }
        }

        /*
         * Assign TO variable handling
         */
        if ($mapped['type'] == "vec3d") {
            self::toVec3D($mapped['offset'], $code, $getLine);

        }else if ($mapped['type'] == "object"){
            self::toObject( $code, $getLine);

        }else if ($mapped['type'] == "array"){

            self::toObject( $code, $getLine);


        }else if ($mapped['type'] == "stringarray"){
            self::toHeaderStringArray( $mapped['offset'], $mapped['size'], $code, $getLine);

        }else if ($mapped['type'] == "custom_functions"){
            self::toCustomFunctions( $code, $getLine);

        }else if(substr($mapped['type'], 0, 9) == "level_var") {
            self::toLevelVar($mapped['offset'], $code, $getLine);

        }else if (isset($mapped['abstract']) && $mapped['abstract'] == "state"){
            self::toHeader( $mapped['offset'], $code, $getLine);

        //regular assignment
        }else if (isset($node['body'][1]) == false){

            if ($mapped['section'] == "header") self::toHeader( $mapped['offset'], $code, $getLine);
            if ($mapped['section'] == "script") self::toScript( $mapped['offset'], $code, $getLine);

        //math operation
        }else if (isset($node['body'][1]) == true){
            self::toScript($mapped['offset'], $code, $getLine);
        }else{
            throw new \Exception(sprintf('T_ASSIGN: unhandled assignment '));
        }

        return $code;
    }


    static public function applyFloatMath( $type, &$code, \Closure $getLine){
        $debugMsg = sprintf('[T_ASSIGN] applyFloatMath operation ' . $type);

        $code[] = $getLine('10000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);


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


    static public function toCustomFunctions( &$code, \Closure $getLine){
        $debugMsg = sprintf('[T_ASSIGN] toCustomFunctions ');

        self::toObject($code, $getLine);

        $code[] = $getLine('13000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);
        $code[] = $getLine('04000000', false, $debugMsg);
        $code[] = $getLine('04000000', false, $debugMsg); //offset?
    }

    static public function fromObject($mapped, &$code, \Closure $getLine){
        $debugMsg = sprintf('[T_ASSIGN] fromObject ');

        $code[] = $getLine($mapped['section'] == "header" ? '21000000' : '22000000');

        $code[] = $getLine('04000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);
        $code[] = $getLine($mapped['offset'], false, $debugMsg);

        $code[] = $getLine('10000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);
    }

    static public function fromObjectAttribute($mapped, &$code, \Closure $getLine){
        $debugMsg = sprintf('[T_ASSIGN] fromObjectAttribute ');

        self::fromObject([
            'offset' => $mapped['object']['offset'],
            'section' => $mapped['section']
        ], $code, $getLine);

        if ($mapped['offset'] != $mapped['object']['offset']){
            $code[] = $getLine('0f000000', false, $debugMsg);
            $code[] = $getLine('01000000', false, $debugMsg);

            $code[] = $getLine('32000000', false, $debugMsg);
            $code[] = $getLine('01000000', false, $debugMsg);

            $code[] = $getLine($mapped['offset'], false, $debugMsg . 'offset');

            $code[] = $getLine('10000000', false, $debugMsg);
            $code[] = $getLine('01000000', false, $debugMsg);
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

    static public function toVec3D( $offset, &$code, \Closure $getLine){
        $debugMsg = sprintf('[T_ASSIGN] toVec3D ');

        $code[] = $getLine('12000000', false, $debugMsg);
        $code[] = $getLine('03000000', false, $debugMsg);
        $code[] = $getLine( $offset , false, $debugMsg . 'offset');

        $code[] = $getLine('0f000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);
        $code[] = $getLine('0f000000', false, $debugMsg);
        $code[] = $getLine('04000000', false, $debugMsg);
        $code[] = $getLine('44000000', false, $debugMsg);

    }

    static public function toHeader( $offset, &$code, \Closure $getLine){
        $debugMsg = sprintf('[T_ASSIGN] toHeader ');

        $code[] = $getLine('16000000', false, $debugMsg);
        $code[] = $getLine('04000000', false, $debugMsg);
        $code[] = $getLine($offset, false, $debugMsg . 'offset');
        $code[] = $getLine('01000000', false, $debugMsg);
    }

    static public function toScript( $offset, &$code, \Closure $getLine){
        $debugMsg = sprintf('[T_ASSIGN] toScript ');

        $code[] = $getLine('15000000', false, $debugMsg);
        $code[] = $getLine('04000000', false, $debugMsg);
        $code[] = $getLine( $offset, false, $debugMsg . 'offset' );
        $code[] = $getLine('01000000', false, $debugMsg);
    }

    static public function toLevelVar( $offset, &$code, \Closure $getLine){
        $debugMsg = sprintf('[T_ASSIGN] toLevelVar ');

        $code[] = $getLine('1a000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);
        $code[] = $getLine( $offset, false, $debugMsg . 'offset' );
        $code[] = $getLine('04000000', false, $debugMsg);
    }

    static public function toHeaderStringArray( $offset, $size, &$code, \Closure $getLine){

        $debugMsg = sprintf('[T_ASSIGN] toHeaderStringArray ');

        //define target offset
        $code[] = $getLine('21000000', false, $debugMsg);
        $code[] = $getLine('04000000', false, $debugMsg);
        $code[] = $getLine('04000000', false, $debugMsg);
        $code[] = $getLine( $offset, false, $debugMsg . 'offset' );

        //define the length
        $code[] = $getLine('12000000', false, $debugMsg);
        $code[] = $getLine('03000000', false, $debugMsg);
        $code[] = $getLine( Helper::fromIntToHex($size), false, $debugMsg . 'size' );
        $code[] = $getLine('10000000', false, $debugMsg);
        $code[] = $getLine('04000000', false, $debugMsg);

        // save result
        $code[] = $getLine('10000000', false, $debugMsg);
        $code[] = $getLine('03000000', false, $debugMsg);
        $code[] = $getLine('48000000', false, $debugMsg);
    }
}