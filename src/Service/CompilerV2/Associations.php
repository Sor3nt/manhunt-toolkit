<?php

namespace App\Service\CompilerV2;

use App\Service\Compiler\Token;
use App\Service\Helper;
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
    public $index = null;
    /** @var array|null  */
    public $forceFloat = null;
    public $varType = null;
    public $section = null;
    public $fromArray = false;

    public $return = null;
    public $isNot = null;

    public $condition = false;
    public $onTrue = null;
    public $onFalse = null;
    public $operator = null;
    /** @var Associations|null  */
    public $operatorValue = null;
    public $statementOperator = null;
    public $isCustomFunction = null;
    public $isProcedure = null;
    public $start = null;
    public $paramCount = null;
    public $isLastWriteDebugParam = null;
    public $isLastCondition = null;
    public $negate = false;
    public $scriptName = "";
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
        if ($this->scriptName !== "") $debug['scriptName'] = $this->scriptName;
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
        if ($this->index !== null) $debug['index'] = $this->index;
        if ($this->return !== null) $debug['return'] = $this->return;
        if ($this->isNot !== null) $debug['isNot'] = $this->isNot;
        if ($this->onTrue !== null) $debug['onTrue'] = $this->onTrue;
        if ($this->onFalse !== null) $debug['onFalse'] = $this->onFalse;
        if ($this->condition !== false) $debug['condition'] = $this->condition;
        if ($this->isLastCondition !== null) $debug['isLastCondition'] = $this->isLastCondition;
        if ($this->operator !== null) $debug['operator'] = $this->operator;
        if ($this->operatorValue !== null) $debug['operatorValue'] = $this->operatorValue;
        if ($this->statementOperator !== null) $debug['statementOperator'] = $this->statementOperator;
        if ($this->isCustomFunction !== null) $debug['isCustomFunction'] = $this->isCustomFunction;
        if ($this->isProcedure !== null) $debug['isProcedure'] = $this->isProcedure;
        if ($this->paramCount !== null) $debug['paramCount'] = $this->paramCount;
        if ($this->start !== null) $debug['start'] = $this->start;
        if ($this->end !== null) $debug['end'] = $this->end;
        if ($this->fromArray !== false) $debug['fromArray'] = $this->fromArray;
        if ($this->negate !== false) $debug['negate'] = $this->negate;
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
            $this->sizeWithoutPad4 = isset($variable['sizeWithoutPad4']) ? $variable['sizeWithoutPad4'] : $variable['size'];
            $this->varType = $variable['type'];
            $this->section = $variable['section'];


            //used from array variables like "itemsSpawned[1]"
            if (isset($variable['fromArray'])) $this->fromArray = $variable['fromArray'];
            if (isset($variable['index'])) $this->index = $variable['index'];



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
                $this->type = Tokens::T_MATH;
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
//            $this-> = $value;
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
        if (strpos($value, '"') !== false || strpos($value, '\'') !== false) {
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
                $this->consumeConstants($compiler);
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

                if ($compiler->currentBlockType == "procedure"){
                    $compiler->offsetScriptVariable = 0;
                }
//                var_dump($compiler->currentScriptName . " " . $);
//                exit;
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
                $compiler->currentBlockType = $value;

                $compiler->offsetProcedureVariable = -12;

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
            case 'while':
            case 'if':
                $this->type = $value == "if" ? Tokens::T_IF : Tokens::T_DO;

                $case = new Associations();
                $this->cases[] = $case;

                $case->type = Token::T_IF_CASE;

                /** @var Associations[] $conditions */
                $conditions = $this->associateUntil($compiler, $this->type == Tokens::T_IF ? Tokens::T_THEN : Tokens::T_DO);

                $this->unwrapSimpleCondition($conditions);
                $this->convertToSimpleCondition($conditions);
                $this->convertConditionNot($conditions);
                $this->convertConditionStatementOperator($conditions);
                $this->convertConditionCompareOperator($conditions);
                $this->getLastCondition($conditions, $lastCondition);

                /** @var Associations $lastCondition */
                $lastCondition->isLastCondition = true;
                $case->condition = $conditions;



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
                $this->type = Tokens::T_INT;
                $this->varType = 'integer';
                $this->value = 1;
                $this->offset = 1;
                break;
            case 'false':
                $this->type = Tokens::T_INT;
                $this->varType = 'integer';
                $this->value = 0;
                $this->offset = 0;
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
                $entry['start'] = $start;
                $entry['end'] = $end;

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
                    $compiler->addVariable($name, 'array', null, false, false, $section);

                    for ($i = $var['start']; $i <= $var['end']; $i++) {
                        $compiler->addVariable($name . '[' . $i . ']', $var['type'], null, false, false, $section, true, $i);
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

    private function unwrapSimpleCondition( &$conditions){
        foreach ($conditions as $index => $child) {
            if ($child->type == Tokens::T_CONDITION){

                $this->unwrapSimpleCondition($child->childs);

                /**
                 * take sure we only unwrap nonsense wrapped stuff
                 *
                 * (GetEntity('Syringe_(CT)')) <> NIL
                 *
                 * and NOT
                 *
                 * (GetEntity('Syringe_(CT)')) or (GetEntity('Syringe_(CT)'))
                 */

                if (
                    count($conditions) == 3 &&
                    count($child->childs) == 1 &&

                    in_array($conditions[1]->type, [
                        Tokens::T_IS_NOT_EQUAL,
                        Tokens::T_IS_GREATER_EQUAL,
                        Tokens::T_IS_GREATER,
                        Tokens::T_IS_SMALLER_EQUAL,
                        Tokens::T_IS_SMALLER,
                        Tokens::T_IS_EQUAL,
                    ]) !== false
                ){
                    $conditions[$index] = $child->childs[0];
                }

                continue;
            }
        }
    }

    /**
     * If IsPlayerWalking then sleep(1500);
     * If NOT IsPlayerWalking then sleep(1500);
     * if leaveCutText = TRUE then sleep(1500);
     *
     * @param Associations[] $conditions
     * @throws Exception
     */
    private function convertToSimpleCondition( &$conditions){

        if (count($conditions) > 3) return;
        if ($conditions[0]->type == Tokens::T_CONDITION) return;
        if (
            count($conditions) == 2 &&
            $conditions[1]->type == Tokens::T_CONDITION
        ) return;
        if (
            count($conditions) == 3 &&
            $conditions[2]->type == Tokens::T_CONDITION
        ) return;

        $newCondition = new Associations();
        $newCondition->type = Tokens::T_CONDITION;
        $newCondition->childs = $conditions;

        $conditions = [$newCondition];
    }

    /**
     * @param Associations[] $conditions
     * @param Associations $parent
     * @param null $nextNot
     */
    private function convertConditionNot(&$conditions, &$parent = null, $nextNot = null ){

        foreach ($conditions as $index => $child) {

            if ($child->type == Tokens::T_CONDITION){

                if ($nextNot === true){
                    $child->isNot = true;
                    $nextNot = null;
                }
                $this->convertConditionNot($child->childs, $child, $nextNot);
                continue;
            }

            if ($child->type == Tokens::T_NOT){
                unset($conditions[$index]);

                if ($parent !== null){
                    $parent->isNot = true;
                }else{
                    $nextNot = true;
                }

                continue;
            }

        }

    }

    /**
     * @param $conditions
     */
    private function convertConditionStatementOperator( &$conditions ){

        $nextStatementOperator = false;
        foreach ($conditions as $index => $child) {

            if ($child->type == Tokens::T_CONDITION){

                if ($nextStatementOperator !== false){
                    $child->statementOperator = $nextStatementOperator;
                    $nextStatementOperator = false;
                }

                $this->convertConditionStatementOperator($child->childs);
                continue;
            }

            if (
                $child->type == Tokens::T_OR ||
                $child->type == Tokens::T_AND
            ){
                $nextStatementOperator = clone $child;

                unset($conditions[$index]);
            }
        }
    }


    /**
     * @param $conditions
     * @param $last
     *
     * find the last condition of a if statement
     */
    private function getLastCondition( &$conditions, &$last ){
        foreach ($conditions as $index => &$child) {

            if ($child->type == Tokens::T_CONDITION){
                $last = $child;
                $this->getLastCondition($child->childs, $last);
                continue;
            }
        }
    }


    /**
     * @param Associations[] $conditions
     * @param Associations|null $parent
     * @throws Exception
     */
    private function convertConditionCompareOperator( &$conditions, Associations &$parent = null ){

        /** @var Associations $lastCondition */

        $foundCondition = false;
        foreach ($conditions as $index => &$child) {

            if ($child->type == Tokens::T_CONDITION){
                $this->convertConditionCompareOperator($child->childs, $child);
                $foundCondition = true;
                continue;
            }
        }

        if ($foundCondition === false && count($conditions) == 3){
            $newCondition = new Associations();
            $newCondition->type = Tokens::T_CONDITION;
            list($firstChild, $operator, $operatorValue) = $this->convertTripleStatement($conditions);
            $newCondition->childs = [$firstChild];
            $newCondition->operator = $operator;
            $newCondition->operatorValue = $operatorValue;

            if ($parent == null){
                $conditions = $newCondition;
            }else{
                $parent->childs = [$firstChild];
                $parent->operator = $operator;
                $parent->operatorValue = $operatorValue;
            }
        }

    }

    public function __toString()
    {
        return $this->type;
    }

}