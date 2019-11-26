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
    public $codes = [];

    public $currentSection = "header";
    public $currentScriptName = "";

    public $mlsScriptMain = "levelscript";
    public $mlsEntityName = "demo_level";
    public $mlsEntityType = "et_level";

    public $offsetString = 0;
    public $offsetGlobalVariable = 0;
    public $offsetScriptVariable = 0;

    public function __construct($source, $game, $platform, $parentScript = false)
    {

        $this->game = $game;
        $this->platform = $platform;
        $this->gameClass = $this->game == MHT::GAME_MANHUNT ? new Manhunt() : new Manhunt2();


        // remove comments / unused code
        $source = preg_replace("/\{(.|\s)*\}/mU", "", $source);


        //extract all used strings
        preg_match_all("/['|\"](.+)['|\"]/U", $source, $strings);
        $this->strings = array_unique($strings[1]);

        foreach ($this->strings as &$string) {

            $len = strlen($string) + 1;

            $string = [
                'value' => $string,
                'offset' => $this->offsetGlobalVariable,
                'size' => $len
            ];

            if (4 - $len % 4 != 0) $len += 4 - $len % 4;

            $this->offsetGlobalVariable += $len;

        }

        unset($string);

        //replace usage with dummy
        foreach ($strings[0] as $index => $string) {
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
        $source = preg_replace("/\(/", " ( ", $source);
        $source = preg_replace("/\)/", " ) ", $source);
        $source = preg_replace("/\+/", " + ", $source);
//        $source = preg_replace("/\-/", " - ", $source);
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
     * @throws \Exception
     */
    public function compile(){
        $associated = [];
        while ($this->current < count($this->tokens)){

            $association = new Associations($this);
            if ($association->type == Tokens::T_NOP) continue;
            $associated[] = $association;
        }


//        var_dump($associated);
//        exit;
        //TODO FORWARD HANDLING

        foreach ($associated as $association) {
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


    public function addStates($name, $states ){

        $types = [];
        foreach ($states as $index => $state) {
            $types[] = [
                'name' => $state,
                'offset' => $index
            ];
        }

        var_dump("Add Type: " . $name . " with types " . print_r($types, true) );

        $this->gameClass->types[$name] = [
            'types' => $types
        ];

    }
    public function addConstants( $name, $value ){
        var_dump("Add Constant: " . $name . " with value " . $value );
        $this->gameClass->constants[$name] = [
            'value' => $value
        ];

    }

    public function addVariable( $name, $type, $size = null, $isLevelVar = false, $isGameVar = false, $section = null ){

        var_dump("Add Variable: " . $name);

        if (is_null($size)) $size = $this->calcSize($type);

        if ($section == "header"){

            $offset = $this->offsetGlobalVariable;

            if ($size % 4 != 0) $size += $size % 4;
            $this->offsetGlobalVariable += $size;
        }else{
            $this->offsetScriptVariable += $size;

            $offset = $this->offsetScriptVariable;

            if ($size % 4 != 0) $size += $size % 4;
            $this->offsetScriptVariable += $size;
        }

        $this->variables[strtolower($name) . '_' . $this->currentSection] = [
            'name' => strtolower($name),
            'type' => $type,
            'size' => $size,
            'offset' => $offset,
            'section' => $section,
            'scriptName' => $this->currentScriptName
        ];



        if ($type == "vec3d") {
            $this->variables[strtolower($name) . '_' . $this->currentSection]['size'] = 0;

            foreach (["x", "y", "z"] as $entry) {

                $attributeName = strtolower($name) . '.' . $entry;

                $this->variables[$attributeName . '_' . $this->currentSection] = [
                    'name' => $attributeName,
                    'type' => 'float',
                    'size' => 4,
                    'offset' => '123456789',
                    'section' => $section,
                    'scriptName' => $this->currentScriptName
                ];
            }
        }else if ($type == "rgbaint"){
            $this->variables[strtolower($name) . '_' . $this->currentSection]['size'] = 0;

            foreach (["red", "green", "blue", "alpha"] as $entry) {

                $attributeName = strtolower($name) . '.' . $entry;

                $this->variables[$attributeName . '_' . $this->currentSection] = [
                    'name' => $attributeName,
                    'type' => 'integer',
                    'size' => 4,
                    'offset' => '123456789',
                    'section' => $section,
                    'scriptName' => $this->currentScriptName
                ];
            }
        }


    }

    public function addCustomFunction( $name, $offset = null ){
        $this->gameClass->functions[strtolower($name)] = [
            'name' => strtolower($name),
            'offset' => $offset,
            'type' => Tokens::T_CUSTOM_FUNCTION
        ];
    }

    public function getVariablesByScriptName($scriptName){

        $found = [];
        foreach ($this->variables as $variable) {
            if ($variable['scriptName'] == $scriptName) $found[] = $variable;
        }

        return $found;
    }

    public function getScriptSize($scriptName){
        $size = 0;
        $variables = $this->getVariablesByScriptName($scriptName);
        foreach ($variables as $variable) {
            $size += $variable['size'];
        }

        return $size;
    }

    public function getVariable($name){

        $index = strtolower($name) . '_';

        if (isset($this->variables[$index . 'header'])){
            return $this->variables[$index. 'header'];
        }

        if (isset($this->variables[$index . 'script'])){
            return $this->variables[$index. 'script'];
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

        if ($addString4Bytes) {
            if ($size % 4 == 0) $size += 4;
        }

        return $size;
    }

    public function validateCode($compareCode){
        foreach ($this->codes as $index => $code) {
            if ($code['code'] != $compareCode[$index]) return false;
        }

        return true;
    }
}
