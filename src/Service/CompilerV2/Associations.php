<?php

namespace App\Service\CompilerV2;

use App\Service\Compiler\Token;use App\Service\Compiler\Tokens\T_AND;
use Exception;

class Associations
{

    public $type = Tokens::T_UNKNOWN;
    public $value = "";
    public $forIndex = null;

    /** @var Associations[] */
    public $childs = [];

    public $assign = false;
    public $math = false;

    public $size = null;
    public $sizeWithoutPad4 = null;
    public $offset = null;
    /** @var array|null  */
    public $forceFloat = null;
    public $varType = null;
    public $section = null;

    public $return = null;
    public $isNot = null;

    public $condition = false;
    public $onTrue = null;
    public $onFalse = null;
    public $operator = null;
    public $operatorValue = null;
    public $statementOperator = null;
    public $isCustomFunction = null;
    public $isProcedure = null;
    public $start = null;
    public $paramCount = null;
    public $isLastWriteDebugParam = null;
    /**
     * @var Associations|null
     */
    public $end = null;

    public $cases = [];

    public function __debugInfo()
    {

        $debug = [
            'type' => $this->type
        ];
        if ($this->value !== "") $debug['value'] = $this->value;
        if (count($this->childs)) $debug['childs'] = $this->childs;
        if (count($this->cases)) $debug['cases'] = $this->cases;
        if ($this->assign !== false) $debug['assign'] = $this->assign;
        if ($this->math !== false) $debug['math'] = $this->math;
        if ($this->size !== null) $debug['size'] = $this->size;
        if ($this->sizeWithoutPad4 !== null) $debug['sizeWithoutPad4'] = $this->sizeWithoutPad4;
        if ($this->forceFloat !== null) $debug['forceFloat'] = $this->forceFloat;
        if ($this->offset !== null) $debug['offset'] = $this->offset;
        if ($this->forIndex !== null) $debug['forIndex'] = $this->forIndex;
        if ($this->varType !== null) $debug['varType'] = $this->varType;
        if ($this->section !== null) $debug['section'] = $this->section;
        if ($this->return !== null) $debug['return'] = $this->return;
        if ($this->isNot !== null) $debug['isNot'] = $this->isNot;
        if ($this->onTrue !== null) $debug['onTrue'] = $this->onTrue;
        if ($this->onFalse !== null) $debug['onFalse'] = $this->onFalse;
        if ($this->condition !== false) $debug['condition'] = $this->condition;
        if ($this->operator !== null) $debug['operator'] = $this->operator;
        if ($this->operatorValue !== null) $debug['operatorValue'] = $this->operatorValue;
        if ($this->statementOperator !== null) $debug['statementOperator'] = $this->statementOperator;
        if ($this->isCustomFunction !== null) $debug['isCustomFunction'] = $this->isCustomFunction;
        if ($this->isProcedure !== null) $debug['isProcedure'] = $this->isProcedure;
        if ($this->paramCount !== null) $debug['paramCount'] = $this->paramCount;
        if ($this->start !== null) $debug['start'] = $this->start;
        if ($this->end !== null) $debug['end'] = $this->end;
        if ($this->isLastWriteDebugParam !== null) $debug['isLastWriteDebugParam'] = $this->isLastWriteDebugParam;

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

        /**
         * Check: Is this a variable ?
         */
        $variable = $compiler->getVariable($value);

        if ($variable !== false) {
//exit;
            if ($variable['type'] == "array") {
                $compiler->current++;
                $indexName = $compiler->consume();
                $variable = $compiler->getVariable($value . '[' . $indexName . ']');

                if ($variable == false){
                    /**
                     * This happens when we access a array index within a loop
                     *
                     * for i:= 1 to 3 do PKarray[i] := false;
                     *
                     * PKarray[i] can not be known, the iterator "i" can have different names
                     */
                    $variable = $compiler->getVariable($value);
                    $this->forIndex = $compiler->getVariable($indexName);
                }

                $compiler->current++;
            }

            $this->type = Tokens::T_VARIABLE;
            $this->value = $variable['name'];

            $this->offset = $variable['offset'];
            $this->size = $variable['size'];
            $this->sizeWithoutPad4 = $variable['sizeWithoutPad4'];
            $this->varType = $variable['type'];
            $this->section = $variable['section'];


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
                    $this->end = new Associations($compiler);

                }else{
                    /**
                     * Assignment
                     */
                    if ($isState !== false) {
                        $stateName = $compiler->consume();

                        $state = $compiler->getState($this->varType, $stateName);

                        $this->varType = "state";
                        $this->assign = new Associations();
                        $this->assign->type = Tokens::T_STATE;
                        $this->assign->value = $stateName;
                        $this->assign->offset = $state['offset'];

                    } else {
                        $this->assign = new Associations($compiler);
                    }
                }


            }

            /**
             * Math operations
             */
            if (
                $compiler->getToken() == "+" ||
                $compiler->getToken() == "-" ||
                $compiler->getToken() == "*" ||
                $compiler->getToken() == "div"
            ) {

                $operator = new Associations($compiler);
                $operator->childs = [new Associations($compiler)];
                $this->math = $operator;
            }


            return;
        }

        /**
         * Check: Is this a function ?
         */
        $function = $compiler->gameClass->getFunction($value);

        if ($function !== false) {

            $this->type = Tokens::T_FUNCTION;
            $this->value = $function['name'];
            $this->offset = $function['offset'];
            if (isset($function['forceFloat'])) $this->forceFloat = $function['forceFloat'];

            if (isset($function['type'])){
                $this->isCustomFunction = $function['type'] == Tokens::T_CUSTOM_FUNCTION;
                $this->isProcedure = $function['type'] == Tokens::T_PROCEDURE;
            }
            $this->return = !isset($function['return']) ? null : $function['return'];

            if ($compiler->getToken() == "(") {
                $params = new Associations($compiler);
                foreach ($params->childs as $child) {
                    $this->childs[] = $child;
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
            $this->offset = $constant['offset'];
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

            return;
        }

        /**
         * Check: Is this a string ?
         */
        if (strpos($value, '"') !== false || strpos($value, '\'') !== false) {
            $this->type = Tokens::T_STRING;
            $this->value = substr($value, 1, -1);

            $stringIndex = substr($this->value, 4);
            $this->value = $compiler->strings[$stringIndex];

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
                $this->consumeConstants($compiler);
                break;
            case 'type':
                $this->type = Tokens::T_NOP;
                $this->consumeTypes($compiler);
                break;
            case 'var':
            case 'arg':
                $this->type = Tokens::T_NOP;
                $this->consumeParameters($compiler, $compiler->currentSection);
                break;
            case 'entity':
                $this->type = Tokens::T_NOP;
                $compiler->mlsEntityName = $compiler->consume();
                $compiler->current++;
                $compiler->mlsEntityType = $compiler->consume();
                break;

            case 'procedure':
            case 'function':

                $this->value = $compiler->consume();
                $compiler->currentScriptName = $this->value;

                //we have params
                if ($compiler->consumeIfTrue("(")) $this->consumeParameters($compiler, $this->value, true);

                // Return type
                if ($compiler->consumeIfTrue(":")) $this->return = $compiler->consume();

                // Forward order
                if ($compiler->consumeIfTrue("forward")) {
                    $this->type = Tokens::T_FORWARD;

                    // regular body content
                } else {
                    $this->type = $value == "function" ? Tokens::T_CUSTOM_FUNCTION : Tokens::T_PROCEDURE;

                    $this->childs = $this->associateUntil($compiler, Tokens::T_END);

                }

                $compiler->addCustomFunction($this->value, Tokens::T_PROCEDURE);

                break;
            case 'script':
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
            case 'while':
            case 'if':
                $this->type = $value == "if" ? Tokens::T_IF : Tokens::T_DO;

                $case = new Associations();
                $this->cases[] = $case;

                $case->type = Token::T_IF_CASE;

                /** @var Associations[] $conditions */
                $conditions = $this->associateUntil($compiler, $this->type == Tokens::T_IF ? Tokens::T_THEN : Tokens::T_DO);

                $conditionsRearranged = [];

                $nextNot = null;
                $nextOperator = null;

                /** Parse the Statement */

                /**
                 * We have a regular statement without brackets
                 *
                 * if randFlash = 0 then
                 */
                if (count($conditions) == 3 && $conditions[0]->type != Tokens::T_CONDITION) {
                    $newCondition = new Associations();
                    $newCondition->type = Tokens::T_CONDITION;
                    list($firstChild, $operator, $operatorValue) = $this->convertTripleStatement($conditions);
                    $newCondition->childs = [$firstChild];
                    $newCondition->operator = $operator;
                    $newCondition->operatorValue = $operatorValue;

                    $conditionsRearranged[] = $newCondition;

                    /**
                     * strange Wrapped statement
                     *
                     * if (sleep(100)) <> NIL then
                     */
                }else if (
                    count($conditions) == 3 &&
                    $conditions[0]->type == Tokens::T_CONDITION &&
                    count($conditions[0]->childs) == 1
                ){

                    $conditions[0] = $conditions[0]->childs[0];

                    $newCondition = new Associations();
                    $newCondition->type = Tokens::T_CONDITION;
                    list($firstChild, $operator, $operatorValue) = $this->convertTripleStatement($conditions);
                    $newCondition->childs = [$firstChild];
                    $newCondition->operator = $operator;
                    $newCondition->operatorValue = $operatorValue;

                    $conditionsRearranged[] = $newCondition;

                }else{

                    foreach ($conditions as $conditionRaw) {

                        $conditionReGrouped = [$conditionRaw];
                        if (
                            $conditionRaw->type === Tokens::T_CONDITION &&
                            count($conditionRaw->childs) < 3
                        ){
                            $conditionReGrouped = $conditionRaw->childs;
                        }

                        foreach ($conditionReGrouped as $condition) {

                            if ($condition->type === Tokens::T_CONDITION){

                                /**
                                 * We have a regular statement
                                 *
                                 * if (GetDoorState(entity) <> DOOR_CLOSED) then
                                 */
                                if (count($condition->childs) == 3){
                                    list($firstChild, $operator, $operatorValue) = $this->convertTripleStatement($condition->childs);

                                    $newCondition = new Associations();
                                    $newCondition->type = Tokens::T_CONDITION;
                                    $newCondition->childs = [$firstChild];
                                    $newCondition->isNot = $nextNot;
                                    $newCondition->statementOperator = $nextOperator;
                                    $newCondition->operator = $operator;
                                    $newCondition->operatorValue = $operatorValue;

                                    $conditionsRearranged[] = $newCondition;

                                    $nextNot = null;
                                    $nextOperator = null;

                                }else{
                                    var_dump($condition);
                                    throw new Exception("IF Statement with not 3 childs");
                                }
                            }else if ($condition->type == Tokens::T_NOT){
                                $nextNot = true;
                                continue;
                            }else if ($condition->type == Tokens::T_AND){
                                $nextOperator = $condition->type;
                                continue;
                            }else if ($condition->type == Tokens::T_OR){
                                $nextOperator = $condition->type;
                                continue;

                            }else{
                                /**
                                 * We have a single value statement
                                 *
                                 * If IsPlayerWalking then
                                 */
                                $newCondition = new Associations();
                                $newCondition->type = Tokens::T_CONDITION;
                                $newCondition->childs = [$condition];
                                $newCondition->isNot = $nextNot;
                                $newCondition->statementOperator = $nextOperator;

                                $conditionsRearranged[] = $newCondition;

                                $nextNot = null;
                                $nextOperator = null;
                            }
                        }

                    }
                }

                $case->condition = $conditionsRearranged;

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

            case 'true':
                $this->type = Tokens::T_BOOLEAN;
                $this->varType = 'boolean';
                $this->value = true;
                break;
            case 'false':
                $this->type = Tokens::T_BOOLEAN;
                $this->varType = 'boolean';
                $this->value = false;
                break;

            /**
             * Simple values, just convert into T_TOKEN
             */
            case '<>':   $this->type = Tokens::T_IS_NOT_EQUAL; break;
            case 'and':  $this->type = Tokens::T_AND; break;
            case 'or':   $this->type = Tokens::T_OR; break;
            case 'nil':  $this->type = Tokens::T_INT; $this->value = 0; break;
            case '+':    $this->type = Tokens::T_ADDITION; break;
            case '-':    $this->type = Tokens::T_SUBSTRACTION; break;
            case '*':    $this->type = Tokens::T_MULTIPLY; break;
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
            case 'end.':
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
     * @param Associations[] $associations
     * @return mixed
     */
    private function convertTripleStatement( array $associations ){

        return [
            $associations[0],
            $associations[1]->type,
            $associations[2]
        ];
    }

    private function consumeParameters(Compiler $compiler, $section = "header", $reverse = false)
    {

        /**
         * A mystery
         *
         * it exists a call like this
         * PROCEDURE SpawnHunterWithM16( var pos : Vec3D ); FORWARD;
         *
         * and hell, i dont know why they put "var" before the variable
         */
        $compiler->consumeIfTrue('var');

        $toAdd = [];

        while (
            $compiler->getToken($compiler->current + 1) == ":" ||
            $compiler->getToken($compiler->current + 1) == ","
        ) {

            $compiler->consumeIfTrue('var');

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

            $entry = [
                'names' => $names,
                'type' => $type,
                'size' => null,
                'fromArray' => false,
                'isLevelVar' => $isLevelVar,
                'isGameVar' => $isGameVar,
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

            } else {

                /**
                 * Parse the string size
                 *
                 * me : string[32];
                 */
                $size = null;
                if ($type == "string" && $compiler->getToken() == "[") {
                    $compiler->current++;
                    $size = (int)$compiler->consume();
                    $entry['size'] = $size;
                    $compiler->current++;

                }

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

            $compiler->consumeIfTrue('var');

        }

        if ($reverse) $toAdd = array_reverse($toAdd);

        foreach ($toAdd as $var) {

            if ($reverse) $var['names'] = array_reverse($var['names']);

            if ($var['fromArray'] === true){

                foreach ($var['names'] as $name) {
                    $compiler->addVariable($name, 'array', null, false, false, $section);

                    for ($i = $start; $i <= $end; $i++) {
                        $compiler->addVariable($name . '[' . $i . ']', $var['tyüe'], null, false, false, $section);
                    }
                }

            }else{

                foreach ($var['names'] as $name) {
                    $compiler->addVariable($name, $var['type'], $var['size'], $var['isLevelVar'], $var['isGameVar'], $section);
                }


            }

        }
    }


    private function consumeTypes(Compiler $compiler)
    {

        while ($compiler->getToken($compiler->current + 1) == "=") {

            $name = $compiler->consume();
            $compiler->current++;
            $compiler->current++;

            $entries = [$compiler->consume()];
            while ($compiler->getToken($compiler->current) != ')') {
                $compiler->current++;
                $entries[] = $compiler->consume();
            }

            $compiler->current++;

            $compiler->addStates($name, $entries);
        }
    }

    private function consumeConstants(Compiler $compiler)
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
                    $type = 'int';
                }
            }

            $compiler->addConstants($name, $value, $type);
        }

    }

    public function __toString()
    {
        return $this->type;
    }

}