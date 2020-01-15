<?php

namespace App\Service\CompilerV2;

use App\Service\Compiler\Token;
use App\Service\Helper;
use Exception;

class Associations
{

    public $type = Tokens::T_UNKNOWN;

    /*
     * Regular Parts
     */

    public $value = null;

    /** @var Associations[] */
    public $childs = [];

    /** @var Associations */
    public $usedinFunction = null;
    public $size = null;
    public $offset = null;
    public $fallback = null;
    public $records = [];

    public $levelVarSize = null;
    public $isLevelVar = null;
    public $isGameVar = null;
    public $isCustomFunction = null;
    public $isProcedure = null;
    public $isLastWriteDebugParam = null;
    public $isArgument = null;

    public $section = null;

    /*
     * Array parts
     */

    // Type of the current array
    // Example: "sleepArr : array [1..3] of integer;" would be the type "integer"
    public $typeOf = null;

    // Current used  index to access the array
    // Example: "sleep(sleepArr[i])" would be the  variable "i" used as index
    public $forIndex = null;

    //flag for the association
    public $fromArray = null;

    /** @var Associations|null */
    public $start = null;

    /** @var Associations|null */
    public $end = null;



    /*
     * Conditions
     */
    public $cases = [];

    public $operator = null;
    public $condition = null;

    public $onTrue = null;
    public $onFalse = null;



    /** @var Associations[] */
    public $extraArguments = [];

    public $assign = null;
    public $onlyPointer = null;

    /** @var Associations */
    public $math = null;

    /** @var null|Associations */
    public $attribute = null;

    public $firstAttribute = null;

    public $fromState = null;
    public $parent = null;
    public $index = null;

    /** @var array|null  */
    public $forceFloat = null;
    public $varType = null;

    public $return = null;

    public $negate = null;
    public $scriptName = "";

    public function toCsv(){
        $infos = $this->__debugInfo();
        $csv = "";
        foreach ($infos as $key => $value) {
            if (is_array($value)){

                $csv .= '"' . implode(',', $value) . "\",";
            }else{
                $csv .= $value . ",";

            }
        }

        return substr($csv, 0, -1);
    }

    public function __debugInfo()
    {

        $debug = [];

        foreach ($this as $key => $value) {

            if ($value !== null && $value !== ""){
                if (is_array($value) && count($value) == 0) continue;
                $debug[$key] = $value;
            }
        }

        return $debug;
    }

    /**
     * Associations constructor.
     * @param Compiler|null $compiler
     * @throws Exception
     */
    public function __construct(Compiler $compiler = null)
    {
        if (is_null($compiler)) return;

        $value = strtolower($compiler->consume());

        $isVariableOrFunction = false;

        /**
         * Check: Is this a variable ?
         */
        $variable = $compiler->getVariable($value);

        if ($variable !== false) {
            $isVariableOrFunction = true;

            if ($variable['type'] == "array") {
                $compiler->current++; // Skip "["
                $this->forIndex = $compiler->getPossibleMathChilds();
                $compiler->current++; // Skip "]"
            }


            //we access a object attribute
            if ($compiler->consumeIfTrue(".")){
                $attribute = $compiler->createVariableAssociation($variable);
                $attribute->value = $compiler->consume();

                if ($variable['type'] == "array"){
                    $records = $compiler->records[$variable['typeOf']];

                }else{
                    $records = $compiler->records[$variable['type']];
                }

                $attribute->firstAttribute = array_keys($records)[0] == $attribute->value;

                foreach ($records as $index => $record) {

                    if ($index == $attribute->value){
                        $attribute->varType = $record['type'];
                        $attribute->offset = $record['offset'];
                    }
                }


                $variable['attribute'] = $attribute;

            }

            if (isset($compiler->records[$variable['type']])){
                $variable['type'] = 'object';
            }

            $compiler->createVariableAssociation($variable, $this);
        }

        /**
         * Check: Is this a function ?
         */
        $function = $compiler->gameClass->getFunction($value);
        if ($function !== false) {
            $isVariableOrFunction = true;

            $this->type = Tokens::T_FUNCTION;
            $this->value = $function['name'];
            $this->offset = $function['offset'];

            if (isset($function['forceFloat']))
                $this->forceFloat = $function['forceFloat'];

            if (isset($function['type'])){
                $this->isCustomFunction = $function['type'] == Tokens::T_CUSTOM_FUNCTION;
                $this->isProcedure = $function['type'] == Tokens::T_PROCEDURE;
            }

            $this->return = !isset($function['return']) ? null : $function['return'];

            if ($compiler->getToken() == "(") {

                $params = new Associations($compiler);
//                var_dump($params);exit;
                $current = 0;
                while($current < count($params->childs)){
                    $param = $params->childs[$current];

                    /**
                     * Right after a function parameter is a math operation
                     */

//                    var_dump($params->childs[$current + 1]->type);
                    if (
                        $param->type == Token::T_CONDITION ||
                        (
                            isset($params->childs[$current + 1]) &&
                            $compiler->isTypeMathOperator($params->childs[$current + 1]->type)
                        )
                    ){
//                        var_dump($param);

                        $math = new Associations();
                        $math->type = Tokens::T_MATH;
                        $math->usedinFunction = $compiler->createVariableAssociation( $function);
                        $math->usedinFunction->varType = null;

                        if ($param->type == Token::T_CONDITION){
                            $math->childs = (new RPN())->convertToReversePolishNotation($param->childs);

                            $current = $current - 2;
                        }else{
                            $math->childs = (new RPN())->convertToReversePolishNotation([
                                $params->childs[$current],  //value a
                                $params->childs[$current + 1], // operator
                                $params->childs[$current + 2], // value b
                            ]);
//                            var_dump($this->childs);exit;

                        }

                        $current = $current + 2;

                        if (count($math->childs) == 1){
                            $this->childs[] = $math->childs[0];
                        }else{
                            $this->childs[] = $math;

                        }

                    }else{
                        $this->childs[] = $param;
                    }

                    $current++;
                }

            }
            /**
             * Callscript calls can have arguments
             */
            if ($compiler->consumeIfTrue(":")) {

                do{
                    $entry = new Associations($compiler);
                    $entry->onlyPointer = true;
                    $this->extraArguments[] = $entry;
                }while($compiler->consumeIfTrue(","));
            }
        }

        /**
         * Assignment can be happen to a variable and also to a function.
         *
         * Custom function blocks use his name to return the value...
         */
        if ($isVariableOrFunction){

            $isState = $compiler->getState($this->varType);

            if ($compiler->consumeIfTrue(":=")) {

                if ($compiler->getToken($compiler->current + 1) == "to"){

                    /**
                     * Assignment within for loop condition
                     *
                     * for i:= 1 to 3 do
                     */

                    $this->start = new Associations($compiler);
                    $compiler->current++;       //Skip "to"

                    $this->end = $compiler->getPossibleMathChilds();

                }else{
                    /**
                     * Assignment
                     */

                    $this->type = Tokens::T_ASSIGN;
                    if ($isState !== false) {
                        $stateName = $compiler->consume();

                        $state = $compiler->getState($this->varType, $stateName);

                        $this->varType = "state";
                        $this->assign = new Associations();
                        $this->assign->type = Tokens::T_STATE;
                        $this->assign->value = $stateName;
                        $this->assign->offset = $state['offset'];

                    } else {
                        $this->assign = $compiler->getPossibleMathChilds();
                    }
                }
            }

            return;
        }

        /**
         * Check: Is this a constant ?
         */
        $constant = $compiler->gameClass->getConstant($value);

        if ($constant !== false) {

            $this->type = Tokens::T_CONSTANT;
            $this->value = $value;
            $this->section = "header";
            $this->offset = Helper::fromHexToInt($constant['offset']);
            $this->varType = isset($constant['varType']) ? $constant['varType'] : 'integer';
            return;
        }

        /**
         * Check: Is this a int / float ?
         */
        if (is_numeric($value)) {

            //convert the string into a float/int
            $number = strpos($value, '.') !== false ?
                (float)$value :
                (int)$value;

            $this->type = is_float($number) ? Tokens::T_FLOAT : Tokens::T_INT;
            $this->value = $number;

            if (
                isset($compiler->gameClass->floatAllowedDeviation[(string)$this->value])
            ){
                $this->value = $compiler->gameClass->floatAllowedDeviation[(string)$this->value];
            }

            if ($number < 0){
                $this->negate = true;
                $this->value *= -1;
            }

            $this->offset = $this->value;

            return;
        }

        /**
         * Check: Is this a string ?
         */
        if (strpos($value, '\'') !== false) {
            $this->type = Tokens::T_STRING;

            //convert replaced strings back to original
            $this->value = substr($value, 1, -1);
            $stringIndex = substr($this->value, 4);
            $this->value = $compiler->strings[$stringIndex];

            /** @var Associations $string */
            $string = $compiler->strings4Script[strtolower($compiler->currentScriptName)][strtolower($this->value)];
            $this->scriptName = $string->scriptName;
            $this->size = $string->size;
            $this->offset = $string->offset;
            $this->section = $string->section;

            return;
        }

        /**
         * Check: Regular T_Token
         */
        switch ($value) {

            case 'scriptmain':
                $this->type = Tokens::T_NOP;
                $compiler->mlsScriptMain = $compiler->consume();
                break;
            case 'const':

                $this->type = Tokens::T_NOP;
                $this->consumeConstants($compiler, true);
                break;
            case 'type':
                $this->type = Tokens::T_NOP;
                $this->consumeTypes($compiler);
                break;
            case 'var':
            case 'arg':

            /**
             * Any procedure share his own scope of memory
             * Regular scripts share the same memory
             *
             * Note: Procedures are always loaded before any script block
             */

                if (
                    $compiler->currentBlockType == "procedure" ||
                    $compiler->currentBlockType == "function"
                ){
                    $compiler->offsetGlobalVariable = 0;
                    $compiler->offsetScriptVariable = 0;
                    $toAdd = $this->consumeParameters($compiler, "script", $value == "arg");
                }else{

                    $toAdd = $this->consumeParameters($compiler, $compiler->currentSection, $value == "arg");
                }

                $this->type = Tokens::T_NOP;

                $this->applyVariables($compiler, $toAdd);

                $compiler->consumeIfTrue("begin");      //skip "begin"

                break;
            case 'entity':
                $this->type = Tokens::T_NOP;
                $compiler->mlsEntityName = $compiler->consume();

                while($compiler->getToken() != ':'){
                    $compiler->mlsEntityName .= $compiler->consume(); // consume (
                }

                $compiler->current++;
                $compiler->mlsEntityType = $compiler->consume();
                break;

            case 'procedure':
            case 'function':
                $compiler->currentBlockType = $value;

                $this->value = $compiler->consume();
                $compiler->currentScriptName = $this->value;

                $parameters = [];

                //we have params
                if ($compiler->consumeIfTrue("(")){
                    $parameters = $this->consumeParameters($compiler, $this->value);

                    $parameterCount = 0;
                    foreach ($parameters as $parameter) {
                        $parameterCount += count($parameter['names']);
                    }

                    $compiler->offsetProcedureVariable = -12;

                    $this->applyVariables($compiler, $parameters, true);
                }

                // Return type
                if ($compiler->consumeIfTrue(":")) $this->return = $compiler->consume();

                // Forward order
                if ($compiler->consumeIfTrue("forward")) {
                    $this->type = Tokens::T_FORWARD;

                // regular body content
                } else {
                    $this->type = $value == "function" ? Tokens::T_CUSTOM_FUNCTION : Tokens::T_PROCEDURE;

                    if (isset($compiler->gameClass->functions[strtolower($this->value)])){
                        $this->return = $compiler->gameClass->functions[strtolower($this->value)]['return'];
                    }

                }

                $compiler->consumeIfTrue("begin");      //skip "begin"


                $compiler->addCustomFunction(
                    $this->value,
                    $value == "function" ? Tokens::T_CUSTOM_FUNCTION : Tokens::T_PROCEDURE,
                    $this->return
                );

                if ($this->type !== Tokens::T_FORWARD){
                    $this->childs = $this->associateUntil($compiler, Tokens::T_END);
                }

                /**
                 * Add the custom function also to the force float map
                 */
                $floatMap = [];
                $addFloatMap = false;
                foreach (array_reverse($parameters) as $parameter) {

                    if ( $parameter['type'] == 'float' ){
                        $addFloatMap = true;
                        foreach ($parameter['names'] as $name) {
                            $floatMap[] = true;
                        }
                    }else{
                        foreach ($parameter['names'] as $name) {
                            $floatMap[] = false;
                        }
                    }
                }

                if ($addFloatMap){
                    $compiler->gameClass->functionForceFloat[$compiler->currentScriptName] = $floatMap;
                }

                break;
            case 'script':
                $compiler->currentBlockType = $value;
                $compiler->offsetScriptVariable = 0;

                //at this point we save any new variables into the script section
                $compiler->currentSection = "script";
                $compiler->currentScriptName = $compiler->consume();

                $this->type = Tokens::T_SCRIPT;
                $this->value = $compiler->currentScriptName;

                $compiler->consumeIfTrue("begin");      //skip "begin"

                $this->childs = $this->associateUntil($compiler, Tokens::T_END);

                break;

            case 'case':
                $this->type = Tokens::T_SWITCH;
                $this->value = new Associations($compiler);

                $isState = $compiler->getState($this->value->varType);

                $compiler->consumeIfTrue("of");     //skip "of"

                while ($compiler->getToken($compiler->current + 1) == ":") {
                    $state = false;

                    if ($isState !== false) {
                        $state = $compiler->getState($this->value->varType, $compiler->getToken());
                    }

                    $case = new Associations();
                    $case->type = Tokens::T_CASE;

                    if ($state !== false) {
                        $case->value = $state;
                        $compiler->current++;
                    } else {
                        $case->value = new Associations($compiler);
                    }
                    $compiler->current++;

                    $case->onTrue = [];
                    if ($compiler->consumeIfTrue("begin")) {
                        $case->onTrue = $this->associateUntil($compiler, Tokens::T_END);
                    } else {
                        $case->onTrue[] = new Associations($compiler);
                    }

                    $this->cases[] = $case;
                }

                $compiler->current++;

                break;
            case 'for':
                $this->type = Tokens::T_FOR;
                $condition = new Associations($compiler);

                $compiler->current++;       //Skip "do"

                $this->start = $condition->start;
                $this->end = $condition->end;

                $this->childs[] = $condition;

                if ($compiler->consumeIfTrue("begin")) {

                    $this->onTrue = $this->associateUntil($compiler, Tokens::T_END);

                } else {
                    /**
                     * Short For-Statement
                     *
                     * for i:= 1 to 3 do PKarray[i] := false;
                     */
                    $this->onTrue = [new Associations($compiler)];
                }

                break;
            case 'this':
                $this->type = Tokens::T_SELF;
                break;

            case 'while':
            case 'if':
                $this->type = $value == "if" ? Tokens::T_IF : Tokens::T_DO;

                $case = new Associations();
                $this->cases[] = $case;

                $case->type = Token::T_IF_CASE;

                /** @var Associations[] $conditions */
                $conditions = $this->associateUntil($compiler, $this->type == Tokens::T_IF ? Tokens::T_THEN : Tokens::T_DO);

                $result = [];
                $this->flatForRpn($conditions, $result);
                $conditions = (new RPN())->convertToReversePolishNotation($result);
                $newCondition = new Associations();
                $newCondition->type = Tokens::T_CONDITION;
                $newCondition->childs = $conditions;

                $case->condition = $newCondition;

                if ($compiler->consumeIfTrue("begin")) {

                    $case->onTrue = $this->associateUntil($compiler, Tokens::T_END);

                } else {
                    /**
                     * Short IF-Statement
                     *
                     * If IsPlayerWalking then sleep(1500);
                     */
                    $case->onTrue = [new Associations($compiler)];
                }

                if ($compiler->consumeIfTrue("else")) {

                    if ($compiler->consumeIfTrue("begin")) {

                        $case->onFalse = $this->associateUntil($compiler, Tokens::T_END);
                    } else if ($compiler->getToken() == "if") {
                        foreach ((new Associations($compiler))->cases as $_case) {
                            $this->cases[] = $_case;
                        }

                    } else {
                        $case->onFalse = [new Associations($compiler)];
                    }

                }

                break;

            case '(':
                $this->type = Tokens::T_CONDITION;

                $this->childs = $this->associateUntil($compiler, Tokens::T_BRACKET_CLOSE);

                break;

            /**
             * Tread boolean as simple int
             */
            case 'true':
            case 'false':
                $this->type = Tokens::T_INT;
                $this->varType = 'integer';
                $this->value = $value == "true" ? 1 : 0;
                $this->offset = $value == "true" ? 1 : 0;
                break;

            /**
             * Simple values, just convert into T_TOKEN
             */
            case 'else':   $this->type = Tokens::T_ELSE; break;
            case '<>':   $this->type = Tokens::T_IS_NOT_EQUAL; break;
            case 'and':  $this->type = Tokens::T_AND; break;
            case 'or':   $this->type = Tokens::T_OR; break;
            case 'nil':  $this->type = Tokens::T_INT; $this->value = 0; break;
            case '+':    $this->type = Tokens::T_ADDITION; break;
            case 'mod':  $this->type = Tokens::T_MOD; break;
            case '-':    $this->type = Tokens::T_SUBSTRACTION; break;
            case '*':    $this->type = Tokens::T_MULTIPLY; break;
            case '/':    $this->type = Tokens::T_DIVISION; break;
            case ':=':   $this->type = Tokens::T_ASSIGN; break;
            case '=':    $this->type = Tokens::T_IS_EQUAL; break;
            case '<':    $this->type = Tokens::T_IS_SMALLER; break;
            case '<=':   $this->type = Tokens::T_IS_SMALLER_EQUAL; break;
            case '>':    $this->type = Tokens::T_IS_GREATER; break;
            case '>=':   $this->type = Tokens::T_IS_GREATER_EQUAL; break;
            case 'then': $this->type = Tokens::T_THEN; break;
            case ')':    $this->type = Tokens::T_BRACKET_CLOSE; break;
            case 'end':  $this->type = Tokens::T_END; break;
            case 'not':  $this->type = Tokens::T_NOT; break;
            case 'do':   $this->type = Tokens::T_DO; break;

            case 'begin':
                $this->type = Tokens::T_BEGIN_WRAPPER;

                $this->childs = $this->associateUntil($compiler, Tokens::T_END);
                break;
            case 'end.':
            //Uhm its a mistake by the r* devs...
            case 'd.':
            case ',':    $this->type = Tokens::T_NOP; break;

            default:
                $compiler->raiseException();
                break;
        }
    }

    /**
     * @param Compiler $compiler
     * @param $tokenType
     * @return array
     * @throws Exception
     */
    public function associateUntil(Compiler $compiler, $tokenType)
    {

        $result = [];
        $endFound = false;
        while ($endFound == false) {
            $associated = new Associations($compiler);
            if ($associated->type == Tokens::T_NOP) continue;

            if ($associated->type != $tokenType) $result[] = $associated;

            $endFound = $associated->type == $tokenType;
        }

        return $result;
    }


    /**
     * @param Compiler $compiler
     * @param string $section
     * @param bool $isArgument
     * @return array
     * @throws Exception
     */
    private function consumeParameters(Compiler $compiler, $section = "header", $isArgument = false)
    {

        /**
         * A mystery
         *
         * it exists a call like this
         * PROCEDURE SpawnHunterWithM16( var pos : Vec3D ); FORWARD;
         *
         * and hell, i dont know why they put "var" before the variable
         */
        if ($isArgument == false) $compiler->consumeIfTrue('var');

        $toAdd = [];

        while (
            $compiler->getToken($compiler->current + 1) == ":" ||
            $compiler->getToken($compiler->current + 1) == ","
        ) {

            if ($isArgument == false) $compiler->consumeIfTrue('var');

            if ($compiler->consumeIfTrue(")")) break;

            $names = [$compiler->consume()];

            while ($compiler->getToken() == ',') {
                $compiler->current++;
                $names[] = $compiler->consume();
            }

            $compiler->current++;   //skip ":"

            $isLevelVar = $compiler->getToken() == "level_var";
            $isGameVar = $compiler->getToken() == "game_var";

            if ($isLevelVar || $isGameVar) $compiler->current++;

            $type = $compiler->consume();
            if ($type == "real") $type = "float";

            $entry = [
                'names' => $names,
                'type' => $type,
                'size' => null,
                'fromArray' => false,
                'isArgument' => $isArgument,
                'isLevelVar' => $isLevelVar,
                'isGameVar' => $isGameVar,
                'section' => $section
            ];


            /**
             * itemsSpawned : array[1..3] of boolean;
             */
            if ($type == "array") {

                $compiler->current++;   // Skip "["
                list($start, $end) = explode('..', $compiler->consume());
                $compiler->current++;   // Skip "]"
                $compiler->current++;   // Skip "of"

                $type = $compiler->consume();

                $entry['type'] = $type;
                $entry['fromArray'] = true;
                $entry['start'] = $start;
                $entry['end'] = $end;

            } else {

                /**
                 * Parse the string size
                 *
                 * me : string[32];
                 */
                if ($type == "string" && $compiler->getToken() == "[") {
                    $compiler->current++; //Skip "["
                    $entry['size'] = (int)$compiler->consume();;
                    $compiler->current++; //Skip "]"
                }

            }

            if ($compiler->consumeIfTrue(':')){
                $entry['fallback'] = new Associations($compiler);
            }

            $toAdd[] = $entry;

            /**
             * When we use this function to parse "custom function" parameters,
             * its possible that the "custom function" has a return value
             *
             * We need to check if the last token a closed bracket is and break then
             *
             * function DoorIsOpen(name : string) : boolean;
             */
            if ($compiler->getToken() == ")") {
                $compiler->current++;
                break;
            }

            if ($isArgument == false) $compiler->consumeIfTrue('var');

        }


        return $toAdd;
    }

    public function applyVariables(Compiler $compiler, &$toAdd, $reverse = false){
        /**
         * Procedure arguments offset calculation are reversed
         *
         * PROCEDURE SpawnHunter(HunterName : string; x, y, z : real); FORWARD;
         *
         * need to be reversed into
         *
         * PROCEDURE SpawnHunter(z, y, x : real; HunterName : string); FORWARD;
         *
         * I guess its because we generate negative offsets for procedures/custom functions
         *
         */
        if ($reverse) $toAdd = array_reverse($toAdd);

        foreach ($toAdd as $var) {

            if ($reverse) $var['names'] = array_reverse($var['names']);

            if ($var['fromArray'] === true){

                foreach ($var['names'] as $name) {

                    $entry = array_merge([], $var);
                    $entry['name'] = $name;
                    $entry['type'] = 'array';
                    $entry['typeOf'] = $var['type'];

                    if (isset($compiler->records[$var['type']])){
                        $entry['size'] = $var['end'] * $compiler->calcSize($var['type']);

                    }else{
                        $entry['size'] = $var['end'] * 4;
                    }

                    $compiler->addVariable($entry);

                }

            }else{

                foreach ($var['names'] as $name) {

                    $entry = array_merge([], $var);
                    $entry['name'] = $name;

                    $compiler->addVariable($entry);
                }
            }
        }
    }


    private function consumeTypes(Compiler $compiler)
    {
        while ($compiler->getToken($compiler->current + 1) == "=") {

            $name = $compiler->consume();
            $compiler->current++; // Skip "="


            if ($compiler->consumeIfTrue('(')){
                $entries = [$compiler->consume()];
                while ($compiler->getToken($compiler->current) != ')') {
                    $compiler->current++;
                    $entries[] = $compiler->consume();
                }

                $compiler->current++;
                $compiler->addStates($name, $entries);

            }

            else if ($compiler->consumeIfTrue('record')){


                $recordEntries = [];
                $offset = 0;
                while ($compiler->getToken($compiler->current + 1) == ":") {
                    $recordName = $compiler->consume();
                    $compiler->current++; // Skip ":"
                    $recordType = $compiler->consume();

                    $recordEntries[$recordName] = [
                        'type' => $recordType,
                        'offset' => $offset
                    ];

                    $offset += $compiler->calcSize($recordType);
                }

                $compiler->current++; // Skip "end"


                $compiler->addRecord($name, $recordEntries);

            }



        }
    }

    public function consumeConstants(Compiler $compiler, $onlyConsume = false)
    {

        while (
            $compiler->getToken($compiler->current + 1) == "="
        ) {

            $name = $compiler->consume();
            //skip "="
            $compiler->current++;

            $value = $compiler->consume();

            if (strpos($value, "'") !== false) {
                $value = substr($value, 1, -1);
                $type = 'string';
            }else{
                if (strpos($value, ".") !== false) {
                    $value = (float)$value;
                    $type = 'float';
                } else {
                    $value = (int)$value;
                    $type = 'integer';
                }
            }

            if ($onlyConsume == false) $compiler->addConstants($name, $value, $type);
        }
    }

    /**
     * @param Associations[] $entries
     * @param Associations[] $result
     * @throws Exception
     */
    public function flatForRpn( array $entries, array &$result ){

        foreach ($entries as $entry) {

            if ($entry->type == Tokens::T_CONDITION){

                $open = new Associations();
                $open->type = Tokens::T_BRACKET_OPEN;
                $open->value = "(";

                $result[] = $open;
                $this->flatForRpn($entry->childs, $result);

                $close = new Associations();
                $close->type = Tokens::T_BRACKET_CLOSE;
                $close->value = ")";

                $result[] = $close;
                continue;
            }else{

                $result[] = $entry;
            }
        }

    }

    public function __toString()
    {
        return $this->type;
    }

}