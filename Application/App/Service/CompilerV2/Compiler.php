<?php
namespace App\Service\CompilerV2;

use App\MHT;
use App\Service\Helper;
use Exception;

class Compiler
{

    public $untouchedSource;

    public $game;
    public $platform;
    public $debug = false;

    /** @var null|Compiler  */
    public $gameScript = null;

    /** @var null|Compiler  */
    public $levelScript = null;

    public $tokens = [];

    public $records = [

        'rgbaint' => [
            'red' => [
                'type' => 'short-integer',
                'offset' => 0,
            ],
            'blue' => [
                'type' => 'short-integer',
                'offset' => 2
            ],
            'green' => [
                'type' => 'short-integer',
                'offset' => 1
            ],
            'alpha' => [
                'type' => 'short-integer',
                'offset' => 3
            ],
        ],
    
        'vec3d' => [
            'x' => [
                'type' => 'float',
                'offset' => 0
            ],

            'y' => [
                'type' => 'float',
                'offset' => 4
            ],

            'z' => [
                'type' => 'float',
                 'offset' => 8
           ],
        ]

    ];

//    public $records = [];
    public $current = 0;

    /** @var ManhuntDefault */
    public $gameClass;

    public $variables = [];
    public $gameVarOccurrences = [];
    public $levelVarOccurrences = [];

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

    public $scriptBlockSizes = [];
    public $lastScriptEnd = 0;


    public $evalVar;

    public $storedProcedureCallOffsets = [];

    public $isGameSccSrc;

    public function debug($stuff, $exit = true){
        if ($this->debug){
            var_dump($stuff);
            if ($exit) exit;
        }
    }

    public function __construct($source, $game, $platform, $isGameSccSrc = false)
    {

        $this->isGameSccSrc = $isGameSccSrc;

        $this->evalVar = new EvaluateVariable($this);

        $this->game = $game;
        $this->platform = $platform;
        $this->gameClass = $this->game == MHT::GAME_MANHUNT ? new Manhunt() : new Manhunt2($this->platform);


        $source = preg_replace_callback("/FrisbeeSpeechPlayWait\((.*)\s?,(.*)\s?,(.*)\s?,(.*)\s?\)/U", function( $match ) use (&$index){
            $newMap = sprintf('FrisbeeSpeechPlay(%s,%s,%s);', $match[1],$match[2],$match[3]);
            $newMap .= sprintf('while NOT FrisbeeSpeechIsFinished(%s) do sleep(%s);', $match[1],$match[4]);
            return $newMap;
        },$source);


        $source = preg_replace_callback("/AIAddSubPackForLeaderCombatGoal\((.*)\s?,(.*)\s?,(.*)\s?,(.*)\s?\)/U", function( $match ) use (&$index){
            $newMap = sprintf('AIAddSubPackForLeader(%s,%s);', $match[1],$match[2]);
            $newMap .= sprintf('AISetSubpackCombatType(%s,%s,%s);', $match[1],$match[2],$match[3]);
            $newMap .= sprintf('AIAddGoalForSubpack(%s,%s,%s);', $match[1],$match[2],$match[4]);
            return $newMap;
        },$source);


        $source = preg_replace_callback("/AIAddEntityAndRun\((.*)\s?,(.*)\s?\)/U", function( $match ) use (&$index){
            $newMap = sprintf('AIAddEntity(%s);', $match[1]);
            $newMap .= sprintf('RunScript(%s, %s);', $match[1],$match[2]);
            return $newMap;
        },$source);

        $source = preg_replace_callback("/DisplayGameTextWait\((.*)\s?,(.*)\s?\)/U", function( $match ) use (&$index){
            $newMap = sprintf('DisplayGameText(%s);', $match[1]);
            $newMap .= sprintf('while IsGameTextDisplaying do Sleep(%s);', $match[2]);
            return $newMap;
        },$source);


        $source = preg_replace_callback("/DebugMove\((.*)\s?\)/U", function( $match ) use (&$index){
            $newMap = sprintf('moveentity(getplayer, getentityposition(getentity(%s)), 0);', $match[1]);
            return $newMap;
        },$source);

        $source = preg_replace_callback("/WaitForFrisbeeSpeechIsFinished\((.*)\s?,(.*)\s?\)/U", function( $match ) use (&$index){
            $newMap = sprintf('while NOT FrisbeeSpeechIsFinished(%s) do sleep(%s);', $match[1], $match[2]);
            return $newMap;
        },$source);


        $this->untouchedSource = $source;

        //a special comment
        $source = str_replace("{OPEN THE DOORS}", "", $source);
        $source = str_replace("}}", "}", $source);
        $source = str_replace('{TEMP SLEEP FOR PLACEHOLDER TEXT}', '', $source);

        // remove comments / unused code
        $source = preg_replace("/{(.|\s)*}/mU", "", $source);

        //extract all used strings

        $index = 0;
        $source = preg_replace_callback("/['](.*)[']/U", function( $match ) use (&$index){
//        $source = preg_replace_callback("/['|\"](.*)['|\"]/U", function( $match ) use (&$index){

            $this->strings[] = $match[1];
            $name = "'str_" . $index . "'";
            $index++;
            return $name;
        },$source);



        /**
         * Avoid wrong associations
         */
        //leftover from the exporter (todo)
        $source = str_replace("\00", "", $source);

        //todo...
        $source = str_replace("9-i", "9 - i", $source);

        //split the parts a little bit more
        $source = preg_replace("/\//", " / ", $source);
        $source = preg_replace("/\(/", " ( ", $source);
        $source = preg_replace("/\)/", " ) ", $source);
        $source = preg_replace("/\*/", " * ", $source);

        $source = preg_replace("/</", " < ", $source);
        $source = preg_replace("/>/", " > ", $source);

        $source = preg_replace("/\+/", " + ", $source);
        $source = preg_replace("/,/", " , ", $source);

        //split attribute access
        // "vel.x := vel.x  *  speed;" to "vel . x := vel . x  *  speed;"
        $source = preg_replace("/([a-zA-Z])\.([a-zA-Z])/", "$1 . $2", $source);

        $source = preg_replace("/([a-zA-Z])\s?-([0-9])/", "$1 - $2", $source);

        // "vel.x := vel1.x  *  speed;" to "vel . x := vel1 . x  *  speed;"
        $source = preg_replace("/([0-9])\.([a-zA-Z])/", "$1 . $2", $source);

        $source = preg_replace("/(])\.([a-zA-Z])/", "$1 . $2", $source);

        $source = preg_replace("/\[/", " [ ", $source);
        $source = preg_replace("/]/", " ] ", $source);

        $source = preg_replace("/:/", " : ", $source);
        $source = preg_replace("/=/", " = ", $source);


        $source = preg_replace("/:\s*=/", " := ", $source);
        $source = preg_replace("/>\s*=/", " >= ", $source);
        $source = preg_replace("/<\s*=/", " <= ", $source);
        $source = preg_replace("/<\s*>/", " <> ", $source);


        /**
         * TODO, add parsing for this values
         *
         * appear in a18 script 38
         */
        $source = str_replace('5.09909e - 005', '0.0000509909', $source);
        $source = str_replace('-450', '- 450', $source);


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


        if ($this->isGameSccSrc == false){
            $gameScriptCompiler = new Compiler($this->gameClass->gameSccSrc, $this->game, $this->platform, true);
            $gameScriptCompiler->compile();
            $this->gameScript = $gameScriptCompiler;
        }



        while ($this->current < count($this->tokens)) {

            if ($this->consumeIfTrue('const')){
                (new Associations())->consumeConstants($this);
            }else{
                $this->current++;
            }
        }

        $this->current = 0;

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
//var_dump($associationRearranged);
//exit;
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

            'CODE' => $this->getCODE(),
            'DATA' => $this->generateDATA(),
            'STAB' => $this->generateSTAB(),
            'SCPT' => $this->generateSCPT(),
            'ENTT' => $this->generateEntity(),
            'LINE' => $this->generateLine(),
            'TRCE' => ['00000000'],
            'SRCE' => $this->untouchedSource,

            //TODO calc missed
            'SMEM' => $this->getSMEM(),
            'DMEM' => $this->getDMEM()

        ];
    }

    public function getSMEM(){
        //should be unused , just internal
        if (strpos($this->untouchedSource, 'entity manhunt : et_game') !== false)
            return 78596;

        if ($this->platform === MHT::PLATFORM_PSP){
            $firstLine = explode("\n", $this->untouchedSource)[0];
            if (strpos($firstLine, 'SMEM:') === false)
                die("MHT header missed!, every srce need this => {#MHT SMEM:69076 | DMEM:190756}");

            $mem = explode("SMEM:", $firstLine)[1];
            $mem = (int) explode(" ", $mem)[0];

            return $mem;
        }else{
            return 78596;
        }

    }

    public function getDMEM(){
        //should be unused , just internal
        if (strpos($this->untouchedSource, 'entity manhunt : et_game') !== false)
            return 78596;

        if ($this->platform === MHT::PLATFORM_PSP){
            $firstLine = explode("\n", $this->untouchedSource)[0];
            if (strpos($firstLine, 'DMEM:') === false)
                die("MHT header missed!, every srce need this => {#MHT SMEM:69076 | DMEM:190756}");

            $mem = explode("DMEM:", $firstLine)[1];
            $mem = (int) explode(" ", $mem)[0];

            return $mem;
        }else{
            return 78596;
        }

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
        if ($this->game == MHT::GAME_MANHUNT){
            if (
                $currentScriptName != "" && // we are inside s const section...
                isset($this->strings4Script[$currentScriptName]) &&
                isset($this->strings4Script[$currentScriptName][$string])
            ){
                return;
            }
        }else{
            if (
                $currentScriptName != "" && // we are inside s const section...
                isset($this->strings4Script[$currentScriptName]) &&
                isset($this->strings4Script[$currentScriptName][strtolower($string)])
            ){
                return;
            }
        }



        if (!isset($this->strings4Script[$currentScriptName]))
            $this->strings4Script[$currentScriptName] = [];

        $len = strlen($string);


        $newString = new Associations();
        $newString->value = $string;
        $newString->section = "header";
        $newString->offset = $this->offsetGlobalVariable;
        $newString->scriptName = $currentScriptName;

        if ($this->game == MHT::GAME_MANHUNT){

//            echo " Offset: " . $newString->offset . " Len Ori: " . $len;

            if (4 - $len % 4 != 0) $len +=  4 - $len % 4;
//            echo " Len Calc: " . $len . " " . $string . "\n";

            $newString->size = $len;
        }else{
            $len += 1;
            $newString->size = $len;
        }


        if ($this->game == MHT::GAME_MANHUNT){
            $this->strings4Script[$currentScriptName][$string] = $newString;
        }else{
            $this->strings4Script[$currentScriptName][strtolower($string)] = $newString;
        }


        if ($this->game == MHT::GAME_MANHUNT_2){
            if (4 - $len % 4 != 0) $len += 4 - $len % 4;
        }

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
                'isLevelVar' => false,
                'isGameVar' => false,
                'section' => 'header',
                'scriptName' => 'header'
            ];
        }

        $this->gameClass->types[$name] = [
            'types' => $types
        ];

    }
    public function addRecord($name, $recordEntries ){

        $this->records[$name] = $recordEntries;


    }
    public function addConstants( $name, $value, $type){


        $data = [
            'size' => 4,
            'name' => $name,
            'value' => $value,
            'type' => $type,
            'isLevelVar' => false,
            'isGameVar' => false,
            'varType' => $type,
            'section' => 'constant',
            'scriptName' => 'header'
        ];


        if (is_int($value)) {
            $data['offset'] = $value;
            $this->offsetGlobalVariable += 4;
        }else if (is_float($value)) {
            $data['offset'] = $value;
            $this->offsetGlobalVariable += 4;
        }else{
            $stringIndex = substr($value, 4);
            $value = $this->strings[$stringIndex];

            $data['size'] = strlen($value) + 1;
            $data['offset'] = $this->offsetConstants;

        }

        /**
         * this is a little bit tricky here
         *
         * the constant values are part of the globaleVariableOffset
         * but the string extraction process will grab the constant strings also.
         *
         * To avoid duplicate offset calculation we use a temporary offset calculation...
         */
        $this->offsetConstants += $data['size'] + (4 - $data['size'] % 4);

        $this->addVariable($data);
    }

    public function addVariable( $data ){

        if ($data['type'] == 'real') $data['type'] = "float";

        if (!isset($data['size'])) $data['size'] = $this->calcSize($data['type']);

        if (!isset($data['offset'])){

            if (
                $data['section'] == "header" ||
                $data['section'] == "constant"
            ){

                $size = $data['size'];
                if ($data['type'] == "string"){
                    if ($size % 4 != 0){
                        $size += $data['size'] % 4;
                    }else{
                        if ($this->game == MHT::GAME_MANHUNT_2) {
                            $size += 4;
                        }
                    }
                }

                $offset = $this->offsetGlobalVariable;
//                var_dump("addVariable -> " . $data['name'] . " => " . $offset . " add " . $size);
                $this->offsetGlobalVariable += $size;

            }else if ($data['section'] == "script"){

                $this->offsetScriptVariable += $data['size'];

                $offset = $this->offsetScriptVariable;

                 if ($data['type'] == "string"){

                    if ($data['size'] % 4 != 0){
                        $this->offsetScriptVariable += $data['size'] % 4;
                    }else{
                        if ($this->game == MHT::GAME_MANHUNT_2) {
                            $this->offsetScriptVariable += 4;
                        }
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
//            var_dump("addVariable -> " . $data['name'] . " => " . $offset );

        }

        /**
         * Overwrite the calculated offset with the game_var / level_var offset
         */
        if (isset($data['isGameVar']) && $data['isGameVar'] == true && $this->gameScript !== null){
            $gameVar = $this->gameScript->getVariable($data['name']);
            if ($gameVar) $offset = $gameVar['offset'];
        }

        if (isset($data['isLevelVar']) && $data['isLevelVar'] == true && $this->levelScript !== null){
            $levelVar = $this->levelScript->getVariable($data['name']);
            if ($levelVar){
                $offset = $levelVar['offset'];
                $data['levelVarSize'] = $levelVar['size'];
            }
        }


        $master = array_merge([], $data);
        $master['offset'] = $offset;
        $master['scriptName'] = $this->currentScriptName;

        $this->variables[] = $master;

        return $master;
    }

    public function addCustomFunction( $name, $type = Tokens::T_CUSTOM_FUNCTION, $return = null ){

        //The forward command already add the function, reuse the return value
        if (
            isset($this->gameClass->functions[strtolower($name)]) &&
            isset($this->gameClass->functions[strtolower($name)]['return'])
        ){
            $return = $this->gameClass->functions[strtolower($name)]['return'];
        }

        $this->gameClass->functions[strtolower($name)] = [
            'name' => strtolower($name),
            'offset' => Helper::fromIntToHex($this->offsetProcedureScripts),
            'return' => $return,
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
                    if ($this->game == MHT::GAME_MANHUNT_2){
                        $size += 4;

                    }
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
        $current -= 20;

        for($i = -10; $i < 20; $i++){
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

        if (isset($this->records[$type])){
            $records = $this->records[$type];

            $size = 0;
            foreach ($records as $item) {
                $size += $this->calcSize($item['type']);
            }

        }else {
            $size = 4;
            switch ($type) {
                case 'short-integer':
                    $size = 1;
                    break;
                case 'vec3d':
                    $size = 12;
                    break;
            }
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


                /**
                 * The r* did a mistake, he think a string is special large, thats not correct
                 * we ignore the mismatch, our result is correct ;)
                 *
                 * Appears in A18 script 26
                 */
                if (
                    $compareCode[$index] == "e4010000" &&
                    $code['code'] == "0f000000"
                ) continue;

                /**
                 * same shit in A18 script 30
                 */
                if (
                    $compareCode[$index] == "2c070000" &&
                    $code['code'] == "77000000"
                ) continue;


                return false;
            }
        }

        return true;
    }

    /**
     * @param Associations $association
     * @return mixed|string|null
     * @throws Exception
     */
    public function detectVarType( Associations $association ){

        $varType = null;
        switch ($association->type){
            case Tokens::T_ASSIGN:
            case Tokens::T_CONSTANT:
            case Tokens::T_VARIABLE:
                $varType = $association->varType;

                if ($varType == "object" && $association->attribute !== null){
                    return $association->attribute->varType;
                }
                if ($varType == "array"){
                    return $association->typeOf;
                }
                break;
            case Tokens::T_FUNCTION:
                if ($association->return == null){
                    throw new Exception("Unable to detect varType, RETURN missed for function " . $association->value);
                }
                $varType = $association->return;
                break;
            case Tokens::T_STATE:
            case Tokens::T_BOOLEAN:
            case Tokens::T_SELF:
            case Tokens::T_INT:
                $varType = "integer";
                break;
            case Tokens::T_FLOAT:
                $varType = "float";
                break;
            case Tokens::T_STRING:
                $varType = "string";
                break;
            default:
                throw new Exception("Unable to detect compareType for type " . $association->type);
                break;
        }

        return $varType;
    }

    public function isTypeMathOperator($type){

        switch ($type){
            case Tokens::T_ADDITION:
            case Tokens::T_SUBSTRACTION:
            case Tokens::T_MULTIPLY:
            case Tokens::T_DIVISION:
            case Tokens::T_MOD:
                return true;
        }

        return false;
    }

    public function isTypeConditionOperatorOrOperation($type){
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

    public function isTypeConditionOperation($type){
        switch ($type){
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

    /**
     * @return Associations
     * @throws Exception
     */
    public function getPossibleMathChilds(){
        /** @var Associations[] $mathChilds */
        $mathChilds = [
            new Associations($this)
        ];
        while (
            $this->getToken() == "+" ||
            $this->getToken() == "-" ||
            $this->getToken() == "*" ||
            $this->getToken() == "/" ||
            $this->getToken() == "(" ||
            $this->getToken() == "mod" ||
            $this->getToken() == "div"
        ) {
            $mathChilds[] = new Associations($this);
            $mathChilds[] = new Associations($this);
        }


        $result = [];
        (new Associations())->flatForRpn($mathChilds, $result);

        if (count($result) > 1){

            $math = new Associations();
            $math->type = Tokens::T_MATH;

            $math->childs = (new RPN())->convertToReversePolishNotation($result);

            if (count($math->childs) == 1) return $math->childs[0];
            return $math;
        }else{
            return $mathChilds[0];
        }

    }

    public function createVariableAssociation( array $data, Associations $variable = null ){

        if ($variable == null) $variable = new Associations();

        //copy all given data into the Association object
        foreach ($data as $index => $value) {

            if ($index == "name") $index = "value";

            if (property_exists(Associations::class, $index)){
                $variable->$index = $value;
            }
        }

        if ($variable->varType == null && $variable->type !== null){
            $variable->varType = $variable->type;
        }

        $variable->type = Tokens::T_VARIABLE;

        return $variable;
    }

    public $logPad = 0;
    public function log($msg){
//        echo "|>" . str_repeat('-', $this->logPad) . " " .$msg . "\n";
    }


    /**
     * @return array
     * @throws Exception
     */
    private function generateDATA()
    {
        $result = [
            'const' => [],
            'strings' => []
        ];

        foreach ($this->variables as $variable) {
            if ($variable['section'] == "constant"){

                switch ($variable['type']){

                    case 'float':
                    case 'integer':

                        $result['const'][] = $variable['value'];
                        break;
                    case 'string':
                        break;

                    default:
                        var_dump($variable);
                        throw new Exception("Unknown constant type " . $variable['type']);
                        break;

                }
            }
        }

        foreach ($this->strings4Script as $strings) {
            foreach ($strings as $value => $string) {
                $result['strings'][] = $string->value;
            }
        }

        return $result;
    }


    private function generateSTAB(){

        $results = [];

        foreach ($this->variables as $variable) {
            if (
                $variable['section'] != "header" &&

                //Note: some script declare a level_var within a regular script block...
                $variable['isLevelVar'] === false
            ) continue;

            if (isset($variable['fromState']) && $variable['fromState'] === true ) continue;

            $offset = Helper::fromIntToHex($variable['offset']);
            $size = $variable['size'];
            $objectType = $variable['type'];

            $isState = $this->getState($objectType);

            if ($isState !== false){
                $objectType = "state";
            }


            /**
             * anything is a boolean ? mkay
             */
            if ($this->game == MHT::GAME_MANHUNT){


                if (isset($this->records[$variable['type']])){
                    $objectType = "vec3d";
                }else{
                    $objectType = "boolean";
                }
            }

            $occurrences = [];

            $definitionCount = 0;
            foreach ($this->variables as $_variable) {
                if ($_variable['name'] == $variable['name']){

                    if ($definitionCount >= 1 ){
                        $offset = Helper::fromIntToHex($_variable['offset'] - $_variable['size']);

                    }

                    $definitionCount++;
                }
            }


            if ($definitionCount > 1){
                $hierarchieType = Helper::fromIntToHex(2);
            }else{
                $hierarchieType = Helper::fromIntToHex(1);

            }




            if ($variable['isLevelVar'] === true) {

                if (isset($this->levelVarOccurrences[$variable['name']])){
                    $occurrences = $this->levelVarOccurrences[$variable['name']];
                }

                $hierarchieType = "ffffffff";
                $offset = "ffffffff";
                $size = "ffffffff";

                if ($this->game == MHT::GAME_MANHUNT){
                    $objectType = "ffffffff";
                }

            }

            else if ($variable['isGameVar'] === true){

                if (isset($this->gameVarOccurrences[$variable['name']])) {
                    $occurrences = $this->gameVarOccurrences[$variable['name']];
                }

                $hierarchieType = "feffffff";
                $offset = "ffffffff";
                $size = "ffffffff";

                if ($this->game == MHT::GAME_MANHUNT){
                    $objectType = "feffffff";
                }

            }

            /**
             * Looks like the objectType is not in use really...
             */
            if ($objectType == "ecollectabletype") $objectType = "integer";
            if ($objectType == "entityptr") $objectType = "integer";
            if ($objectType == "array") $objectType = "integer";
            if ($objectType == "float") $objectType = "real";


            $result = [
                'name' => strtolower($variable['name']),
                'offset' => $offset,
                'size' => $size,
                'objectType' => $objectType,
                'occurrences' => $occurrences
            ];

            if ($this->game == MHT::GAME_MANHUNT_2){
                $result['hierarchieType'] = $hierarchieType;
            }

            $results[] = $result;
        }

        usort($results, function ($a, $b) {
            return $a['name'] > $b['name'];
        });

        return $results;
    }


    private function generateEntity(){
        return [
            'name' => $this->mlsEntityName,
            'type' => $this->mlsEntityType == "et_level" ? "levelscript" : "other"
        ];

    }


    private function generateSCPT(){
        $results = [];

        $scriptSize = 0;
        foreach ($this->scriptBlockSizes as $name => $size) {
            $scriptSize += $size;

            $onTrigger = $this->gameClass->functionEventDefinition['__default__'];

            if (isset($this->gameClass->functionEventDefinition[$name])){
                $onTrigger = $this->gameClass->functionEventDefinition[$name];
            }

            $results[] = [
                'name' => $name,
                'onTrigger' => $onTrigger,
                'scriptStart' => $scriptSize
            ];
        }

        return $results;
    }

    public function generateLine(){

        if ($this->game == MHT::GAME_MANHUNT_2) return [];

        $result = [];

        foreach ($this->codes as $item) {
            $result[] = '00000000';
        }

        return $result;
    }

}


