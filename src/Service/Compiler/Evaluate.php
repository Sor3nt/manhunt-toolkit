<?php
namespace App\Service\Compiler;

use App\Bytecode\Helper;
use App\Service\Compiler\FunctionMap\Manhunt2;

class Evaluate {


    /**
     * process commands
     *
     */
    static public function processAssign( $node, &$code, \Closure $getLine,\Closure $emitter, $data ){

        $code = [];

        /**
         * when the variable is not found, check if its an object
         */
        if (!isset($data['variables'][$node['value']])){

            if (strpos($node['value'], '.') !== false){

                $mapped = Evaluate::getObjectToAttributeSplit(array_merge($data, [
                    'conditionVariable' => [ 'value' => $node['value'] ]
                ]));
            }else{
                throw new \Exception(sprintf('T_ASSIGN: unable to detect variable: %s', $node['value']));
            }
        }else{
            $mapped = $data['variables'][$node['value']];
        }


        switch (count($node['body'])){
            // something like val := val + 1
            case 3:

                $resultCode = self::handleSimpleMath($node['body'], $getLine, $emitter, $data);
                foreach ($resultCode as $line) {
                    $code[] = $line;
                }

                break;


            case 1:
            break;

            default:
                throw new \Exception(sprintf('Unable to handle Assignment'));

                break;
        }

        /**
         * handle the target variable
         */
        switch ($mapped['section']){

            case 'header':

                switch (strtolower($mapped['type'])){
                    case 'boolean':

                        Evaluate::initializeParameterInteger($code, $getLine);

                        //evaluate the boolean
                        $resultCode = $emitter($node['body'][0]);
                        foreach ($resultCode as $line) {
                            $code[] = $line;
                        }

                        Evaluate::assignToUnknownInteger($mapped['offset'], $code, $getLine);

                        break;
                    case 'level_var tlevelstate':
                        if (isset($data['types'][$node['value']])){

                            $variableType = $data['types'][$node['value']];
                            $type = $variableType[$node['body'][0]['value']];

                            Evaluate::initializeParameterInteger($code, $getLine);

                            $code[] = $getLine($type['offset']);

                            Evaluate::assignToLevelVar($mapped['offset'], $code, $getLine);
                        }else{
                            throw new \Exception(sprintf('T_ASSIGN: level_var tLevelState type not found: %s  '), $node['value']);

                        }

                        break;
                    case 'level_var boolean':

                        self::initializeParameterInteger($code, $getLine);

                        //evaluate the integer
                        $resultCode = $emitter($node['body'][0]);
                        foreach ($resultCode as $line) {
                            $code[] = $line;
                        }

                        self::assignToLevelVar($mapped['offset'],  $code, $getLine);

                        break;

                    case 'level_var integer':
                        self::assignToLevelVar($mapped['offset'], $code, $getLine);
                        break;

                    case 'integer':
                        if ($node['body'][0]['type'] == Token::T_FUNCTION) {

                            $resultCode = $emitter($node['body'][0]);
                            foreach ($resultCode as $line) {
                                $code[] = $line;
                            }

                            self::assignToUnknownInteger($mapped['offset'], $code, $getLine);

                        }else {
                            self::assignToHeaderInteger($mapped['offset'], $code, $getLine);
                        }
                        break;
                    case 'stringarray':

                        if ($node['body'][0]['type'] == Token::T_FUNCTION) {

                            //evaluate the function call
                            $resultCode = $emitter($node['body'][0]);
                            foreach ($resultCode as $line) {
                                $code[] = $line;
                            }

                            self::assignToUnknownStringArray($mapped, $code, $getLine);

                            return $code;

                        } else {
                            self::assignToHeaderStringArray($mapped['offset'], $code, $getLine);
                        }

                        break;
                    default:
                        throw new \Exception(sprintf("Header assignment for %s is not implemented", $mapped['type']));
                }

                break;

            case 'script':
                switch (strtolower($mapped['type'])){
                    case 'boolean':

                        Evaluate::initializeParameterInteger($code, $getLine);

                        //evaluate the boolean
                        $resultCode = $emitter($node['body'][0]);
                        foreach ($resultCode as $line) {
                            $code[] = $line;
                        }

                        if ($node['body'][0]['type'] == Token::T_FUNCTION) {

                            Evaluate::assignToUnknownInteger($mapped['offset'], $code, $getLine);

                        }else{

                            Evaluate::assignToScriptInteger($mapped['offset'], $code, $getLine);

                        }


                        break;

                    # so far known only vec3d childs (x,y,z) are object
                    case 'object':

                        Evaluate::initializeReadScriptString($code, $getLine);
                        $code[] = $getLine($mapped['object']['offset']);
                        Evaluate::returnResult($code, $getLine);

                        if ($mapped['offset'] != $mapped['object']['offset']){

                            //hmm ? doppelte bedeutung ?
                            Evaluate::returnObjectResult($code, $getLine);


                            $code[] = $getLine('32000000');
                            $code[] = $getLine('01000000');

                            $code[] = $getLine($mapped['offset']);
                        }

                        Evaluate::returnResult($code, $getLine);
                        Evaluate::initializeParameterInteger($code, $getLine);

                        //evaluate the integer
                        $resultCode = $emitter($node['body'][0]);
                        foreach ($resultCode as $line) {
                            $code[] = $line;
                        }

                        Evaluate::returnObjectAttributeResult($code, $getLine);

                        $code[] = $getLine('17000000');
                        $code[] = $getLine('04000000');
                        $code[] = $getLine('02000000');
                        $code[] = $getLine('01000000');

                        break;
                    case 'vec3d':

                        //evaluate the function call
                        $resultCode = $emitter($node['body'][0]);
                        foreach ($resultCode as $line) {
                            $code[] = $line;
                        }

                        Evaluate::assignToScriptVec3d($mapped['offset'], $code, $getLine);
                        break;
                    case 'integer':
                        if ($node['body'][0]['type'] == Token::T_FUNCTION) {
                            $resultCode = $emitter($node['body'][0]);
                            foreach ($resultCode as $line) {
                                $code[] = $line;
                            }

                            Evaluate::assignToScriptInteger($mapped['offset'], $code, $getLine);

                        }else{

                            self::assignToScriptInteger($mapped['offset'], $code, $getLine);
                        }
                        break;
                    default:
                        throw new \Exception(sprintf("Script assignment for %s is not implemented", $mapped['type']));
                }

                break;

            default:
                throw new \Exception(sprintf("T_FUNCTION: section unknown %s", $mapped['section']));

        }

    }


    static public function processString( $node, &$code, \Closure $getLine, $data ){

        // we have quotes around the string, come from the tokenizer
        $value = substr($node['value'], 1, -1);

        if (!isset($data['strings'][$value])){
            throw new \Exception(sprintf('Evaluate: string =>  %s is not in the map !', $value));
        }

        // when this is false, we are in precalc mode so we dont want to fetch the real value
        if ($data['calculateLineNumber']){
            $code[] = $getLine($data['strings'][$value]['offset']);
        }else{
            $code[] = $getLine("12345678");
        }
    }

    static public function processNumeric( $node, &$code, $data, \Closure $getLine, \Closure $emitter, $doReturn = true ){


        self::initializeParameterInteger($code, $getLine);

        $resultCode = $emitter( $node );
        foreach ($resultCode as $line) {
            $code[] = $line;
        }

        if (isset($data['conditionVariable']) && $data['conditionVariable']['type'] == Token::T_VARIABLE) {

            $mapped = self::getVariableMap(
                $data['conditionVariable']['value'],
                $code,
                $data,
                $getLine,
                $emitter
            );

            if ($mapped === false) return false;

            switch ($mapped['section']) {

                case 'level_var':
                    self::returnConstantResult($code, $getLine);
                    break;

                case 'script':
                    switch ($mapped['type']) {

                        case 'object':

                            Evaluate::returnResult($code, $getLine);


                            break;
                        default:
                            throw new \Exception(sprintf("handling incomplete for type %s", $mapped['type']));
                    }

                    break;

                case 'header':

                    switch ($mapped['type']) {

                        case 'boolean':

                            //while vs if ...

                            if ($data['customData']['isWhile']) {
                                Evaluate::returnResult($code, $getLine);

                            } else {
                                Evaluate::returnConstantResult($code, $getLine);
                            }

                            break;
                        case 'integer':
                            Evaluate::returnResult($code, $getLine);
                            break;
                        default:
                            throw new \Exception(sprintf("handling incomplete for type %s", $mapped['type']));
                    }

                    break;

                default:
                    throw new \Exception(sprintf("handling incomplete for section %s", $mapped['section']));
            }


        }else if (isset($data['conditionVariable']) && $data['conditionVariable']['type'] == Token::T_FUNCTION){
            Evaluate::returnConstantResult($code, $getLine);

        }else if (isset($data['conditionVariable'])){
            throw new \Exception(sprintf("handling incomplete for conditionVariable %s", $data['conditionVariable']['type']));
        }else{
            Evaluate::returnResult($code, $getLine);
        }

        return true;
    }

    static public function processVariable( $node, &$code, $data, \Closure $getLine, \Closure $emitter ){

        $mapped = self::getVariableMap(
            $node['value'],
            $code,
            $data,
            $getLine,
            $emitter
        );

        if ($mapped === false) return false;

        $calledFromFunction = false;
        if (
            isset($data['customData']['type']) &&
            $data['customData']['type'] == Token::T_FUNCTION
        ){
            $calledFromFunction = true;
        }


        switch ($mapped['section']){

            //todo: das sollte es nicht geben, gehört zur header section
            case 'level_var':
                Evaluate::initializeReadLevelVar($code, $getLine);

                // define the offset
                $code[] = $getLine($mapped['offset']);

                Evaluate::returnLevelVarResult($code, $getLine);

                break;
            case 'constant':
                self::initializeParameterInteger($code, $getLine);

                // define the offset
                $code[] = $getLine($mapped['offset']);

                //use as function parameter
                if (isset($data['customData']['type']) && $data['customData']['type'] == Token::T_FUNCTION){
                    self::returnResult($code, $getLine);
                }else{
                    self::returnConstantResult($code, $getLine);

                }

                break;

            case 'script constant':
                Evaluate::initializeReadHeaderString($code, $getLine);
                $code[] = $getLine($mapped['offset']);

                Evaluate::initializeParameterString($code, $getLine);

                $code[] = $getLine(Helper::fromIntToHex( $mapped['length'] + 1));

                Evaluate::returnResult($code, $getLine);
                Evaluate::returnStringResult($code, $getLine);
                break;

            case 'header':

                switch (strtolower($mapped['type'])){


                    case 'level_var integer':
                        self::initializeReadLevelVar($code, $getLine);

                        // define the offset
                        $code[] = $getLine($mapped['offset']);

                        self::returnLevelVarResult($code, $getLine);
                        self::returnResult($code, $getLine);
                        break;

                    case 'integer':
                        self::initializeReadHeaderInteger($code, $getLine);
                        $code[] = $getLine($mapped['offset']);
                        self::returnResult($code, $getLine);
                        break;
                    case 'boolean':
                        self::initializeReadHeaderBoolean($code, $getLine);
                        $code[] = $getLine($mapped['offset']);
                        //todo: hmm er braucht das return jedoch wird das von wo anders bereits gefüllt
//                        self::returnResult($code, $getLine);
                        break;
                    case 'stringarray':
                        Evaluate::initializeReadHeaderString($code, $getLine);
                        $code[] = $getLine($mapped['offset']);

                        self::initializeParameterString($code, $getLine);
                        $code[] = $getLine(Helper::fromIntToHex($mapped['size']));
                        Evaluate::returnResult($code, $getLine);
                        Evaluate::returnStringResult($code, $getLine);

                        break;
                    case 'vec3d':
                        Evaluate::initializeReadHeaderString($code, $getLine);
                        $code[] = $getLine($mapped['offset']);
                        Evaluate::returnResult($code, $getLine);
                        break;
                    default:
                        throw new \Exception(sprintf("Unknown header type %s", $mapped['type']));
                }

                break;

            case 'script':

                switch (strtolower($mapped['type'])){
                    case 'integer':
                        self::initializeReadHeaderInteger($code, $getLine);

                        // define the offset
                        $code[] = $getLine($mapped['offset']);

                        Evaluate::returnResult($code, $getLine);

                        break;

                    case 'boolean':
                        throw new \Exception("You can not assign a boolena variable to a function!");
                        break;

                    case 'object':

                        if ($mapped['offset'] == $mapped['object']['offset']){
                            self::initializeReadScriptString($code, $getLine);

                            $code[] = $getLine($mapped['offset']);

                            self::returnResult($code, $getLine);

                            $code[] = $getLine('0f000000');
                            $code[] = $getLine('02000000');
                            $code[] = $getLine('18000000');
                            $code[] = $getLine('01000000');
                            $code[] = $getLine('04000000');
                            $code[] = $getLine('02000000');
                        }else{
                            $code[] = $getLine('0f000000');
                            $code[] = $getLine('04000000');
                            $code[] = $getLine('44000000');
                            $code[] = $getLine('22000000');
                            $code[] = $getLine('04000000');
                            $code[] = $getLine('01000000');

                            $code[] = $getLine($mapped['object']['offset']);

                            Evaluate::returnResult($code, $getLine);

                            if (isset($mapped['offset'])){

                                //hmm ? doppelte bedeutung ?
                                Evaluate::returnObjectResult($code, $getLine);

                                $code[] = $getLine('32000000');
                                $code[] = $getLine('01000000');

                                $code[] = $getLine($mapped['offset']);



                                Evaluate::returnResult($code, $getLine);


                                $code[] = $getLine('0f000000');
                                $code[] = $getLine('02000000');
                                $code[] = $getLine('18000000');
                                $code[] = $getLine('01000000');

                                $code[] = $getLine($mapped['offset']);

                                $code[] = $getLine('02000000');

                                Evaluate::returnResult($code, $getLine);
                            }
                        }


//                        Evaluate::initializeParameterInteger($code, $getLine);



                        break;
                    case 'entityptr':

                        $code[] = $getLine('13000000');
                        $code[] = $getLine('01000000');
                        $code[] = $getLine('04000000');

                        $code[] = $getLine($mapped['offset']);

                        if ($calledFromFunction){
                            self::returnResult($code, $getLine);

                        }else{
                            self::returnConstantResult($code, $getLine);
                        }


                        break;
                    case 'vec3d':
                        Evaluate::initializeReadScriptString($code, $getLine);
                        $code[] = $getLine($mapped['offset']);
                        Evaluate::returnResult($code, $getLine);

                        break;

                    default:
                        throw new \Exception(sprintf("Unknown Script type %s", $mapped['type']));
                }

                break;

            default:
                throw new \Exception(sprintf("Unknown section type %s", $mapped['section']));
        }


        return true;
    }


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

    static public function initializeReadScriptString( &$code, \Closure $getLine ){

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


    static public function initializeStatementFloat( &$code, \Closure $getLine ){
        $code[] = $getLine('4e000000');
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

    static public function returnObjectResult( &$code, \Closure $getLine ){
        $code[] = $getLine('0f000000');
        $code[] = $getLine('01000000');
    }

    static public function returnObjectAttributeResult( &$code, \Closure $getLine ){
        $code[] = $getLine('0f000000');
        $code[] = $getLine('02000000');
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


    /**
     * assign functions
     */
    static public function assignToHeaderStringArray($offset, &$code, \Closure $getLine ){
        Evaluate::initializeParameterInteger($code, $getLine);

        $code[] = $getLine($offset);

        Evaluate::returnResult($code, $getLine);
    }


    static public function assignToLevelVar($offset, &$code, \Closure $getLine ){

        $code[] = $getLine('1a000000');
        $code[] = $getLine('01000000');

        $code[] = $getLine($offset);

        $code[] = $getLine('04000000');
    }

    static public function assignToScriptInteger($offset, &$code, \Closure $getLine ){

        $code[] = $getLine('15000000');
        $code[] = $getLine('04000000');

        $code[] = $getLine($offset);

        $code[] = $getLine('01000000');
    }

    static public function assignToUnknownInteger($offset, &$code, \Closure $getLine ){

        $code[] = $getLine('16000000');
        $code[] = $getLine('04000000');

        $code[] = $getLine($offset);

        $code[] = $getLine('01000000');
    }

    static public function assignToHeaderInteger($offset, &$code, \Closure $getLine ){

        $code[] = $getLine('11000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('04000000');

        $code[] = $getLine('15000000');
        $code[] = $getLine('04000000');

        // define the offset
        $code[] = $getLine($offset);

        $code[] = $getLine('01000000');

    }

    static public function assignToScriptVec3d($offset, &$code, \Closure $getLine ){

        $code[] = $getLine('12000000');
        $code[] = $getLine('03000000');
        // define the offset
        $code[] = $getLine($offset);

        $code[] = $getLine('0f000000');

    }

    static public function assignToUnknownStringArray($mapped, &$code, \Closure $getLine ){

        Evaluate::initializeReadHeaderStringArray($code, $getLine);

        $code[] = $getLine($mapped['offset']);

        Evaluate::returnStringArrayResult($code, $getLine);


        $code[] = $getLine(Helper::fromIntToHex($mapped['size']));

        $code[] = $getLine('10000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('10000000');
        $code[] = $getLine('03000000');
        $code[] = $getLine('48000000');


    }


    /**
     * Other functions
     */
    static public function getVariableMap( $value, &$code, $data, \Closure $getLine, \Closure $emitter ){

        if (isset(Manhunt2::$functions[ strtolower($value) ])) {

            // mismatch, some function has no params and looks loke variables
            // just redirect to the function handler
            $result = $emitter( [
                'type' => Token::T_FUNCTION,
                'value' => $value
            ] );

            foreach ($result as $item) {
                $code[] = $item;
            }
//            Evaluate::returnResult($code, $getLine);


            return false;
        }else if (isset(Manhunt2::$constants[ $value ])) {
            $mapped = Manhunt2::$constants[ $value ];
            $mapped['section'] = "constant";

        }else if (isset(Manhunt2::$levelVarBoolean[ $value ])) {
            $mapped = Manhunt2::$levelVarBoolean[ $value ];
            $mapped['section'] = "level_var";

        }else if (isset($data['const'][ $value ])){
            $mapped = $data['const'][ $value ];
            $mapped['section'] = "script constant";

        }else if (isset($data['variables'][ $value ])){
            $mapped = $data['variables'][ $value ];

        }else{

            // we have a object notation here
            if (strpos($value, '.') !== false){
                if (isset($data['conditionVariable'])){
                    $mapped = self::getObjectToAttributeSplit($data);
                }else{
                    throw new \Exception(sprintf("T_FUNCTION: (numeric) unable to find variable offset for %s", $value));

                }
            }else{
                throw new \Exception(sprintf("T_FUNCTION: (numeric) unable to find variable offset for %s", $value));

            }


        }

        return $mapped;
    }

    static public function getObjectToAttributeSplit( $data ){
        list($originalObject, $attribute) = explode('.', $data['conditionVariable']['value']);
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


    static public function handleSimpleMath( $node, \Closure $getLine, \Closure $emitter, $data ){

        $code = [];
        list($leftHand, $operator, $rightHand) = $node;

        if ($leftHand !== false){
            if ($leftHand['type'] == Token::T_VARIABLE){

                $mapped = Evaluate::processVariable(
                    $leftHand,
                    $code,
                    $data,
                    $getLine,
                    $emitter
                );

                if ($mapped === false) return $code;

//                Evaluate::returnResult($code, $getLine);
            }else{
                throw new \Exception(sprintf('T_ASSIGN: handleSimpleMath unknown leftHand: %s', $leftHand['type']));
            }
        }


        if ($rightHand['type'] == Token::T_INT || $rightHand['type'] == Token::T_FLOAT){

            Evaluate::initializeParameterInteger($code, $getLine);

            $resultCode = $emitter($rightHand);
            foreach ($resultCode as $line) {
                $code[] = $line;
            }

            Evaluate::returnConstantResult($code, $getLine);

        }else{
            throw new \Exception(sprintf('T_ASSIGN: handleSimpleMath unknown rightHand: %s', $rightHand['type']));
        }

        if ($operator['type'] == Token::T_ADDITION) {
            Evaluate::setStatementAddition($code, $getLine);
        }else if ($operator['type'] == Token::T_SUBSTRACTION){
            Evaluate::setStatementSubstraction($code, $getLine);
        }else{
            throw new \Exception(sprintf('T_ASSIGN: handleSimpleMath operator not supported: %s', $operator['type']));

        }


        return $code;
    }
}