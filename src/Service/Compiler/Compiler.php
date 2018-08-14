<?php
namespace App\Service\Compiler;

use App\Bytecode\Helper;
use App\Service\Compiler\Emitter\T_VARIABLE;
use App\Service\Compiler\FunctionMap\Manhunt;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\FunctionMap\ManhuntDefault;

class Compiler {

    /**
     * @param $source
     * @return mixed
     */
    private function prepare($source){

        $source = str_replace([
            "if (GetEntity('Syringe_(CT)')) <> NIL then",
        ],[
            "if GetEntity('Syringe_(CT)') <> NIL then",
        ], $source);

        // remove double whitespaces
        $source = preg_replace("/\s+/", ' ', $source);

        // remove comments / unused code
        $source = preg_replace("/\{(.*?)\}/", "", $source);

        // replace line ends with new lines
        $source = preg_replace("/;/", ";\n", $source);

        return trim($source);
    }

    private function getHeaderVariables( $tokens, $types ){

        $current = 0;
        $currentSection = 'header';

        $vars = [];

        $inside = false;

        while ($current < count($tokens)) {

            $token = $tokens[ $current ];

            // we need to know the current section for the defined vars
            if (
                $inside &&
                (
                    $token['type'] == Token::T_DEFINE_SECTION_TYPE ||
                    $token['type'] == Token::T_DEFINE_SECTION_ENTITY ||
                    $token['type'] == Token::T_DEFINE_SECTION_CONST ||
                    $token['type'] == Token::T_PROCEDURE ||
                    $token['type'] == Token::T_SCRIPT
                )

            ){

                return $vars;
            }

            if ($token['type'] == Token::T_SCRIPT || $token['type'] == Token::T_PROCEDURE) {
                return $vars;
            }

            if ($token['type'] == Token::T_DEFINE_SECTION_VAR) {
                $inside = true;
            }

            if ($inside == false){
                $current++;
                continue;
            }

            if ($token['type'] == Token::T_VARIABLE && $tokens[$current + 1]['type'] == Token::T_DEFINE){

                $variables = [
                    $token
                ];

                $prevToken = $tokens[ $current - 1];
                $innerCurrent = $current;
                while($prevToken['type'] == Token::T_VARIABLE){
                    $variables[] = $prevToken;
                    $innerCurrent--;
                    $prevToken = $tokens[ $innerCurrent - 1];
                }

                $variables = array_reverse($variables);

                foreach ($variables as $variable) {
                    $variable = $variable['value'];

                    if (!$this->isVariableInUse($tokens, $variable)){
                        continue;
                    }


                    $variableType = strtolower($tokens[$current + 2]['value']);

                    if (isset($types[ $variableType ] )){
                        $row = [
                            'section' => $currentSection,
                            'type' => $variableType,
                            'abstract' => 'state'
                        ];
                    }else{
                        $row = [
                            'section' => $currentSection,
                            'type' => $variableType
                        ];
                    }

                    if (substr($variableType, 0, 7) == "string["){
                        $size = (int) explode("]", substr($variableType, 7))[0];
                        $row['type'] = 'stringarray';
                        $row['size'] = $size;

                    }else{

//                        try{
//                            $mapping = T_VARIABLE::getMapping($tokens[$current], null, []);
//
//                            $row['force_offset'] = $mapping['offset'];
//
//                        }catch(\Exception $e){
//                            $mapping = false;
//                        }


                        switch ($variableType){
                            case 'vec3d':
                                $size = 12; // 3 floats a 4-bytes
                                break;

                            default:
                                $size = 4;
                                break;

                        }

                        $row['size'] = $size;
                    }

                    $vars[$variable] = $row;
                }
            }

            $current++;
        }

        return $vars;
    }

    private function isVariableInUse($tokens, $var){

        $result = $this->recursiveSearch($tokens, [
            Token::T_VARIABLE,
            Token::T_ASSIGN
        ]);

        foreach ($result as $token) {

            if ($token['value'] == $var){
                return true;
            }
        }

        return false;
    }

    private function getConstants($tokens, &$smemOffset){

        $current = 0;

        $vars = [];
        $currentSection = false;

        while ($current < count($tokens)) {

            $token = $tokens[ $current ];

            // we need to know the current section for the defined vars
            if ($token['type'] == Token::T_DEFINE_SECTION_CONST){
                $currentSection = 'const';
                $current++;
                continue;
            }

            if ($currentSection == "const"){

                if ($token['type'] == Token::T_SCRIPT){
                    break;
                }else{
                    $variable = $token['value'];
                    $variableValue = $tokens[$current + 2]['value'];
                    $variableValue = str_replace('"', '', $variableValue);

                    $vars[$variable] = [
                        'offset' => Helper::fromIntToHex($smemOffset),
                        'length' => strlen($variableValue)
                    ];

                    $length = strlen($variableValue);

                    if ($length % 4 == 0){
                        $smemOffset += $length;
                    }else{
                        $smemOffset += $length + (4 - $length % 4);
                    };

                    $current = $current + 3;
                }
            }

            $current++;
        }

        return $vars;
    }


    private function getStrings($tokens, &$smemOffset){

        $response =  $this->recursiveSearch($tokens, [Token::T_STRING]);

        $result = [];

        foreach ($response as $item) {

            $value = str_replace('"', '', $item['value']);

            $result[$value] = $value;
        }

        $strings = array_unique($result);
        foreach ($strings as &$string) {

            $length = strlen($string) + 1;
            $string = [
                'offset' => Helper::fromIntToHex($smemOffset),
                'length' => strlen($string)
            ];

            if (4 - $length % 4 != 0){
                $length += 4 - $length % 4;
            }

            $smemOffset += $length;

        }

        return $strings;
    }

    private function getTypes($tokens){

        $types = [];

        $current = 0;
        $offset = 0;
        $inside = false;
        $currentTypeSection = false;

        while( $current < count($tokens)){

            $token = $tokens[ $current ];

            if ($token['type'] == Token::T_DEFINE_SECTION_TYPE) {
                $inside = true;

            }else if (
                $inside && (
                    $token['type'] == Token::T_DEFINE_SECTION_VAR ||
                    $token['type'] == Token::T_DEFINE_SECTION_ENTITY ||
                    $token['type'] == Token::T_DEFINE_SECTION_CONST ||
                    $token['type'] == Token::T_SCRIPT
                )
            ){
                return $types;

            }else if (
                $token['type'] == Token::T_BRACKET_OPEN ||
                $token['type'] == Token::T_BRACKET_CLOSE
            ){
                // do nothing
            }else if (
                $token['type'] == Token::T_LINEEND
            ){

                $currentTypeSection = false;
            }else if ($inside){

                if ($token['type'] == Token::T_IS_EQUAL){
                    $beforeToken = $tokens[ $current - 1 ];

                    $offset = 0;
                    $currentTypeSection = strtolower($beforeToken['value']);

                    $types[ $currentTypeSection ]  = [];


                }else if ($currentTypeSection && $token['type'] == Token::T_VARIABLE){

                    $types[ $currentTypeSection ][ strtolower($token['value']) ] = [
                        'type' => 'level_var tLevelState',
                        'section' => "header",
                        'offset' => Helper::fromIntToHex($offset)
                    ];

                    $offset++;
                }
            }

            $current++;
        }

        return $types;
    }

    private function getEntitity($tokens){

        $found = false;
        $current = 0;

        $scriptName = strtolower($tokens[1]['value']);


        while($current < count($tokens)){

            $token = $tokens[$current];

            if ($token['type'] == Token::T_DEFINE_SECTION_ENTITY){
                $found = true;
                $current++;
                continue;
            }

            if (!$found){
                $current++;
                continue;
            }

            return [
                'name' => strtolower($token['value']),
                'type' => $scriptName == "levelscript" ? "levelscript" : "other"
            ];
        }

        throw new \Exception('Compiler could not find / parse the Entity section');
    }

    public function parse($source, $levelScript = false, $game = "Manhunt2"){
//
//        if ($levelScript != false){
//            var_dump($levelScript['extra']['headerVariables']);
//            exit;
//        }

        if (!defined('GAME')){
            define('GAME', $game);
        }

        $smemOffset = 0;
        $scriptName = false;

        // cleanup the source code
        $source = $this->prepare($source);

        // convert script code into tokens
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->run($source);

        $types = $this->getTypes($tokens);
        // extract every header and script variable definition
        $headerVariables = $this->getHeaderVariables($tokens, $types);

        if ($levelScript != false){
            foreach ($levelScript['extra']['headerVariables'] as $levelHeaderVariableName => $levelHeaderVariable) {

                foreach ($headerVariables as $headerVariableName => &$headerVariable) {
                    if (strpos(strtolower($headerVariable['type']), 'level_var') !== false){

                        if ($levelHeaderVariableName == $headerVariableName){
                            $headerVariable['offset'] = $levelHeaderVariable['offset'];

                        }
                    }
                }

            }
        }

        $const = $this->getConstants($tokens, $smemOffset);

        $tokens = $tokenizer->fixProcedureEndCall($tokens);
        $tokens = $tokenizer->fixTypeMapping($tokens, $types);
        $tokens = $tokenizer->fixHeaderBracketMismatches($tokens);

        // parse the token list to a ast
        $parser = new Parser( );
        $ast = $parser->toAST($tokens);

        $this->fixWriteDebug($ast['body']);

        $header = [];
        $currentSection = "header";

        $sectionCode = [];
        $start = 1;

        $lineCount = 1;
        $smemOffset = 0;

        $strings4Scripts = [];

        foreach ($ast["body"] as $index => $token) {

            if (
                $token['type'] == Token::T_SCRIPT ||
                $token['type'] == Token::T_PROCEDURE ||
                $token['type'] == Token::T_FUNCTION
            ){
                $strings4Scripts[$token['value']] = $this->getStrings($token['body'], $smemOffset);
            }
        }

        $ast = $parser->handleForward($ast);


        foreach ($headerVariables as $name => &$item) {

            if (!isset($item['offset'])){
                $item['offset'] = Helper::fromIntToHex($smemOffset);

            }

            $size = $item['size'];

            if ($size % 4 !== 0){
                $size += $size % 4;
            }

            $smemOffset += $size;
        }

        $smemOffset2Tmp = 0;

        $scriptBlockSizes = [];
        $lastScriptEnd = 0;
        $scriptBlockSizesAdditional = 0;
        foreach ($ast["body"] as $index => $token) {

            if (
                $token['type'] == Token::T_SCRIPT ||
                $token['type'] == Token::T_PROCEDURE ||
                $token['type'] == Token::T_FUNCTION
            ){
                $currentSection = "script";
                $scriptName = $token['value'];

                /**
                 * Calculate string offsets
                 */
                $scriptVar = $this->getScriptVar($token['body']);

                $smemOffset2 = 0;
                $scriptVarFinal = [];
                foreach ($scriptVar as $name => &$item) {
                    $smemOffset2 += $item['size'];

                    if ($item['size'] % 4 !== 0){
                        $smemOffset2 += $item['size'] % 4;
                    }
                    $item['offset'] = Helper::fromIntToHex($smemOffset2);
                    $scriptVarFinal[$name ] = $item;
                }

                $smemOffset2Tmp += $smemOffset2;

                foreach ($headerVariables as $name => $item) {

                    if ($this->isVariableInUse($token['body'], $name)){

                        $scriptVarFinal[$name ] = $item;
                    }
                }

                /**
                 * Translate Token AST to Bytecode
                 */


                $emitter = new Emitter(  $scriptVarFinal, $strings4Scripts[$scriptName], $types, $const, $lineCount );

                $code = $emitter->emitter([
                    'type' => "root",
                    'body' => [
                        $token
                    ]
                ]);

                if ($token['type'] == Token::T_SCRIPT){
                    $scriptBlockSizes[$scriptName] = $lastScriptEnd;
                }

                $lastScriptEnd = count($code) * 4;
//
//                //TODO: logic recode, what a mess....
//                if ($token['type'] == Token::T_SCRIPT){
//                    if ($scriptBlockSizesAdditional > 0){
//                        echo "ja drin";
//                        $scriptBlockSizes[$scriptName] = $scriptBlockSizesAdditional;
//                        $scriptBlockSizesAdditional = count($code) * 4;
//                    }else{
//                        echo "add2";
//                        $scriptBlockSizesAdditional = 0;
//                    }
//                }else{
//                    $scriptBlockSizesAdditional = count($code) * 4;
//                    echo "add1";
//
//                }

                foreach ($code as $line) {
                    if ($line->lineNumber !== $start){
                        var_dump( $line, $start);
                        throw new \Exception('Calulated line number did not match with the generated one');
                    }

                    $start++;
                    $sectionCode[] = $line->hex;
                }

                if (isset($line)) $lineCount = $line->lineNumber + 1;

            }else if ($currentSection == "header"){
                $header[] = $token;
            }else{
                throw new \Exception(sprintf('Compiler: parse unknown type for emitter %s', $token['type']));
            }
        }

        return [
            'extra' => [
                'headerVariables' => $headerVariables
            ],
            'CODE' => $sectionCode,
            'DATA' => $this->generateDATA($strings4Scripts),
            'STAB' => $this->generateSTAB($headerVariables),
            'SCPT' => $this->generateSCPT($scriptBlockSizes),
            'ENTT' => $this->getEntitity($tokens),
//            'NAME' => $scriptName,

            //todo: value did not match...
            'SMEM' => 78596
//            'SMEM' => ($smemOffset + $smemOffset2Tmp) * 4

        ];
    }

    public function recursiveSearch($tokens, $searchType, $ignoreTypes = []){


        $result = [];
        foreach ($tokens as $token) {

            if (count($searchType) == 0 || in_array($token['type'],$searchType)){
                if (in_array($token['type'],$ignoreTypes)){
                    continue;
                }else{
                    $result[] = $token;
                }
            }

            if (isset($token['params'])) {
                $response =  $this->recursiveSearch($token['params'], $searchType, $ignoreTypes);
                foreach ($response as $item) {
                    $result[] = $item;
                }
            }else if (isset($token['body'])){
                $response =   $this->recursiveSearch($token['body'], $searchType, $ignoreTypes);
                foreach ($response as $item) {
                    $result[] = $item;
                }
            }else if (isset($token['cases'])){

                if (isset($token['switch'])){
                    $response =   $this->recursiveSearch([$token['switch']], $searchType, $ignoreTypes);
                    foreach ($response as $item) {
                        $result[] = $item;
                    }
                }

                foreach ($token['cases'] as $case) {

                    if (!isset($case['condition'])){
                        $response = $this->recursiveSearch($case['body'], $searchType, $ignoreTypes);
                        foreach ($response as $item) {
                            $result[] = $item;
                        }
                    }

                    if (isset($case['condition'])){
                        $response = $this->recursiveSearch($case['condition'], $searchType, $ignoreTypes);
                        foreach ($response as $item) {
                            $result[] = $item;
                        }

                        $response = $this->recursiveSearch($case['isTrue'], $searchType, $ignoreTypes);
                        foreach ($response as $item) {
                            $result[] = $item;
                        }
                    }
                }
            }
        }

        return $result;
    }

    private function getScriptVar( $tokens ){

        $otherTokens = [];
        $varSection = [];

        foreach ($tokens as $token) {
            if ($token['type'] == Token::T_DEFINE_SECTION_VAR) {
                $varSection = $token['body'];
            }else{
                $otherTokens[] = $token;
            }
        }

        $tokens = $varSection;
        $current = 0;
        $vars = [];

        while ($current < count($tokens)) {

            $token = $tokens[ $current ];

            if ($token['type'] == Token::T_VARIABLE && $tokens[$current + 1]['type'] == Token::T_DEFINE_TYPE){

                $variables = [
                    $token
                ];

                $oriPos = $current;
                if (isset($tokens[ $current - 1])){

                    $prevToken = $tokens[ $current - 1];
                    while($prevToken['type'] == Token::T_VARIABLE){
                        $variables[] = $prevToken;
                        $current--;
                        if (!isset($tokens[ $current - 1])) break;
                        $prevToken = $tokens[ $current - 1];
                    }
                }

                $variables = array_reverse($variables);

                $current = $oriPos;
                $current = $current + 1;

                $variableType = strtolower($tokens[$current]['value']);

                foreach ($variables as $variable) {
                    $variable = $variable['value'];

                    $row = [
                        'section' => 'script',
                        'type' => $variableType
                    ];

                    if (substr($variableType, 0, 7) == "string[") {
                        $size = (int)explode("]", substr($variableType, 7))[0];
                        $row['size'] = $size;

                    }else{
                        switch ($variableType){
                            case 'vec3d':
                                $size = 12; // 3 floats a 4-bytes
                                break;
                            default:
                                $size = 4;
                                break;

                        }

                        $row['size'] = $size;
                    }

                    $vars[$variable] = $row;
                }
            }

            $current++;
        }

        return $vars;
    }

    /**
     *
     * well.. the writedebug calls need to be separated
     * any call can only process one parameter...
     *
     * @param $ast
     * @return array
     */
    private function fixWriteDebug(&$ast ){
        $add = [];

        foreach ($ast as $index =>  &$item) {
            if (isset($item['body'])){
                $this->fixWriteDebug( $item['body'] );
            }

            if (
                $item['type'] == Token::T_FUNCTION &&
                strtolower($item['value']) == "writedebug"
            ){

                $count = count($item['params']);
                foreach ($item['params'] as $innerIndex => $param) {

                    $new = [
                        'type' => Token::T_FUNCTION,
                        'value' => 'writedebug',
                        'nested' => false,
                        'last' => $count  == $innerIndex + 1,
                        'index' => $innerIndex,
                        'params' => [ $param ]
                    ];

                    if ($innerIndex == 0){
                        $item = $new;
                    }else{
                        array_splice( $ast, $index + $innerIndex, 0, [$new] );

                    }
                }
            }
        }

        return $add;
    }

    /**
     * @param $tokens
     * @return array
     */
    public function generateSCPT( $scriptBlockSizes ){

        $scpt = [];
        $scriptSize = 0;
        foreach ($scriptBlockSizes as $name => $item) {
            $scriptSize += $item;

            $functionEventDefinitionDefault = ManhuntDefault::$functionEventDefinition;
            $functionEventDefinition = Manhunt2::$functionEventDefinition;
            if (GAME == "mh1") $functionEventDefinition = Manhunt::$functionEventDefinition;

            if (isset($functionEventDefinitionDefault[strtolower($name)])) {
                $onTrigger = $functionEventDefinitionDefault[strtolower($name)];
            }else if (isset($functionEventDefinition[strtolower($name)])){
                $onTrigger = $functionEventDefinition[strtolower($name)];
            }else{
                $onTrigger = $functionEventDefinition['__default__'];
            }

            $scpt[] = [
                'name' => strtolower($name),
                'onTrigger' => $onTrigger,
                'scriptStart' => $scriptSize
            ];

        }

        return $scpt;
    }


    public function generateDATA( $strings4Scripts ){

        $result = [];

        foreach ($strings4Scripts as $strings) {
            foreach ($strings as $value => $string) {

                $result[] = $value;
            }
        }

        return $result;
    }


    public function generateSTAB( $headerVariables ){

        $result = [];

        foreach ($headerVariables as $name => $variable) {

            $varType = $variable['type'];

            /*
             *
             * TODO: HACKS... damit ich erstma lvl1 compilen kann...
             */
            if ($varType == "stringarray") $varType = "string";
            if ($varType == "televatorlevel") $varType = "tLevelState";
            if ($varType == "tlevelstate") $varType = "tLevelState";

            $row = [
                'name' => strtolower($name),
                'offset' => $variable['offset'],
                'size' => $variable['size'],

                //TODO lvl1 verwendet 01 ja aber der rest nicht
                'unknownType' => '01000000',
                'objectType' => $varType,

                //todo: mh1 brauch das
                'occurrences' => []
            ];

            //todo...
            if (strtolower($name) == "ldebuggingflag"){
                $row['unknown'] = '012000b6012000dd03200072192000b319';

            }


            $result[] = $row;
        }

        usort($result, function($a,$b){
            return $a['name'] > $b['name'];
        });

        return $result;
    }

}