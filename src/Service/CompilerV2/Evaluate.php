<?php

namespace App\Service\CompilerV2;

use App\MHT;
use App\Service\Helper;
use Exception;

class Evaluate{

    public $msg = "";

    /** @var Compiler */
    private $compiler;

    /**
     * Evaluate constructor.
     * @param Compiler $compiler
     * @param Associations $association
     * @throws Exception
     */
    public function __construct( Compiler $compiler, Associations $association, $restoreMessage = null )
    {
        $compiler->logPad++;

        $compiler->log(sprintf("Call Evaluate"));




        $this->compiler = $compiler;

        switch ($association->type) {

            case Tokens::T_SELF:
                $this->compiler->evalVar->valuePointer(73); // self

                break;
            case Tokens::T_BEGIN_WRAPPER:
                foreach ($association->childs as $item) {
                    new Evaluate($this->compiler, $item);
                }

                break;
            case Tokens::T_MATH:


                /**
                 * THIS IS A VERY BAD HACK!!!
                 *
                 * time := Round(time * timefactor);
                 *
                 * timefactor is a float
                 * time is a integer
                 * Round accept (int and float ?!)
                 * Written to integer
                 *
                 *
                 * The required code did not match with any other int/float math logic...
                 * currently no idea so i clone this special part
                 */

                if (
                    $association->usedinFunction != null &&
                    strtolower($association->usedinFunction->value) == "round" &&
                    $association->childs[0]->value == 'time' &&
                    $association->childs[1]->value == 'timefactor' &&
                    $association->childs[2]->type == Tokens::T_MULTIPLY

                ) {

                    new Evaluate($this->compiler, $association->childs[0]);
                    $compiler->evalVar->ret();
                    new Evaluate($this->compiler, $association->childs[1]);

                    $rawByteCode = [

                        '10000000', //nested call return result
                        '01000000', //nested call return result

                        '0f000000', //unknown
                        '01000000', //unknown
                        '0f000000', //unknown
                        '02000000', //unknown

                        '10000000', //return string ?
                        '01000000', //return string ?
                        '10000000', //return string ?
                        '02000000', //return string ?

                        '4d000000', //int2float

                        '0f000000', //unknown
                        '02000000', //unknown

                        '10000000', //return string ?
                        '01000000', //return string ?
                        '10000000', //return string ?
                        '02000000', //return string ?

                        '52000000', //T_MULTIPLY (float)

                    ];

                    foreach ($rawByteCode as $code) {
                        $this->add($code, "HARDCODED");
                    }
                }else if (
                    count($association->childs) == 5 &&
                    $association->childs[0]->value == 'accuracy' &&
                    $association->childs[1]->value == 50 &&
                    $association->childs[2]->type == Tokens::T_SUBSTRACTION &&
                    $association->childs[3]->value == 100 &&
                    $association->childs[4]->type == Tokens::T_DIVISION

                ){


                    new Evaluate($this->compiler, $association->childs[0]);
                    $compiler->evalVar->ret();
                    new Evaluate($this->compiler, $association->childs[1]);

                    $rawByteCode = [

                        '0f000000', //parameter (temp int)
                        '04000000', //parameter (temp int)

                        '33000000', //T_SUBSTRACTION (int)
                        '04000000', //T_SUBSTRACTION (int)
                        '01000000', //T_SUBSTRACTION (int)
                        '11000000', //T_SUBSTRACTION (int)
                        '01000000', //T_SUBSTRACTION (int)
                        '04000000', //T_SUBSTRACTION (int)

                        '10000000', //nested call return result
                        '01000000', //nested call return result
                        '12000000', //parameter (read simple type (int/float...))
                        '01000000', //parameter (read simple type (int/float...))
                        '64000000', //value 100

                        '10000000', //nested call return result
                        '01000000', //nested call return result

                        '4d000000', //unknown

                        '10000000', //nested call return result
                        '01000000', //nested call return result
                        '0f000000', //unknown
                        '01000000', //unknown
                        '0f000000', //unknown
                        '02000000', //unknown
                        '10000000', //nested call return result
                        '01000000', //nested call return result
                        '10000000', //unknown
                        '02000000', //unknown
                        '4d000000', //unknown
                        '0f000000', //unknown
                        '02000000', //unknown
                        '10000000', //nested call return result
                        '01000000', //nested call return result
                        '10000000', //unknown
                        '02000000', //unknown
                        '53000000', //unknown

                    ];

                    foreach ($rawByteCode as $code) {
                        $this->add($code, "HARDCODED");
                    }
                }else{
                    $this->doMath($association->childs);

                }

                break;

            case Tokens::T_SCRIPT:
            case Tokens::T_PROCEDURE:
            case Tokens::T_CUSTOM_FUNCTION:

                $blockStartAt = count($compiler->codes);
                $this->compiler->evalVar->msg = "Create new Script Block";

                if ($association->type == Tokens::T_PROCEDURE || $association->type == Tokens::T_CUSTOM_FUNCTION){
                    $compiler->gameClass->functions[strtolower($association->value)]['offset'] = Helper::fromIntToHex(count($this->compiler->codes) * 4);
                }

                $this->compiler->currentScriptName = $association->value;

                $this->compiler->evalVar->scriptStart($association->value);

                if ($association->type == Tokens::T_CUSTOM_FUNCTION){
                    $compiler->evalVar->reserveMemory($compiler->calcSize($association->return));
                }

                $compiler->evalVar->reserveMemory(
                    $compiler->getScriptSize($association->value)
                );

                /** @var Associations[] $arguments */
                $arguments = $compiler->getScriptArgumentsByScriptName($association->value);

                if (count($arguments)){
                    $compiler->evalVar->msg = sprintf("Process %s Arguments ", count($arguments));
                    $this->add('10030000', 'Initialize Argument reading');

                    $this->add('24000000', 'read argument');
                    $this->add('01000000', 'read argument');
                    $this->add('00000000', 'offset?');

                    $this->add('3f000000', 'unknown');

                    $endOffset = count($compiler->codes);
                    $this->add('00000000', 'Line Offset');

                }

                foreach ($arguments as $index => $argument) {

                    $this->compiler->evalVar->valuePointer($index);
                    $this->compiler->evalVar->ret();

                    if (isset($argument['fallback'])){

                        new Evaluate($this->compiler, $argument['fallback'], $this->compiler->evalVar->msg);

                        if ($argument['fallback']->size == null){
                            throw new \Exception('Fallback size is null ?');
                        }

                        $this->compiler->evalVar->readSize($argument['fallback']->size);

                        $this->compiler->evalVar->retString();

                        $this->add('0c030000', 'a argument command, for first param');


                        $this->add('22000000', 'write to ?');
                        $this->add('04000000', 'write to ?');
                        $this->add('04000000', 'write to ?');
                        $this->add(Helper::fromIntToHex($argument['size']), 'size of string is ' . $argument['size']);

                        $this->add('12000000', 'write to ?');
                        $this->add('03000000', 'write to ?');
                        $this->add(Helper::fromIntToHex($argument['size']), 'size of string is ' . $argument['size']);

                        $this->add('10000000', 'write to ?');
                        $this->add('04000000', 'write to ?');
                        $this->add('10000000', 'write to ?');
                        $this->add('03000000', 'write to ?');
                        $this->add('48000000', 'write to ?');

                    }else{

                        $this->compiler->evalVar->valuePointer(0);
                        $this->compiler->evalVar->ret();

                        if ($argument['type'] == "integer"){
                            $this->add('0a030000', 'argument is integer');
                        }else if ($argument['type'] == "float"){
                            $this->add('0b030000', 'argument is float');
                        }else if ($argument['type'] == "string"){
                            $this->add('0c030000', 'argument is string');
                        }

                        $this->add('15000000', 'read from offset');
                        $this->add('04000000', 'read from offset');

                        $this->add(Helper::fromIntToHex($argument['offset']), 'offset for type ' . $argument['type']);
                        $this->add('01000000', 'read from offset');

                    }

                }

                if (count($arguments)){
                    $this->add('0f030000', 'end of arguments');
                    $compiler->codes[$endOffset]['code'] = Helper::fromIntToHex(count($compiler->codes) * 4);
                }

                foreach ($association->childs as $condition) {
                    new Evaluate($this->compiler, $condition, $this->compiler->evalVar->msg);
                }

                if ($association->type == Tokens::T_CUSTOM_FUNCTION){
                    $this->compiler->evalVar->readVariable($association, $compiler->calcSize($association->return));
                }

                if (
                    $association->type == Tokens::T_PROCEDURE ||
                    $association->type == Tokens::T_CUSTOM_FUNCTION
                ) {
                    $this->compiler->evalVar->procedureEnd($association);
                } else {
                    $this->compiler->evalVar->scriptEnd($association->value);
                }


                /**
                 * Calculate the end of each SCRIPT block
                 * Any PROCEDURE or FUNCTION will just count up the size
                 *
                 * This information is required to generate the SCPT section
                 */

                $blockSize = count($compiler->codes) - $blockStartAt;
                if ($association->type == Tokens::T_SCRIPT) {
                    $compiler->scriptBlockSizes[$association->value] = $compiler->lastScriptEnd;
                    $compiler->lastScriptEnd = $blockSize * 4;
                } else if (
                    $association->type == Tokens::T_PROCEDURE ||
                    $association->type == Tokens::T_CUSTOM_FUNCTION
                ) {

                    $compiler->lastScriptEnd += $blockSize * 4;
                }

                break;
            case Tokens::T_STATE:
                $this->compiler->evalVar->readData($association);

                break;
            case Tokens::T_ASSIGN:
                $compiler->evalVar->msg = sprintf("Process Assign %s", $association->value);

                /**
                 * Some Elements need to be initialized first
                 *
                 * Init left hand
                 */

                $compiler->log(sprintf("We assign something to %s", $association->value));

                if ($association->varType == "object" && $association->attribute == null) {
                    $compiler->log(sprintf("Assign to Object"));
                    $association->type = Tokens::T_VARIABLE;
                    new Evaluate($this->compiler, $association);

                    $this->compiler->evalVar->ret();

                }

                else if ($association->isCustomFunction){
                    $compiler->log(sprintf("Assignment is actual a Function return"));
                    $this->add('10000000', 'to custom function return');
                    $this->add('02000000', 'to custom function return');

                    $this->add('11000000', 'to custom function return');
                    $this->add('02000000', 'to custom function return');
                    $this->add('0a000000', 'to custom function return');

                    $this->add('34000000', 'to custom function return');
                    $this->add('02000000', 'to custom function return');
                    $this->add('04000000', 'to custom function return');
                    $this->add('20000000', 'to custom function return');
                    $this->add('01000000', 'to custom function return');
                    $this->add('04000000', 'to custom function return');
                    $this->add('02000000', 'to custom function return');
                    $this->add('0f000000', 'to custom function return');
                    $this->add('02000000', 'to custom function return');

                    $compiler->evalVar->ret();

                /**
                 * We assign to an array index (by id)
                 *
                 * itemsSpawned[1] := FALSE;
                 */
                }

                else if (
                    $association->varType == "array" &&
//                    $association->varType == "object" &&
                    $association->forIndex !== null
                ) {
                    $compiler->log(sprintf("Assign to Array Index"));
                    $association->type = Tokens::T_VARIABLE;
                    new Evaluate($this->compiler, $association);
//                    var_dump($association);exit;

                //we access a object attribute
                } else if (
                    $association->attribute != null
                ) {
                    $compiler->log(sprintf("Assign to Object Attribute"));
                    $association->type = Tokens::T_VARIABLE;
                    new Evaluate($this->compiler, $association);

//                    if ($association->attribute->firstAttribute === false) {
//                        $this->compiler->evalVar->moveAttributePointer($association->attribute, "T_ASSIGN");
//                        $this->compiler->evalVar->ret();
//                    }


                }



                $compiler->log(sprintf("Evaluate right side"));

               /**
                 * Handle right hand (value to assign)
                 */
                new Evaluate($this->compiler, $association->assign);
//
//                if ($association->assign->varType == 'object'){
//                    $compiler->evalVar->ret();
//                }else

                if (

                    $association->assign->varType == 'array' ||
                    $association->assign->varType == 'object'
                ){
                    if ($association->assign->attribute != null){

                        $compiler->evalVar->readAttribute($association->assign);
                    }else if($association->assign->forIndex !== null){
                        $compiler->evalVar->readAttribute($association->assign);
                    }else{
                        $compiler->evalVar->ret();

                    }

                }else if (
                    $association->assign->type == Tokens::T_STRING
                ){
                    $compiler->evalVar->readSize( $association->assign->size );
                    $compiler->evalVar->retString();
                }else if (
                    $association->assign->varType == 'string'
                ){
                    //that should be equal to Tokens::T_STRING case ....
                    $compiler->evalVar->retString();
                }else if (
                    $association->assign->type == Tokens::T_FUNCTION
                ){

                    if ($association->assign->return == null){
                        throw new Exception("No return value available for " . $association->assign->value);
                    }

                    if (
                        $association->varType == "float" &&
                        $association->assign->return == "integer"
                    ){
                        $this->compiler->evalVar->int2float("T_ASSIGN function return int");
                    }

//                    var_dump($association);
                }

                /**
                 * These types accept only floats, given int need to be converted
                 */

                if (
                    $compiler->detectVarType($association) == "float" &&
//                    $compiler->detectVarType($association) == "integer" &&
                    $association->assign->type == Tokens::T_INT
                ){
                    $this->compiler->evalVar->int2float("T_ASSIGN");
                }

                /**
                 * Block 2: Write to leftHand
                 */
                $compiler->evalVar->msg = sprintf("Assign to Variable %s", $association->value);

                $compiler->evalVar->writeToVariable($association);

                break;


            case Tokens::T_VARIABLE:


                if ($association->isGameVar === true) {
                    $this->compiler->log(sprintf("Read game var %s", $association->value));
                    $compiler->evalVar->msg = sprintf("Use Game Variable %s / %s", $association->value, $association->varType);
                    $compiler->evalVar->gameVarPointer($association);
                }

                else if ($association->isLevelVar === true){
                    $this->compiler->log(sprintf("Read level var %s", $association->value));
                    $compiler->evalVar->msg = sprintf("Use Level Variable %s / %s", $association->value, $association->varType);

                    if ($association->varType == "object") {
                        $compiler->evalVar->levelVarPointerString($association);


                        if ($association->attribute != null){
                            $this->compiler->evalVar->ret("move!");

                            if ($association->attribute->firstAttribute === false) {

                                $this->compiler->evalVar->moveAttributePointer($association->attribute, "T_VARIABLE");
                                $this->compiler->evalVar->ret("move!");
                            }

                        }


                    }else if ($association->varType == "array"){
                        $compiler->evalVar->levelVarPointerArray($association);
                        $compiler->evalVar->ret("level var array ret");

                        new Evaluate($compiler, $association->forIndex);

                        //todo calc size of array
                        $compiler->evalVar->readArray(4);

                    }else if ($association->varType == "string"){
                        $this->compiler->log(sprintf("Read String"));

                        $compiler->evalVar->levelVarPointerString($association);
                        $compiler->evalVar->readSize($association->size);

                    }else{
                        $this->compiler->log(sprintf("Read regular level var"));
                        $compiler->evalVar->levelVarPointer($association);
                    }
                }

                else if (
                    $association->fromArray === true
                ) {
                    $this->compiler->log(sprintf("Read from array %s", $association->value));

                    $compiler->evalVar->msg = sprintf("Use Array Variable %s / %s", $association->value, $association->varType);
                    $compiler->evalVar->readFromArrayIndex($association);

                    if($association->attribute !== null ) {
                        if ($association->attribute->firstAttribute === false) {

                            $this->compiler->evalVar->moveAttributePointer($association->attribute, "T_VARIABLE");
                            $this->compiler->evalVar->ret("move!");
                        }

                    }
                }

                else if ( $association->varType == "string") {
                    $this->compiler->log(sprintf("Read from string variable %s", $association->value));

                    if (in_array($association->section, ['header', 'script', 'constant']) !== false){
                        $compiler->evalVar->msg = sprintf("Use String Variable %s / %s", $association->value, $association->varType);

                        $this->compiler->evalVar->memoryPointer($association);

                        //arguments for procedures need only the pointer...
                        if ($association->onlyPointer == null){
                            $this->compiler->evalVar->readSize( $association->size );
                        }

                    }else{
                        $compiler->evalVar->msg = sprintf("Use Empty-String Variable %s / %s", $association->value, $association->varType);

                        $this->compiler->evalVar->readVariable( $association );
                        $this->compiler->evalVar->readSize( 0 );
                    }
                }

                /**
                 * We read a object Attribute
                 *
                 * WriteDebug(pos.x);
                 * pos is our $association and x is stored inside $association->attribute
                 */
                else if (
                    $association->varType == "object" &&
                    $association->attribute !== null
                ) {

                    $this->compiler->evalVar->memoryPointer($association);
                    $this->compiler->evalVar->ret("hier");
//                    $this->compiler->evalVar->readFromAttribute($association);

                    if ($association->attribute->firstAttribute === false){
                        $this->compiler->evalVar->moveAttributePointer($association->attribute);
                        $this->compiler->evalVar->ret();
                    }

                }


                else if (
                    $association->varType == "object" &&
                    $association->attribute == null
                ) {
                    $this->compiler->log(sprintf("Read from object variable %s", $association->value));

                    //we read from a procedure argument
                    if ($association->offset < 0) {
                        $compiler->evalVar->msg = sprintf("Use Object Variable from Procedure %s / %s", $association->value, $association->varType);
                        $this->compiler->evalVar->readVariable( $association );
                    } else {
                        $compiler->evalVar->msg = sprintf("Use Object Variable %s / %s", $association->value, $association->varType);
                        $this->compiler->evalVar->memoryPointer($association);
                    }

                }

                else if ($association->section == "constant"){
                    $this->compiler->log(sprintf("Read from constant variable %s", $association->value));
                    $compiler->evalVar->msg = sprintf("Use Constant Variable %s / %s", $association->value, $association->varType);
                    $compiler->evalVar->valuePointer($association->offset);
                }

                else {

                    $this->compiler->log(sprintf("Read from regular variable %s", $association->value));
                    $compiler->evalVar->msg = sprintf("Use Regular Variable %s / %s", $association->value, $association->varType);
                    $compiler->evalVar->variablePointer(
                        $association,
                        $compiler->getState($association->varType) ? 'state' : null
                    );

                }


                break;

            case Tokens::T_FOR:


                new Evaluate($this->compiler, $association->start);

                $compiler->evalVar->msg = sprintf("For statement");
                $this->add('15000000');
                $this->add('04000000');
                $this->add(Helper::fromIntToHex($association->childs[0]->offset  ), 'Variable offset');
                $this->add('01000000');

                $startOffset = count($this->compiler->codes);

                if ($association->end->type == Tokens::T_MATH){
                    $this->doMath($association->end->childs);
                }else{
                    new Evaluate($this->compiler, $association->end);
                }



                $compiler->evalVar->msg = sprintf("For statement");
                $this->add('13000000');
                $this->add('02000000');
                $this->add('04000000');
                $this->add(Helper::fromIntToHex($association->childs[0]->offset  ), 'Variable offset');


                $this->add('23000000');
                $this->add('01000000');
                $this->add('02000000');
                $this->add('41000000');

                $startOffset2 = count($this->compiler->codes);
                $this->add('offset 2', 'Offset first command');

                $this->add('3c000000', 'Jump to');
                $endOffset = count($this->compiler->codes);
                $this->add('offset', 'End Offset');

                $compiler->codes[$startOffset2]['code'] = Helper::fromIntToHex(count($compiler->codes) * 4);

                foreach ($association->onTrue as $item) {
                    new Evaluate($this->compiler, $item);
                }

                $compiler->evalVar->msg = sprintf("For statement");
                $this->add('2f000000');
                $this->add('04000000');
                $this->add(Helper::fromIntToHex($association->childs[0]->offset - 4  ), 'Variable offset');

                $this->add('3c000000', 'Jump to');
                $this->add(Helper::fromIntToHex($startOffset * 4), 'Start Offset');


                $compiler->codes[$endOffset]['code'] = Helper::fromIntToHex(count($compiler->codes) * 4);
                $this->add('30000000');
                $this->add('04000000');
                $this->add(Helper::fromIntToHex($association->childs[0]->offset - 4  ), 'Variable offset');

                break;

            case Tokens::T_CONDITION:

                $compiler->evalVar->msg = sprintf("Condition");

                foreach ($association->childs as $index => $param) {

                    $lastInCurrentChain = false;
                    if (count($association->childs) == $index + 1) $lastInCurrentChain = true;
                    if (isset($association->childs[$index + 1])){
                        $nextChild = $association->childs[$index + 1];
                        if ($nextChild->type == Tokens::T_NOT){
                            if (isset($association->childs[$index + 2])){

                                $lastInCurrentChain = $this->compiler->isTypeConditionOperatorOrOperation($association->childs[$index + 2]->type);
                            }else{
                                $lastInCurrentChain = true;
                            }
                        }else{
                            $lastInCurrentChain = $this->compiler->isTypeConditionOperatorOrOperation($association->childs[$index + 1]->type);
                        }
                    }

                    $doReturn = null;

                    if ($param->type == Tokens::T_VARIABLE) {

                        if ($param->varType == "string"){
                            new Evaluate($this->compiler, $param);
                            $doReturn = "string";

                        }else if ($param->fromState === true) {
                            $this->compiler->evalVar->valuePointer($param->offset);
                        }else{

                            new Evaluate($this->compiler, $param);

                            $compiler->evalVar->msg = sprintf("Condition");

                            if (
                                $param->fromArray === true ||
                                $param->attribute !== null
                            ) {
                                $compiler->evalVar->readAttribute($param);
                            }

                            if (
                                isset($association->childs[$index + 1]) &&
                                $association->childs[$index + 1]->type == Tokens::T_NOT
                            ){
                                $association->childs[$index + 1]->type = Tokens::T_NOP;
                                $this->compiler->evalVar->not();
                            }

                            if ($param->varType == "float"){
                                $doReturn = "default";

                            }else{
                                if ($lastInCurrentChain){
                                    $doReturn = null;
                                }else{
                                    $doReturn = "default";
                                }
                            }
                        }

                    }else if (
                        $param->type == Tokens::T_SELF ||
                        $param->type == Tokens::T_FLOAT ||
                        $param->type == Tokens::T_INT ||
                        $param->type == Tokens::T_CONSTANT
                    ){
                        new Evaluate($this->compiler, $param);

                        if ($param->type == Tokens::T_FLOAT){
                            $doReturn = "default";
                        }else{
                            if ($lastInCurrentChain){
                                $doReturn = null;
                            }else{
                                $doReturn = "default";
                            }

                        }
                    }else if ( $param->type == Tokens::T_STRING ){
                        new Evaluate($this->compiler, $param);
                        $this->compiler->evalVar->readSize($param->size);

                        $doReturn = "string";

                    }else if ( $param->type == Tokens::T_MATH ){

                        new Evaluate($this->compiler, $param);

                    }else if ( $param->type == Tokens::T_FUNCTION ){
                        new Evaluate($this->compiler, $param);

                        if (isset($association->childs[$index + 1]) && $association->childs[$index + 1]->type == Tokens::T_NOT){
                            $association->childs[$index + 1]->type = Tokens::T_NOP;
                            $this->compiler->evalVar->not();
                        }


                        if ($param->return != "string"){
                            if ($lastInCurrentChain ){
                                $doReturn = null;
                            }else{
                                $doReturn = "default";
                            }
                        }

                    }else if ($param->type == Tokens::T_NOT){
                        $this->compiler->evalVar->not();

                    }else if ($param->type == Tokens::T_NOP){
                        /*
                         * Do nothing
                         */
                    }else if ($param->type == Tokens::T_OR || $param->type == Tokens::T_AND){
                        $compiler->evalVar->msg = sprintf("Condition");

                        $compiler->evalVar->conditionOperator($param);

                        if (!$lastInCurrentChain){
                            $this->compiler->evalVar->ret("Return for next Case");
                        }

                    }else{

                        $compiler->evalVar->msg = sprintf("Condition exec");

                        $compiler->evalVar->conditionOperation($param);

                        $offset = count($compiler->codes);
                        $this->add('OFFSET', 'Offset 1');

                        $this->add('33000000');
                        $this->add('01000000');
                        $this->add('01000000');

                        $compiler->codes[$offset]['code'] = Helper::fromIntToHex(count($compiler->codes) * 4);

                        // we have a next condition chain
                        if (
                            isset($association->childs[$index + 1]) &&
                            (
                                $association->childs[$index + 1]->type != Tokens::T_NOT &&
                                $association->childs[$index + 1]->type != Tokens::T_OR &&
                                $association->childs[$index + 1]->type != Tokens::T_AND
                            )
                        ){
                            $doReturn = "default";
                        }
                    }

                    if ($doReturn == "string"){
                        $this->compiler->evalVar->retString();
                    }else if ($doReturn == "default"){
                        $this->compiler->evalVar->ret();
                    }

                    /**
                     * we reached a operation (>, <, =, <>...)
                     */
                    if (
                        isset($association->childs[$index + 1]) &&
                        $compiler->isTypeConditionOperation($association->childs[$index + 1]->type)
                    ) {

                        if ($param->type == Tokens::T_AND || $param->type == Tokens::T_OR){
                            $compareAgainst = $compiler->detectVarType($association->childs[$index - 1]);
                        }else{
                            $compareAgainst = $compiler->detectVarType($param);
                        }

                        $compiler->evalVar->setCompareMode($compareAgainst);
                    }
                }
                break;

            case Tokens::T_DO:
            case Tokens::T_IF:

                $compiler->evalVar->msg = sprintf("IF Statement ");

                $startOffset = count($compiler->codes);

                $endOffsets = [];
                foreach ($association->cases as $index => $case) {
                    $compiler->evalVar->msg = sprintf("IF Statement case %s", $index);

                    new Evaluate($this->compiler, $case->condition);

                    $compiler->evalVar->msg = sprintf("IF Statement case %s", $index);
                    $this->add('24000000');
                    $this->add('01000000');
                    $this->add('00000000');
                    $this->add('3f000000');

                    $offset = count($compiler->codes);
                    $this->add('OFFSET', "Offset 2");

                    foreach ($case->onTrue as $item) {
                        new Evaluate($this->compiler, $item);
                    }

                    $compiler->evalVar->msg = sprintf("IF Statement case %s", $index);
                    if (count($association->cases) != $index + 1 || $case->onFalse !== null){
                        $this->add('3c000000', 'Jump to');

                        $endOffsets[] = count($compiler->codes);
                        $this->add('END OFFSET', "End Offset");

                    /**
                     * The While do loops jumps back to start
                     */
                    }else if ($association->type == Tokens::T_DO ){
                        $this->add('3c000000', 'Jump to ' . ($startOffset * 4));

                        $this->add(Helper::fromIntToHex($startOffset * 4), "start Offset");
                    }

                    $compiler->codes[$offset]['code'] = Helper::fromIntToHex(count($compiler->codes) * 4);

                    if ($case->onFalse !== null){
                        foreach ($case->onFalse as $item) {
                            new Evaluate($this->compiler, $item);
                        }
                    }

                }

                foreach ($endOffsets as $offset) {
                    $compiler->codes[$offset]['code'] = Helper::fromIntToHex(count($compiler->codes) * 4);
                }

                break;

            case Tokens::T_FUNCTION:

                /**
                 * When we call a procedure, the given offset is wrong because we can not know where the procedure starts
                 * while we parsing it inside the associations.
                 *
                 * the offset is recalculated while evaluating T_PROCEDURE!
                 */
                if (isset($compiler->gameClass->functions[strtolower($association->value)])){
                    $association->offset = $compiler->gameClass->functions[strtolower($association->value)]['offset'];
                }


                /**
                 * A special handler for writedebug calls
                 *
                 * any writedebug accept countless parameters and need to be seperated
                 * into single calls
                 *
                 * writedebug('here1', 'here2');
                 *
                 * need to be split into
                 *
                 * WriteDebug('here1');
                 * WriteDebug('here2');
                 * WriteDebugFlush();
                 */
                if (
                    strtolower($association->value) == "writedebug" &&
                    count($association->childs) > 1
                ){

                    foreach ($association->childs as $index => $param) {
                        $clone = clone $association;
                        $clone->childs = [$param];
                        $clone->isLastWriteDebugParam = count($association->childs) == $index + 1;

                        new Evaluate($compiler, $clone);
                    }
                    break;
                }

                $compiler->evalVar->msg = sprintf("Process Function %s", $association->value);
                foreach ($association->childs as $index => $param) {

                    //todo: i think it is a hack, the t_variable just do to much!
                    $param->onlyPointer = $association->isProcedure == true || $association->isCustomFunction == true;
                    new Evaluate($this->compiler, $param, $compiler->evalVar->msg);

                    if (
                        $association->forceFloat &&
                        !isset($association->forceFloat[$index])
                    ){
                        throw new \Exception(sprintf("Function %s float mapping is invalid looking for index %s " , $association->value, $index));

                    }


                    if ($param->attribute !== null){

                        if (
                            $param->forIndex !== null &&
                            (
                                $param->forIndex->varType == "object" ||
                                $param->forIndex->varType == "array"
                            ) &&
                            $param->attribute->firstAttribute === true){
//                            var_dump($param);exit;

                        }else {

                            if ($param->attribute->varType == "vec3d"){


                            }else{
                                $this->compiler->evalVar->readAttribute($param, "jaja");

                            }

                        }

                    }
//
//                    if(
//                        $param->varType == "object" &&
//                        $param->attribute !== null
//                    ){
//                        $this->compiler->evalVar->readAttribute($param);
//                    }
//
//                    if(
//                        $param->varType == "array" &&
//                        $param->attribute !== null
//                    ){
//                        if ($param->attribute->firstAttribute == false){
//                            $this->compiler->evalVar->readAttribute($param);
//                        }
//                    }

                    /**
                     * Check if we need to convert the given int into a float
                     */
                    if (
                        $association->forceFloat &&
                        $association->forceFloat[$index] === true &&

                        $param->type !== Tokens::T_FLOAT &&

                        $param->varType !== "float"
                    ){

                        if ($param->type == Tokens::T_MATH){
                            if ($compiler->detectVarType($param->childs[0]) != "float"){
                                $this->compiler->evalVar->int2float("T_FUNCTION 1");

                            }
                        }

                        else if ($param->varType == "object"){
                            if (
                                $param->attribute !== null &&
                                $param->attribute->varType != "float"
                            ){
                                $this->compiler->evalVar->int2float("T_FUNCTION 1");
                            }
                        }else{
                            $this->compiler->evalVar->int2float("T_FUNCTION 2");

                            /**
                             * Nested array calls need a return
                             *
                             * SlowSweep[SlowSweepWP[index]].transition
                             */
                            if ($param->forIndex !== null && $param->forIndex->forIndex !== null){
                                $this->compiler->evalVar->ret();
                            }

                        }
                    }

                    /**
                     * i guess the procedure need only the pointer and not the actual value
                     */
                    if (
                        $association->isProcedure === true ||
                        $association->isCustomFunction === true
                    ){
                        $this->compiler->evalVar->ret();
                        continue;
                    }


                    if($param->type == Tokens::T_STRING){
                        if (strlen($param->value) !== 1){
                            $string = $compiler->strings4Script[strtolower($compiler->currentScriptName)][strtolower($param->value)];
                            $this->compiler->evalVar->readSize( $string->size );
                        }
                    }

                    if (strtolower($association->value) == "writedebug"){

                        if (
                            $param->type == Tokens::T_INT ||
                            $param->type == Tokens::T_CONSTANT
                        ){
                            $this->compiler->evalVar->ret();
                        }else if (
                            $param->type == Tokens::T_STRING
                        ) {
                            if (strlen($param->value) !== 1){
                                $this->compiler->evalVar->retString();

                            }
                        }else if (
                            $param->type == Tokens::T_VARIABLE &&
                            $param->varType == "string"
                        ) {
                            $this->compiler->evalVar->retString();
                        }

                    }else{

                        if (

                            $param->type == Tokens::T_CONDITION || //it is a workaround for nested calls...
                            $param->type == Tokens::T_SELF ||
                            $param->type == Tokens::T_MATH ||
                            $param->type == Tokens::T_INT ||
                            $param->type == Tokens::T_CONSTANT ||
                            $param->type == Tokens::T_FLOAT ||
                            (
                                $param->type == Tokens::T_VARIABLE &&
                                (
//                                    ($param->varType == "object" && $param->fromArray == null) ||
                                    $param->varType == "eaicombattype" ||
                                    $param->varType == "ecollectabletype" ||
                                    $param->varType == "effectptr" ||
                                    $param->varType == "matrixptr" ||
                                    $param->varType == "entityptr" ||
                                    $param->varType == "eaiscriptpriority" ||
                                    $param->varType == "integer" ||
                                    $param->varType == "boolean" ||
                                    $param->varType == "float"
                                )
                            )

                            ||

                            (
                                $param->type == Tokens::T_FUNCTION &&
                                (

                                    $param->return == "effectptr" ||
                                    $param->return == "matrixptr" ||
                                    $param->return == "entityptr" ||
                                    $param->return == "eaiscriptpriority" ||
                                    $param->return == "integer" ||
                                    $param->return == "boolean"
                                )
                            )
                        ){

                            $this->compiler->evalVar->ret($param->type);

                        }else if (
                            (
                                $param->type == Tokens::T_VARIABLE &&
                                $param->fromArray == true &&
                                $param->forIndex !== null &&
                                $param->forIndex->forIndex == null &&
                                $param->attribute !== null &&
                                $param->attribute->varType !== "vec3d"
                            )
                        ) {
                            $this->compiler->evalVar->ret();


                        }else if (
                            (
                                $param->type == Tokens::T_VARIABLE &&
                                $param->varType == 'object'
                            )
                        ) {
                            $this->compiler->evalVar->ret();

                        }else if (
                            (
                                $param->type == Tokens::T_VARIABLE &&
                                $param->varType == "string"
                            ) ||
                            $param->type == Tokens::T_STRING
                        ) {
                            $this->compiler->evalVar->retString();

                        }

                    }

                }

                $compiler->evalVar->msg = sprintf("Call Function %s", $association->value);

                if (
                    $association->isProcedure === true ||
                    $association->isCustomFunction === true
                ){
                    $msg = "procedure/function call";
                    $this->add('10000000', $msg);
                    $this->add('04000000', $msg);
                    $this->add('11000000', $msg);
                    $this->add('02000000', $msg);
                    $this->add('00000000', $msg);
                    $this->add('32000000', $msg);
                    $this->add('02000000', $msg);
                    $this->add('1c000000', $msg);
                    $this->add('10000000', $msg);
                    $this->add('02000000', $msg);
                    $this->add('39000000', $msg);


                    /**
                     * Custom functions can return a value and the space need to be defined here.
                     */
                    if ($association->isCustomFunction === true){
                        $compiler->storedProcedureCallOffsets[] = [
                            'value' => $association->value,
                            'offset' => count($compiler->codes)
                        ];

                        $this->add($association->offset, $msg . ' (return offset) ' . $association->offset);
                        return;
                    }
                }

                if (strtolower($association->value) == "writedebug"){
                    $param = $association->childs[0];

                    if ($param->attribute !== null) $param = $param->attribute;

                    if ($param->varType == "string" || $param->type == Tokens::T_STRING) {

                        //Not sure about this part, a space require a different handling
                        if(strlen($param->value) === 1){
                            $writeDebugFunction = $compiler->gameClass->getFunction('writedebugsinglechar');
                        }else{
                            $writeDebugFunction = $compiler->gameClass->getFunction('writedebugstring');
                        }
                    }else if ($param->varType == "integer") {
                        $writeDebugFunction = $compiler->gameClass->getFunction('writedebuginteger');
                    }else if ($param->varType == "float") {
                        $writeDebugFunction = $compiler->gameClass->getFunction('writedebugfloat');
                    }else if ($param->type == Tokens::T_FUNCTION) {
                        switch ($param->return){
                            case 'integer':
                                $writeDebugFunction = $compiler->gameClass->getFunction('writedebuginteger');
                                break;
                            case 'string':
                                $writeDebugFunction = $compiler->gameClass->getFunction('writedebugstring');
                                break;
                            case 'float':
                                $writeDebugFunction = $compiler->gameClass->getFunction('writedebugfloat');
                                break;
                            default:
                                throw new Exception("Unknown WriteDebug function return " . $param->return);


                        }
                    }else{
                        var_dump($param);
                        throw new Exception("Unknown WriteDebug function for " . $param->varType);
                    }

                    $this->add($writeDebugFunction['offset'], "Offset");

                    if ($association->isLastWriteDebugParam === null || $association->isLastWriteDebugParam === true){
                        if ($compiler->game == MHT::GAME_MANHUNT){
                            $this->add('73000000');

                        }else{
                            $this->add('74000000');

                        }
                    }
                }else{

                    if ($association->isProcedure || $association->isCustomFunction){
                        $compiler->storedProcedureCallOffsets[] = [
                            'value' => $association->value,
                            'offset' => count($compiler->codes)
                        ];
                    }

                    $this->add($association->offset, "Offset");

                    if (strtolower($association->value) == "callscript"){

                        foreach ($association->extraArguments as $index => $extraArgument) {

                            $compiler->evalVar->valuePointer($index);

                            $compiler->evalVar->ret();

                            new Evaluate($this->compiler, $extraArgument);

                            if (
//                                $extraArgument->type == Tokens::T_VARIABLE &&
                                $extraArgument->varType == "array" ||
                                $extraArgument->varType == "object"
                            ) {

                                $this->compiler->evalVar->readAttribute($extraArgument);

                                $compiler->evalVar->ret();

                                if ($extraArgument->attribute->varType == "float") {
                                    $this->add("08030000", "float arg");
                                }else if ($extraArgument->attribute->varType == "integer"){
                                    $this->add("07030000", "integer arg");
                                }else{
                                    var_dump($extraArgument);
                                    throw new Exception("Object return type not defined for callscript");
                                }

                            }

                            else if (
                                $extraArgument->type == Tokens::T_STRING ||
                                $extraArgument->varType == 'string'
                            ){

                                $compiler->evalVar->readSize($extraArgument->size);
                                $compiler->evalVar->retString();
                                $this->add("09030000", "string arg");
                            }else{
                                $compiler->evalVar->ret();

                                $this->add("07030000", "int arg");
                            }
                        }

                        $this->add("0e030000", "hidden callscript");
                    }
                }

                break;

            case Tokens::T_SWITCH:
                /** @var Associations $caseVariable */
                $caseVariable = $association->value;

                $compiler->evalVar->msg = sprintf("Switch %s", $caseVariable->value);

                new Evaluate($this->compiler, $caseVariable);

                $caseStartOffsets = [];
                $caseEndOffsets = [];

                $cases = array_reverse($association->cases);
                foreach ($cases as $index => $case) {
//                    var_dump($case->value);
                    if (
                        !$case->value instanceof Associations ||
                        $case->value->type != Tokens::T_ELSE
                    ) continue;
                    Helper::moveArrayIndexToBottom($cases, $index);
                }

                foreach ($cases as $index => $case) {
//                    if ($case->value->type == Tokens::T_ELSE) continue;

                    if (
                        $case->value instanceof Associations &&
                        $case->value->type == Tokens::T_ELSE
                    ) continue;


                    $realIndex =  $index ;

                    $this->add('24000000', 'Case ' . $realIndex);
                    $this->add('01000000');

                    if (is_array($case->value)){
                        $this->add(Helper::fromIntToHex($case->value['offset']), 'case Offset (1)');
                    }else{
                        $this->add(Helper::fromIntToHex($case->value->offset), 'case Offset (2)');
                    }

                    $this->add('3f000000', "Jo");

                    //we dont know yet the correct offset, we store the position and
                    //fix it in the next loop
                    $caseStartOffsets[$index] = count($compiler->codes);
                    $this->add('CASE OFFSET', 'Case Offset start');
                }
//
//

                $elseCase = false;
                foreach ($cases as $index => $case) {


                    if (
                        !$case->value instanceof Associations ||
                        $case->value->type != Tokens::T_ELSE
                    ) continue;

//                    if ($case->value->type != Tokens::T_ELSE) continue;

                    $this->add('3c000000', 'Jump to ELSE');

                    $elseCase = count($compiler->codes);
                    $this->add('ELSE OFFSET', 'ELSE Case Offset');
                }



                foreach ($cases as $index => $case) {

//                    if ($case->value->type == Tokens::T_ELSE) continue;

                    $this->add('3c000000', 'Jump to Case ' . $index);

                    //we dont know yet the correct offset, we store the position and
                    //fix it in the next loop
                    $caseEndOffsets[] = count($compiler->codes);
                    $this->add('END OFFSET', 'Last Case Offset');

                    //fix the missed start offsets

                    if  (
                        $case->value instanceof Associations &&
                        $case->value->type == Tokens::T_ELSE
                    ){
                        $compiler->codes[ $elseCase ]['code'] = Helper::fromIntToHex(count($compiler->codes) * 4);

                    }else{

                        $compiler->codes[ $caseStartOffsets[$index] ]['code'] = Helper::fromIntToHex(count($compiler->codes) * 4);
                    }
                    new Evaluate($this->compiler, $case);
                }

                $this->add('3c000000', 'Jump to');

                $caseEndOffsets[] = count($compiler->codes);
                $this->add('END OFFSET', 'Last Case Offset');

                //fix the missed end offsets
                foreach ($caseEndOffsets as $caseEndOffset) {
                    $compiler->codes[ $caseEndOffset ]['code'] = Helper::fromIntToHex(count($compiler->codes) * 4);
                }

                break;

            case Tokens::T_CONSTANT:
            case Tokens::T_FLOAT:
            case Tokens::T_BOOLEAN:
            case Tokens::T_INT:
                $compiler->evalVar->msg = sprintf("Read simple value %s", $association->value);


                $this->compiler->evalVar->readData($association);
                break;

            case Tokens::T_STRING:

                if (strlen($association->value) == 1) {
                    $this->compiler->evalVar->valuePointer(ord($association->value));
                }else{
                    $this->compiler->evalVar->readData($association);
                }
                break;
            case Tokens::T_CASE:

                foreach ($association->onTrue as $condition) {
                    new Evaluate($this->compiler, $condition);
                }

                break;

            default:

                var_dump($association);
                throw new Exception(sprintf("Unable to evaluate %s ", $association->type));
        }


        if ($restoreMessage !== null){
            $compiler->evalVar->msg = $restoreMessage;
        }

        $compiler->logPad--;

    }

    private function add($code, $appendix = null ){
        $msg = $this->compiler->evalVar->msg;

        if (!is_null($appendix)) $msg .= ' | ' . $appendix;

        $this->compiler->codes[] = [
            'code' => $code,
            'msg' => $msg
        ];
    }

    /**
     * @param Associations[] $associations
     * @throws Exception
     */
    public function doMath( $associations ){


        $this->compiler->evalVar->msg = sprintf("Math Operation ");

        $varType = $this->compiler->detectVarType($associations[0]);

        foreach ($associations as $index => $association) {

            if ($this->compiler->isTypeMathOperator($association->type)){
                $isLast = count($associations) == $index + 1;

                $this->compiler->evalVar->math($association->type, $varType);

                if ($isLast == false) $this->compiler->evalVar->ret();

                /**
                 * Looks like a hack but the extra return appears only in
                 * function parameters that ends with a multiply operation...
                 */
                if (
                    $varType != "float" &&
                    $isLast == true &&
                    $association->type == Tokens::T_MULTIPLY
                ){
                    $this->compiler->evalVar->ret();
                }

            }else {

                //we reached the last token followed by a operator
                $isLast = count($associations) == $index + 2;

//
                $beforeOperator = false;
                if (isset($associations[$index + 1])){
                    $beforeOperator = $this->compiler->isTypeMathOperator($associations[$index + 1]->type);
//                    var_dump($beforeOperator);
                }

                new Evaluate($this->compiler, $association);

                if ($association->type == Tokens::T_VARIABLE){
                    if (
                        $this->compiler->detectVarType($association) !== "float" &&
                        $varType !== $this->compiler->detectVarType($association)
                    ){
                        $this->compiler->evalVar->int2float("T_MATH");
                    }
                }

                if (
                    $association->attribute !== null ||
                    $association->fromArray

                ) {
                    $this->compiler->evalVar->readAttribute($association);

//                    $this->compiler->evalVar->readFromAttribute($association);
                }

                if ($varType == "float" || ($varType == "integer" && $isLast == false && $beforeOperator == false)
                ){
//                    if ($beforeOperator){
//                        var_dump($associations);
//                    }

                    $this->compiler->evalVar->ret($varType);
                }else if($isLast == false){
//
//                    if ($varType == 'string'){
//                        var_dump($associations);
//                        exit;
//                    }
//                    echo $association->toCsv() . "\n";

                }

                if ($varType == "float" ){

                    if ($association->type == Tokens::T_INT) {
                        var_dump("convert! 1");
                        $this->add('4d000000', 'integer to float3');
                        $this->compiler->evalVar->ret("3");

                    }else if (
                        $association->type == Tokens::T_FUNCTION &&
                        $association->return != $varType
                    ){
                        if ($association->return === null){
                            throw new Exception(sprintf(" No return defined for Function %s", $association->value));
                        }

                        $this->add('4d000000', 'integer to float1');
                        $this->compiler->evalVar->ret("3");

                    }

                }



                if ($beforeOperator == false){
                    $varType = $this->compiler->detectVarType($association);

                    if ($varType == null){
                        throw new Exception("Unable to detect varType");
                    }

                }

            }
        }
    }
}