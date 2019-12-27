<?php

namespace App\Service\CompilerV2;

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
    public function __construct( Compiler $compiler, Associations $association )
    {

        $this->compiler = $compiler;

        switch ($association->type) {

            case Tokens::T_SELF:
                $this->add('12000000', 'self call');
                $this->add('01000000', 'self call');
                $this->add('49000000', 'self call');

                break;
            case Tokens::T_MATH:
                $this->doMath($association->childs);
                break;
            case Tokens::T_SCRIPT:
            case Tokens::T_PROCEDURE:
            case Tokens::T_CUSTOM_FUNCTION:


                if ($association->type == Tokens::T_PROCEDURE){
                    $compiler->gameClass->functions[strtolower($association->value)]['offset'] = Helper::fromIntToHex(count($this->compiler->codes) * 4);
                }

                $this->compiler->currentScriptName = $association->value;

                $this->compiler->evalVar->scriptStart($association->value);


                if ($association->type == Tokens::T_CUSTOM_FUNCTION){
                    $compiler->evalVar->reserveMemory($compiler->calcSize($association->return));

                }

                $scriptSize = $compiler->getScriptSize($association->value);
                $compiler->evalVar->reserveMemory($scriptSize);


                $arguments = $compiler->getScriptArgumentsByScriptName($association->value);
                foreach ($arguments as $index => $argument) {
                    $compiler->evalVar->msg = sprintf("Process Argument index %s", $index);
                    $this->add('10030000', 'init argument read');

                    $this->add('24000000', 'read argument');
                    $this->add('01000000', 'read argument');

                    $this->add('00000000', 'offset?');

                    $this->add('3f000000', 'unknown');
                    $endOffset = count($compiler->codes);
                    $this->add('00000000', 'offset?');

                    $this->add('12000000', 'read index');
                    $this->add('01000000', 'read index');
                    $this->add('00000000', 'offset');

                    $this->compiler->evalVar->ret();

                    $this->add('12000000', 'read index');
                    $this->add('01000000', 'read index');
                    $this->add('00000000', 'offset');

                    $this->compiler->evalVar->ret();

                    $this->add('0a030000', 'a argument command, for first param');

                    $this->add('15000000', 'read param');
                    $this->add('04000000', 'read param');
                    $this->add('04000000', 'offset');


                    $this->add('01000000', 'unknown');
                    $this->add('0f030000', 'unknown');
                    $compiler->codes[$endOffset]['code'] = Helper::fromIntToHex(count($compiler->codes) * 4);

                }

                foreach ($association->childs as $condition) {
                    new Evaluate($this->compiler, $condition);
                }

                if ($association->type == Tokens::T_CUSTOM_FUNCTION){
                    $this->add('13000000', 'read return value');
                    $this->add('01000000', 'read return value');
                    $this->add('04000000', 'read return value');
                    $this->add(Helper::fromIntToHex($compiler->calcSize($association->return)), 'read return value offset');
                }


                if (
                    $association->type == Tokens::T_PROCEDURE ||
                    $association->type == Tokens::T_CUSTOM_FUNCTION
                ) {
                    $this->compiler->evalVar->procedureEnd($association);
                } else {
                    $this->compiler->evalVar->scriptEnd($association->value);
                }

                break;


            case Tokens::T_ASSIGN:
                $compiler->evalVar->msg = sprintf("Process Assign %s", $association->value);

                /**
                 * Some Elements need to be initialized first
                 *
                 * Init left hand
                 */

                if ($association->varType == "vec3d") {
                    $association->type = Tokens::T_VARIABLE;
                    new Evaluate($this->compiler, $association);

                    $compiler->evalVar->msg = sprintf("Process Assign %s", $association->value);
                    $this->compiler->evalVar->ret();



                }else if ($association->isCustomFunction){
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
                }else if ($association->forIndex !== null) {
                    $association->type = Tokens::T_VARIABLE;
                    new Evaluate($this->compiler, $association);

                //we access a object attribute
                } else if (
                    $association->parent != null &&
                    $association->parent->varType == "vec3d"
                ) {
                    $association->type = Tokens::T_VARIABLE;
                    new Evaluate($this->compiler, $association->parent);

                    $this->compiler->evalVar->ret();

                    //we do not assign to the first entry
                    if ($association->parent->value . '.x' !== $association->value) {
                        $this->compiler->evalVar->moveAttributePointer($association);

                        $this->compiler->evalVar->ret();
                    }



                }else if ($association->fromArray == true) {

                    $this->readData($association, "array");

                    $this->compiler->evalVar->ret();

                    $this->compiler->evalVar->valuePointer( (int)$association->index );

                    $this->compiler->evalVar->readArray();

                }

                /**
                 * Handle right hand (value to assign)
                 */
                if (
                    $association->assign->type == Tokens::T_FLOAT ||
                    $association->assign->type == Tokens::T_INT ||
                    $association->assign->type == Tokens::T_FUNCTION ||
                    $association->assign->type == Tokens::T_MATH ||
                    $association->assign->type == Tokens::T_STRING ||
                    $association->assign->type == Tokens::T_VARIABLE
                ) {
                    new Evaluate($this->compiler, $association->assign);

                }else{

                    $rightHandReturn = $this->getVarType($association->assign);

                    $this->readData($association->assign, $rightHandReturn);
                }

                if ($association->assign->varType == 'vec3d'){
                    $compiler->evalVar->ret();
                }


                if ($association->assign->type == Tokens::T_STRING){
                    $compiler->evalVar->readSize( $association->assign->size );
                    $compiler->evalVar->retString();
                }

                /**
                 * These types accept only floats, given int need to be converted
                 */
                if ($association->varType == "float" && $association->assign->type == Tokens::T_INT){
                    $this->compiler->evalVar->int2float();
                }

                /**
                 * Block 2: Write to leftHand
                 */
                $compiler->evalVar->msg = sprintf("Assign to Variable %s", $association->value);

                if ($association->fromArray == true) {
                    $this->writeData($association, "array");
                }else if ($association->isCustomFunction){

                    $compiler->evalVar->writeToAttribute($association);
                }else if (
                    $association->varType == "string" && (
                        $association->section == "header" ||
                        $association->section == "script" ||
                        $association->section == "constant"

                    )
                ){
                    $appendix = 'write to ' . $association->value;

                    $this->add($association->section == "header" ? '21000000' : '22000000', $appendix);
                    $this->add('04000000', $appendix);
                    $this->add('04000000', $appendix);
                    $this->add(Helper::fromIntToHex($association->offset), 'String offset');

                    $this->add('12000000', $appendix);
                    $this->add('03000000', $appendix);
                    $this->add(Helper::fromIntToHex($association->size), 'Size of ' . $association->size);

                    $this->add('10000000', $appendix);
                    $this->add('04000000', $appendix);
                    $this->add('10000000', $appendix);
                    $this->add('03000000', $appendix);
                    $this->add('48000000', $appendix);

                }else if (
                    $association->parent != null &&
                    $association->parent->varType == "vec3d"
                ) {
                    $compiler->evalVar->writeToAttribute($association);
                }else{
                    $this->writeData($association, $association->varType);
                }

                break;


            case Tokens::T_VARIABLE:
                $compiler->evalVar->msg = sprintf("Use Variable %s / %s", $association->value, $association->varType);

                if ($association->isGameVar === true) {
                    $compiler->evalVar->gameVarPointer($association);
                }else if ($association->isLevelVar === true){

                    if ($association->varType == "string"){

                        $compiler->evalVar->levelVarPointerString($association);
                        $compiler->evalVar->readSize($association->size);

                    }else{
                        $compiler->evalVar->levelVarPointer($association);

                    }

                }else if (
                    $association->fromArray === true ||
                    $association->forIndex !== null
                ) {
                    $compiler->evalVar->msg = sprintf("Read from Array %s / %s", $association->value, $association->varType);
                    $compiler->evalVar->readFromArrayIndex($association);

                    if ($association->isRecord === true){

                        //it is not the first attribute, move pointer
                        if ($association->attributeName != $association->records[0][0]){
                            $compiler->evalVar->moveAttributePointer($association);
                            $compiler->evalVar->ret();
                        }

                        $compiler->evalVar->readAttribute($association);
                        $compiler->evalVar->ret();

                    }

                }else if ( $association->varType == "string") {

                    if (in_array($association->section, ['header', 'script', 'constant']) !== false){

                        $this->compiler->evalVar->memoryPointer($association);

                        //arguments for procedures need only the pointer...
                        if ($association->onlyPointer == null){
                            $this->compiler->evalVar->readSize( $association->size );
                        }

                    }else{

                        $this->compiler->evalVar->readVariable( $association );
                        $this->compiler->evalVar->readSize( 0 );
                    }
                }else if ( $association->varType == "vec3d") {

                    //we read from a procedure argument
                    if ($association->offset < 0) {
                        $this->compiler->evalVar->readVariable( $association );

                    } else {
                        $this->compiler->evalVar->memoryPointer($association);
                    }

                }else if ($association->section == "constant"){

                    $compiler->evalVar->valuePointer($association->offset);

                }else{
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

                new Evaluate($this->compiler, $association->end);


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

                $compareAgainst = false;

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

                        $isState = $compiler->getState($param->varType);
                        $compareAgainst = $isState ? 'state' : $param->varType;

                        if ($param->varType == "string"){
                            new Evaluate($this->compiler, $param);
                            $doReturn = "string";

                        }else if ($param->fromState === true) {
                            $this->compiler->evalVar->valuePointer($param->offset);

                        }else{

                            new Evaluate($this->compiler, $param);

                            $compiler->evalVar->msg = sprintf("Condition");

                            //todo part of t_variable....
                            if ($param->fromArray === true) {
                                $compiler->evalVar->readAttribute($param);
                            }

                            if (
                                isset($association->childs[$index + 1]) &&
                                $association->childs[$index + 1]->type == Tokens::T_NOT
                            ){
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

                    }else if ( $param->type == Tokens::T_FUNCTION ){
                        new Evaluate($this->compiler, $param);

                        if (isset($association->childs[$index + 1]) && $association->childs[$index + 1]->type == Tokens::T_NOT){
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
                        /*
                         * Do nothing, the NOT is handled in other calls
                         */

                    }else if ($param->type == Tokens::T_OR || $param->type == Tokens::T_AND){
                        $compiler->evalVar->msg = sprintf("Condition");

                        $compiler->evalVar->conditionOperator($param);

                        if (!$lastInCurrentChain){
                            $this->compiler->evalVar->ret("Return for next Case");
                        }

                    }else{

                        $compiler->evalVar->msg = sprintf("Condition");

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
                        $this->compiler->evalVar->ret('default '. $index . " - ". count($association->childs));
                    }


                    /**
                     * we reached a operation (>, <, =, <>...)
                     */
                    if (
                        isset($association->childs[$index + 1]) &&
                        $compiler->isTypeConditionOperation($association->childs[$index + 1]->type)
                    ) {

                        if ($compareAgainst == false){
                            $compareAgainst = $compiler->detectVarType($param);
                        }

                        $compiler->evalVar->setCompareMode($compareAgainst);

                        $compareAgainst = false;
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
                    $param->onlyPointer = $association->isProcedure == true;
                    new Evaluate($this->compiler, $param);

                    $compiler->evalVar->msg = sprintf("Process argument %s", $param->value);

                    /**
                     * Check if we need to convert the given int into a float
                     */
                    if (
                        $association->forceFloat &&
                        $association->forceFloat[$index] === true &&

                        $param->type != Tokens::T_MATH &&
                        $param->type !== Tokens::T_FLOAT &&

                        $param->varType != "float"
                    ){

                        $this->compiler->evalVar->int2float();
                    }

                    /**
                     * i guess the procedure need only the pointer and not the actual value
                     */
                    if ($association->isProcedure === true){
                        $this->compiler->evalVar->ret();
                        continue;
                    }

                    if($param->type == Tokens::T_STRING){
                        if ($param->value !== " "){
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
                            if ($param->value !== " "){
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
                            $param->type == Tokens::T_SELF ||
                            $param->type == Tokens::T_MATH ||
                            $param->type == Tokens::T_INT ||
                            $param->type == Tokens::T_CONSTANT ||
                            $param->type == Tokens::T_FLOAT ||
                            (
                                $param->type == Tokens::T_VARIABLE &&
                                (
                                    ($param->varType == "vec3d" && $param->fromArray == null) ||
                                    $param->varType == "eaicombattype" ||
                                    $param->varType == "ecollectabletype" ||
                                    $param->varType == "entityptr" ||
                                    $param->varType == "integer" ||
                                    $param->varType == "float"
                                )
                            )

                            ||

                            (
                                $param->type == Tokens::T_FUNCTION &&
                                (
                                    $param->return == "entityptr" ||
                                    $param->return == "integer" ||
                                    $param->return == "boolean"
                                )
                            )
                        ){
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
                        $this->add(Helper::fromIntToHex($association->offset), $msg . ' (return offset)');
                        return;
                    }
                }

                if (strtolower($association->value) == "writedebug"){
                    $param = $association->childs[0];
                    if ($param->varType == "string" || $param->type == Tokens::T_STRING) {

                        //Not sure about this part, a space require a different handling
                        if($param->value === " "){
                            $writeDebugFunction = $compiler->gameClass->getFunction('writedebugemptystring');
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
                        $this->add('74000000');
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
                                $extraArgument->type == Tokens::T_STRING
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
                foreach (array_reverse($association->cases) as $index => $case) {

                    $realIndex = count($cases) - $index ;

                    $this->add('24000000', 'Case ' . $realIndex);
                    $this->add('01000000');

                    if (is_array($case->value)){
                        $this->add(Helper::fromIntToHex($case->value['offset']), 'case Offset (1)');
                    }else{
                        $this->add(Helper::fromIntToHex($case->value->offset), 'case Offset (1)');
                    }

                    $this->add('3f000000');

                    //we dont know yet the correct offset, we store the position and
                    //fix it in the next loop
                    $caseStartOffsets[] = count($compiler->codes);
                    $this->add('CASE OFFSET', 'Case Offset');
                }

                foreach (array_reverse($association->cases) as $index => $case) {
                    $this->add('3c000000', 'Jump to');

                    //we dont know yet the correct offset, we store the position and
                    //fix it in the next loop
                    $caseEndOffsets[] = count($compiler->codes);
                    $this->add('END OFFSET', 'Last Case Offset');

                    //fix the missed start offsets
                    $compiler->codes[ $caseStartOffsets[$index] ]['code'] = Helper::fromIntToHex(count($compiler->codes) * 4);
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
                $this->readData($association);
                break;

            case Tokens::T_STRING:

                if ($association->value === " "){
                    $this->compiler->evalVar->valuePointer(32);

                }else{
                    $this->readData($association, 'string');
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
     * @param Associations $association
     * @return bool|mixed|string|null
     * @throws Exception
     */
    private function getVarType(Associations $association){
        if ($association->type == Tokens::T_BOOLEAN) return 'boolean';
        if ($association->type == Tokens::T_INT) return 'integer';
        if ($association->type == Tokens::T_FLOAT) return 'float';
        if ($association->type == Tokens::T_STRING) return 'string';
        if ($association->type == Tokens::T_STATE) return 'state';
        if ($association->type == Tokens::T_CONSTANT) return 'constant';
        throw new Exception("Unable to resolve type " . $association->type);
    }


    private function writeData(Associations $association, $type ){
        switch ($type) {
            case 'state':
            case 'entityptr':
            case 'integer':
            case 'boolean':
            case 'float':

                if ($association->isLevelVar){
                    $this->add('1a000000', 'LevelVar');
                    $this->add('01000000');
                    $this->add(Helper::fromIntToHex($association->offset), 'Offset');
                    $this->add('04000000');

                }else{
                    $this->add($association->section == "header" ? '16000000' : '15000000', 'Section ' . $association->section);
                    $this->add('04000000');
                    $this->add(Helper::fromIntToHex($association->offset), 'Offset');
                    $this->add('01000000');

                }
                break;
            case 'vec3d':
                $this->add('12000000');
                $this->add('03000000');
                $this->add('0c000000');
//                $this->add(Helper::fromIntToHex($association->offset), 'Offset');

                $this->add('0f000000');
                $this->add('01000000');

                $this->add('0f000000');
                $this->add('04000000');
                $this->add('44000000');
                break;
            case 'array':

                $this->compiler->evalVar->writeToAttribute($association);

                break;
        }

    }


    /**
     * @param Associations $association
     * @param $type
     * @throws Exception
     */
    private function readData($association, $type = null ){

        switch ($type){

            case 'vec3d':
            case 'array':
            case 'string':
                $this->compiler->evalVar->memoryPointer( $association );
                break;

            default:
                $this->compiler->evalVar->valuePointer($association->offset );
                if ($association->negate === true) $this->compiler->evalVar->negate($association);
                break;
        }
    }

    /**
     * @param Associations[] $associations
     * @param null $varType
     * @throws Exception
     */
    public function doMath( $associations, $varType = null ){


        /**
         * Sometimes we need to look around which vartype we have...
         */
        if ($varType == null){

            foreach ($associations as $association) {
                if (
                    $association->type == Tokens::T_INT ||
                    $association->varType == 'integer'
                ){
                    $varType = "integer";
                    break;
                }

                if (
                    $association->type == Tokens::T_FLOAT ||
                    $association->varType == 'float'
                ){
                    $varType = "float";
                    break;
                }

                if (
                    $association->type == Tokens::T_FUNCTION &&
                    $association->return !== null
                ){
                    $varType = $association->return;
                    break;
                }
            }
        }


        if ($varType == null){
            throw new Exception("Unable to detect varType");
        }


        $this->compiler->evalVar->msg = sprintf("Math Operation ");

        foreach ($associations as $index => $association) {



            if (in_array($association->type, [
                Tokens::T_ADDITION,
                Tokens::T_SUBSTRACTION,
                Tokens::T_DIVISION,
                Tokens::T_MULTIPLY,
            ])){
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

            }else{
                //we reached the last token followed by a operator
                $isLast = count($associations) == $index + 2;

                new Evaluate($this->compiler, $association);


                if ($varType == "float"  ||
                    ($varType == "integer" && $isLast == false)
                ){
                    $this->compiler->evalVar->ret();
                }


                if (
                    $varType == "float" &&
                    $association->type == Tokens::T_INT
                ){
                    $this->add('4d000000', 'integer to float2');
                    $this->compiler->evalVar->ret();
                }

            }
        }
    }
}