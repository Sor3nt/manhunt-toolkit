<?php
namespace App\Service\CompilerV2;

use App\MHT;
use App\Service\Helper;
use Exception;

class Compiler
{

    public $game;
    public $platform;
    public $debug = false;

    /** @var null|Compiler  */
    public $gameScript = null;

    /** @var null|Compiler  */
    public $levelScript = null;

    public $tokens = [];
    public $current = 0;

    /** @var ManhuntDefault */
    public $gameClass;

    public $variables = [];
    public $strings = [];

    public $strings4Script = [];
    public $codes = [];

    public $currentSection = "header";
    public $currentScriptName = "";
    public $currentBlockType = "";

    public $mlsScriptMain = "levelscript";
    public $mlsEntityName = "demo_level";
    public $mlsEntityType = "et_level";

    /*
     * Offset holder
     */
    public $offsetGlobalVariable = 0;
    public $offsetScriptVariable = 0;
    public $offsetProcedureVariable = 0;
    public $offsetProcedureScripts = 0;
    public $offsetConstants = 0;

    public $evalVar;

    public $storedProcedureCallOffsets = [];

    public function debug($stuff, $exit = true){
        if ($this->debug){
            var_dump($stuff);
            if ($exit) exit;
        }
    }

    public function __construct($source, $game, $platform)
    {

        $this->evalVar = new EvaluateVariable($this);

        $this->game = $game;
        $this->platform = $platform;
        $this->gameClass = $this->game == MHT::GAME_MANHUNT ? new Manhunt() : new Manhunt2();

        // remove comments / unused code
        $source = preg_replace("/{(.|\s)*}/mU", "", $source);

        //extract all used strings
        preg_match_all("/['|\"](.*)['|\"]/U", $source, $strings);
        $this->strings = array_values(array_unique($strings[1]));

        $newStrings = array_values(array_unique($strings[0]));

        //replace usage with dummy to avoid parsing errors
        foreach ($newStrings as $index => $string) {
            $source = str_replace($string, "'str_" . $index . '\'', $source );
        }

        /**
         * Avoid wrong associations
         */
        //leftover from the exporter (todo)
        $source = str_replace("\00", "", $source);

        //a special comment
        $source = str_replace("}}", "}", $source);

        //split the parts a little bit more (todo: combine them)
        $source = preg_replace("/\//", " / ", $source);
        $source = preg_replace("/\(/", " ( ", $source);
        $source = preg_replace("/\)/", " ) ", $source);
        $source = preg_replace("/\*/", " * ", $source);

        $source = preg_replace("/</", " < ", $source);
        $source = preg_replace("/>/", " > ", $source);

        $source = preg_replace("/\+/", " + ", $source);
        $source = preg_replace("/,/", " , ", $source);
        $source = preg_replace("/\[/", " [ ", $source);
        $source = preg_replace("/]/", " ] ", $source);

        $source = preg_replace("/:/", " : ", $source);
        $source = preg_replace("/=/", " = ", $source);


        $source = preg_replace("/:\s*=/", " := ", $source);
        $source = preg_replace("/>\s*=/", " >= ", $source);
        $source = preg_replace("/<\s*=/", " <= ", $source);
        $source = preg_replace("/<\s*>/", " <> ", $source);

        /**
         * Fetch all chars except whitespaces and line end sign ";"
         */
        preg_match_all("/([^\s|^;]+)/", $source, $tokens);
        $this->tokens = $tokens[0];
    }


    /**
     * We need to parse every string in the original order of the source code.
     * inside one script block the strings are unique but repeat in next blocks again maybe
     */
    private function searchStrings(){

        $current = 0;
        $currentScriptName = "";
        while ($current < count($this->tokens)) {
            $token = $this->tokens[$current];

            switch(strtolower($token)){
                case 'script':
                case 'procedure':
                case 'function':
                    $currentScriptName = $this->tokens[$current + 1];
                    break;

            }

            if (substr($token, -1, 1) == "'"){
                $this->addString(substr($token, 1, -1), $currentScriptName);
            }

            $current++;
        }

    }

    /**
     * @return array
     * @throws Exception
     */
    public function compile(){

        // Search and add all used strings
        $this->searchStrings();

        // Build the AST (Abstract syntax tree)
        $associated = [];
        while ($this->current < count($this->tokens)){

            $association = new Associations($this);
            if ($association->type !== Tokens::T_NOP) $associated[] = $association;

        }

        /**
         * Handle FORWARD command
         *
         * procedure InitAI; FORWARD;
         */

        /**
         * Split association between forward commands and script blocks
         *
         * @var Associations[] $needForward
         */
        $needForward = [];
        $associationRearranged = [];
        foreach ($associated as $association) {
            if ($association->type == Tokens::T_FORWARD){
                $needForward[] = $association;
            }else{
                $associationRearranged[] = $association;
            }
        }

        /**
         * Search the forwards and move them to the top
         */
        foreach (array_reverse($needForward) as $toForward) {
            foreach ($associationRearranged as $index => $association) {
                if ($toForward->value == $association->value){
                    Helper::moveArrayIndexToTop($associationRearranged, $index);
                    breaK;
                }

            }
        }

        // Fix the indices.
        $associationRearranged = array_values($associationRearranged);
//$this->debug($associationRearranged);
        foreach ($associationRearranged as $association) {
            new Evaluate($this, $association);
        }

        /**
         * Procedures can be called BEFORE the actual bytecode is written.
         * In this case we can not know where our procedure starts
         * that is why we fix it afterwards
         */
        foreach ($this->storedProcedureCallOffsets as $call) {
            $this->codes[$call['offset']]['code'] = $this->gameClass->functions[$call['value']]['offset'];
        }

        return [

            'CODE' => $this->codes
        ];
    }


    public function getState($name, $state = null){
        if (!isset($this->gameClass->types[$name])) return false;
        $states = $this->gameClass->types[$name];

        foreach ($states['types'] as $_state) {
            if ($_state['name'] == $state) return $_state;
        }

        return $this->gameClass->types[$name];
    }

    public function addString($string, $currentScriptName){
        $currentScriptName = strtolower($currentScriptName);
        $stringIndex = substr($string, 4);
        $string = $this->strings[$stringIndex];

        if (
            isset($this->strings4Script[$currentScriptName]) &&
            isset($this->strings4Script[$currentScriptName][strtolower($string)])
        ){
            return;
        }

        if (!isset($this->strings4Script[$currentScriptName]))
            $this->strings4Script[$currentScriptName] = [];

        $len = strlen($string) + 1;

        $newString = new Associations();
        $newString->value = $string;
        $newString->section = "header";
        $newString->offset = $this->offsetGlobalVariable;
        $newString->scriptName = $currentScriptName;
        $newString->size = $len;

        $this->strings4Script[$currentScriptName][strtolower($string)] = $newString;

        if (4 - $len % 4 != 0) $len += 4 - $len % 4;

        $this->offsetGlobalVariable += $len;
    }

    public function addStates($name, $states ){

        $types = [];
        foreach ($states as $index => $state) {
            $types[] = [
                'name' => $state,
                'offset' => $index
            ];

            $this->variables[] = [
                'name' => $state,
                'type' => 'integer',
                'size' => 1,
                'offset' => $index,
                'fromState' => true,
                'section' => 'header',
                'scriptName' => 'header'
            ];
        }

        $this->gameClass->types[$name] = [
            'types' => $types
        ];

    }
    public function addConstants( $name, $value, $type){

        if ($type == "real") $type = "float";

        $size = 4;
        if ($type == "string"){
            $stringIndex = substr($value, 4);
            $value = $this->strings[$stringIndex];

            $size = strlen($value) + 1;
        }

        $this->variables[] = [
            'name' => $name,
            'value' => $value,
            'size' => $size,
            'offset' => $this->offsetConstants,
            'type' => $type,
            'varType' => $type,
            'section' => 'header',
            'scriptName' => 'header'
        ];

        $this->offsetConstants += $size + (4 - $size % 4);
    }

    public function addVariable( $data ){

        if ($data['type'] == 'real') $data['type'] = "float";

        if (is_null($data['size'])) $data['size'] = $this->calcSize($data['type']);

        if (!isset($data['offset'])){

            if ($data['section'] == "header"){

                $size = $data['size'];
                if ($data['type'] == "string"){
                    if ($size % 4 != 0){
                        $size += $data['size'] % 4;
                    }else{
                        $size += 4;
                    }
                }

                $offset = $this->offsetGlobalVariable;
                $this->offsetGlobalVariable += $size;

            }else if ($data['section'] == "script"){

                $this->offsetScriptVariable += $data['size'];

                $offset = $this->offsetScriptVariable;

                 if ($data['type'] == "string"){

                    if ($data['size'] % 4 != 0){
                        $this->offsetScriptVariable += $data['size'] % 4;
                    }else{
                        $this->offsetScriptVariable += 4;
                    }
                }

            }else{
                /**
                 * We process some custom_function / procedure variables
                 *
                 * the start of the offset is -12 and any size will be subtracted from the offset
                 *
                 * looks like it is a 4 byte pointer list
                 */

                $offset = $this->offsetProcedureVariable;
                $this->offsetProcedureVariable -= 4;
            }
        }else{
            $offset = $data['offset'];
        }

        /**
         * Overwrite the calculated offset with the game_var / level_var offset
         */
        if ($this->gameScript !== null){
            $gameVar = $this->gameScript->getVariable($data['name']);
            if ($gameVar) $offset = $gameVar['offset'];
        }

        if ($this->levelScript !== null){
            $levelVar = $this->levelScript->getVariable($data['name']);
            if ($levelVar) $offset = $levelVar['offset'];
        }


        $master = array_merge([], $data);
        $master['size'] = $master['type'] == "vec3d" || $master['type'] == "rgbaint" ? 0 : $master['size'];
        $master['offset'] = $offset;
        $master['scriptName'] = $this->currentScriptName;

        $this->variables[] = $master;

        $attributes = [];
        if ($master['type'] == "vec3d") $attributes = ["x" => 'float', "y" => 'float', "z" => 'float'];
        if ($master['type'] == "rgbaint") $attributes = ["red" => 'integer', "green" => 'integer', "blue" => 'integer', "alpha" => 'integer'];

        $index = 0;
        foreach ($attributes as $entry => $type) {

            $attribute = array_merge([], $data);

            $attribute['name'] = $master['name'] . '.' . $entry;
            $attribute['type'] = $type;
            $attribute['size'] = 4;
            $attribute['offset'] = $index * 4;
            $attribute['parent'] = $master;
            $attribute['scriptName'] = $this->currentScriptName;

            $this->variables[] = $attribute;

            $index++;
        }

        return $master;
    }

    public function addCustomFunction( $name, $type = Tokens::T_CUSTOM_FUNCTION ){

        $this->gameClass->functions[strtolower($name)] = [
            'name' => strtolower($name),
            'offset' => Helper::fromIntToHex($this->offsetProcedureScripts),
            'type' => $type
        ];

        $this->offsetProcedureScripts += 4;

    }

    public function getVariablesByScriptName($scriptName){

        $found = [];
        foreach ($this->variables as $variable) {
            if ($variable['scriptName'] == $scriptName) $found[] = $variable;
        }

        return $found;
    }
    public function getProcedureArgumentsByScriptName($scriptName){

        $found = [];
        foreach ($this->variables as $variable) {
            if (
                // do not count object attributes
                !isset($variable['parent']) &&

                $variable['scriptName'] == $scriptName &&
                $variable['scriptName'] == $variable['section']
            ) $found[] = $variable;
        }

        return $found;
    }

    public function getScriptArgumentsByScriptName($scriptName){

        $found = [];
        foreach ($this->variables as $variable) {
            if (
                $variable['scriptName'] == $scriptName &&
                $variable['isArgument'] == true
//                $variable['scriptName'] == $variable['section']
            ) $found[] = $variable;
        }

        return $found;
    }

    public function getScriptSize($scriptName){
        $size = 0;
        $variables = $this->getVariablesByScriptName($scriptName);

        foreach ($variables as $variable) {

            //is this equal, it mean we process a parameter not a regular variable
            if ($variable['section'] == $variable['scriptName']) continue;
            if (isset($variable['index'])) continue;

            $size += $variable['size'];

            if ($variable['type'] == "string"){
                if ($variable['size'] % 4 != 0){
                    $size += $variable['size'] % 4;
                }else{
                    $size += 4;
                }
            }
        }

        return $size;
    }

    public function getVariable($name){

        /**
         * Main prio has the script scope, then the header and constants
         */
        $index = strtolower($name);
        foreach ($this->variables as $variable) {

            if ($variable['name'] == $index){
                if (
                    $variable['scriptName'] == $this->currentScriptName

                ){
                    return $variable;
                }
            }
        }

        foreach ($this->variables as $variable) {

            if ($variable['name'] == $index){
                if (
                    $variable['section'] != "script"
                ){
                    return $variable;
                }
            }
        }

        return false;
    }

    public function consumeIfTrue( $val ){
        if ($this->getToken() == $val){
            $this->current++;
            return true;
        }

        return false;
    }

    public function getToken( $current = null, $toLower = true){
        $index = $current == null ? $this->current : $current;
        if (!isset($this->tokens[ $index ])) return false;

        $token = $this->tokens[ $index ];

        return $toLower ? strtolower($token) : $token;
    }

    public function consume($toLower = true){
        $token = $this->tokens[ $this->current++ ];

        return $toLower ? strtolower($token) : $token;
    }

    public function buildDebugString($current = null){

        $debug = "\n\n... ";

        $errorCurrent = is_null($current) ? $this->current : $current;
        $current -= 10;

        for($i = -10; $i < 10; $i++){
            $current++;
            if (isset($this->tokens[$current])){

                if ($errorCurrent == $current){
                    $debug .= "->" .$this->tokens[$current] . '<- ';
                }else{
                    $debug .= $this->tokens[$current] . ' ';
                }
            }
        }

        $debug .= "...";

        return $debug;
    }

    /**
     * @param string $msg
     * @param int $shift
     * @throws Exception
     */
    public function raiseException($msg = "", $shift = 1){
        throw new Exception(
            sprintf(
                "%s. Could not convert Value %s. Arround here %s",
                $msg,
                $this->tokens[$this->current - $shift],
                $this->buildDebugString($this->current - $shift)
            )
        );
    }

    public function calcSize( $type ){

        $size = 4;
        switch ($type){
            case 'vec3d':
                $size = 12;
                break;
        }

        return $size;
    }

    public function getCODE(){
        $result = [];
        foreach ($this->codes as $code) {
            $result[] = $code['code'];
        }

        return $result;
    }

    public function validateCode($compareCode){
        foreach ($this->codes as $index => $code) {
            if ($code['code'] != $compareCode[$index]){
                return false;
            }
        }

        return true;
    }

    public function isTypeConditionOperatorOrOperator($type){
        switch ($type){
            case Tokens::T_OR:
            case Tokens::T_AND:
            case Tokens::T_IS_GREATER_EQUAL:
            case Tokens::T_IS_GREATER:
            case Tokens::T_IS_SMALLER:
            case Tokens::T_IS_SMALLER_EQUAL:
            case Tokens::T_IS_EQUAL:
            case Tokens::T_IS_NOT_EQUAL:
                return true;
        }

        return false;
    }
}
