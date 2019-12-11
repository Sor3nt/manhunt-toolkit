<?php
namespace App\Service\CompilerV2;

use App\MHT;
use App\Service\Helper;

class Compiler
{

    public $game;
    public $platform;

    public $tokens = [];
    public $current = 0;

    /** @var ManhuntDefault */
    public $gameClass;

    public $variables = [];
    public $strings = [];
    public $stringsAll = [];

    public $strings4Script = [];
    public $codes = [];

    public $currentSection = "header";
    public $currentScriptName = "";
    public $currentBlockType = "";

    public $mlsScriptMain = "levelscript";
    public $mlsEntityName = "demo_level";
    public $mlsEntityType = "et_level";

    public $offsetString = 0;
    public $offsetGlobalVariable = 0;
    public $offsetScriptVariable = 0;
    public $offsetProcedureVariable = -12;
    public $offsetProcedureScripts = 0;
    public $offsetConstants = 0;

    public $evalVar;

    public function __construct($source, $game, $platform, $parentScript = false)
    {

        $this->evalVar = new EvaluateVariable($this);

        $this->game = $game;
        $this->platform = $platform;
        $this->gameClass = $this->game == MHT::GAME_MANHUNT ? new Manhunt() : new Manhunt2();


        // remove comments / unused code
        $source = preg_replace("/\{(.|\s)*\}/mU", "", $source);


        //extract all used strings
        preg_match_all("/['|\"](.*)['|\"]/U", $source, $strings);
        $this->strings = array_values(array_unique($strings[1]));

        $newStrings = array_values(array_unique($strings[0]));

        //replace usage with dummy to avoid parsing errors
        foreach ($newStrings as $index => $string) {
            $source = str_replace($string, "'str_" . $index . '\'', $source );
        }

        if (count($newStrings) !== count(array_unique($strings[1]))){
            die("eh damn, the strings did not match....");
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
        $source = preg_replace("/\+/", " + ", $source);
        $source = preg_replace("/\,/", " , ", $source);
        $source = preg_replace("/\[/", " [ ", $source);
        $source = preg_replace("/\]/", " ] ", $source);
        $source = preg_replace("/\:[^=]/", " : ", $source);
        $source = preg_replace("/\:\=/", " := ", $source);

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
     * @throws \Exception
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
//var_dump($associationRearranged);exit;
        foreach ($associationRearranged as $association) {
            new Evaluate($this, $association);
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

        //        $this->strings4Script[$currentScriptName][strtolower($string)] = [
//            'value' => $string,
//            'offset' => $this->offsetGlobalVariable,
//            'scriptName' => $currentScriptName,
//            'size' => $len
//        ];

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
                'sizeWithoutPad4' => 1,
                'offset' => $index,
                'section' => 'header',
                'scriptName' => 'header'
            ];
        }

//        var_dump("Add Type: " . $name . " with types " . print_r($types, true) );

        $this->gameClass->types[$name] = [
            'types' => $types
        ];

    }
    public function addConstants( $name, $value, $type){
        var_dump("Add Constant: " . $name . " with value " . $value );

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
            'sizeWithoutPad4' => $size,
            'offset' => $this->offsetConstants,
            'type' => $type,
            'varType' => $type,
            'section' => 'header',
            'scriptName' => 'header'

        ];

        $this->offsetConstants += $size + (4 - $size % 4);


    }

    public function addVariable( $name, $type, $size = null, $isLevelVar = false, $isGameVar = false, $section = null, $fromArray = false, $index = null ){

        if ($type == 'real') $type = "float";

        if (is_null($size)) $size = $this->calcSize($type);
        $sizeWithoutPad4 = $size;

        if ($section == "header"){

            $offset = $this->offsetGlobalVariable;
            $this->offsetGlobalVariable += $size;
            $this->offsetGlobalVariable += $this->offsetGlobalVariable % 4;

        }else if ($section == "script"){

            $this->offsetScriptVariable += $size;

            $offset = $this->offsetScriptVariable;

            $this->offsetScriptVariable +=  $this->offsetScriptVariable % 4;
        }else{
            /**
             * We process some custom_function / procedure variables
             *
             * the start of the offset is -12 and any size will be subtracted from the offset
             *
             * looks like it is a 4 byte pointer list
             */

            $offset = $this->offsetProcedureVariable;
            $this->offsetProcedureVariable -= $size + ($size % 4);
        }

        $master = [
            'name' => strtolower($name),
            'type' => $type,
            'size' => $type == "vec3d" || $type == "rgbaint" ? 0 : $size,
            'sizeWithoutPad4' => $sizeWithoutPad4,
            'isLevelVar' => $isLevelVar,
            'isGameVar' => $isGameVar,
            'offset' => $offset,
            'section' => $section,
            'index' => $index,
            'scriptName' => $this->currentScriptName,
            'fromArray' => $fromArray
        ];

        $this->variables[] = $master;


        if ($type == "vec3d") {

            foreach (["x", "y", "z"] as $index => $entry) {

                $attributeName = strtolower($name) . '.' . $entry;


                $this->variables[] = [
                    'name' => $attributeName,
                    'type' => 'float',
                    'isLevelVar' => $isLevelVar,
                    'isGameVar' => $isGameVar,
                    'size' => 4,
                    'offset' => $index * 4,
                    'section' => $section,
                    'scriptName' => $this->currentScriptName,
                    'parent' => $master
                ];
            }
        }else if ($type == "rgbaint"){

            foreach (["red", "green", "blue", "alpha"] as $entry) {

                $attributeName = strtolower($name) . '.' . $entry;

                $this->variables[] = [
                    'name' => $attributeName,
                    'type' => 'integer',
                    'isLevelVar' => $isLevelVar,
                    'isGameVar' => $isGameVar,
                    'size' => 4,
                    'offset' => '123456789',
                    'section' => $section,
                    'scriptName' => $this->currentScriptName,
                    'parent' => $master
                ];
            }

        }

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
    public function getArgumentsByScriptName($scriptName){

        $found = [];
        foreach ($this->variables as $variable) {
            if (
                $variable['scriptName'] == $scriptName &&
                $variable['scriptName'] == $variable['section']
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

            $size += $variable['size'];
        }

        if ($size % 4 != 0 ) $size += $size % 4;

        return $size;
    }

    public function getVariable($name){

        $index = strtolower($name);
        foreach ($this->variables as $variable) {
            if ($variable['name'] == $index){
                if (
                    $variable['section'] == "header" ||
                    $variable['scriptName'] == $this->currentScriptName
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
     * @param int $shift
     * @throws \Exception
     */
    public function raiseException($msg = "", $shift = 1){

        throw new \Exception(
            sprintf(
                "%s. Could not convert Value %s. Arround here %s",
                $msg,
                $this->tokens[$this->current - $shift],
                $this->buildDebugString($this->current - $shift)
            )
        );
    }

    public function calcSize( $type, $addString4Bytes = false ){

        $size = 4;
        switch ($type){
            case 'vec3d':
                $size = 12;
                break;
        }

        if ($addString4Bytes && $size % 4 == 0) $size += 4;

        return $size;
    }

    public function validateCode($compareCode){
        foreach ($this->codes as $index => $code) {
            if ($code['code'] != $compareCode[$index]) return false;
        }

        return true;
    }
}
