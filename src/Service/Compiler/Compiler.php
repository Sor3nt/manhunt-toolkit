<?php
namespace App\Service\Compiler;

use App\Bytecode\Helper;
use App\Service\Compiler\FunctionMap\Manhunt2;

class Compiler {

    /**
     * @param $source
     * @return mixed
     */
    private function prepare($source){
        // remove double whitespaces
        $source = preg_replace("/\s+/", ' ', $source);

        // remove comments / unused code
        // todo: multi line comments not supported
        $source = preg_replace("/\{(.*?)\}/", "", $source);

        // replace line ends with new lines
        $source = preg_replace("/;/", ";\n", $source);

        return trim($source);
    }

    private function getHeaderVariables( $tokens ){

        $current = 0;
        $currentSection = 'header';

        $vars = [];

        $inside = false;

        while ($current < count($tokens)) {

            $token = $tokens[ $current ];

            // we need to know the current section for the defined vars
            if ($token['type'] == Token::T_SCRIPT) return $vars;


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
//
                    if (!$this->isVariableInUse($tokens, $variable)){
                        continue;
                    }


                    $variableType = strtolower($tokens[$current + 2]['value']);

                    $row = [
                        'section' => $currentSection,
                        'type' => $variableType,
//                        'offset' => Helper::fromIntToHex($smemOffset)
                    ];

                    if (substr($variableType, 0, 7) == "string["){
                        $size = (int) explode("]", substr($variableType, 7))[0];
                        $row['size'] = $size;
                        $row['type'] = 'stringarray';


//                        if ($size % 4 == 0){
//                            $smemOffset += $size;
//                        }else{
                            $row['offset'] = Helper::fromIntToHex($size);
//                            $smemOffset += $size + (4 - $size % 4);
//                        };

                    }else{
                        switch ($variableType){
                            case 'vec3d':
                                $size = 12; // 3 floats a 4-bytes
                                $row['offset'] = Helper::fromIntToHex($size);
//                                $smemOffset += $size;
                                break;
                            case 'level_var boolean':

                                $size = 4;

                                $row['offset'] = Manhunt2::$levelVarBoolean[$token['value']]['offset'];
                                break;

                            case 'level_var tlevelstate':

                                $size = 4;
                                $row['offset'] = Manhunt2::$levelVarBoolean["tLevelState"]['offset'];
                                break;

                            case 'boolean':
                            case 'et_name':
                            case 'entityptr':
                            case 'televatorlevel':
                            default:
                                $size = 4;
                                $row['offset'] = Helper::fromIntToHex($size);
//                                $smemOffset += $size;
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

    private function isVariableInUse($tokens, $var, $start = false){

        $result = $this->recursiveSearch($tokens, [
            Token::T_VARIABLE,
            Token::T_ASSIGN
        ]);

        foreach ($result as $token) {

            echo "compare " . $token['value'] . " == " . $var . "\n";
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
                $token['type'] == Token::T_DEFINE_SECTION_VAR ||
                $token['type'] == Token::T_DEFINE_SECTION_ENTITY ||
                $token['type'] == Token::T_DEFINE_SECTION_CONST ||
                $token['type'] == Token::T_SCRIPT
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
                    $currentTypeSection = $beforeToken['value'];

                    $types[ $currentTypeSection ]  = [];


                }else if ($currentTypeSection && $token['type'] == Token::T_VARIABLE){

                    $types[ $currentTypeSection ][ $token['value'] ] = [
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

        $types = [];

        $current = 0;
        $offset = 0;
        $inside = false;
        $currentTypeSection = false;

        while( $current < count($tokens)){

            $token = $tokens[ $current ];

            if ($token['type'] == Token::T_DEFINE_SECTION_ENTITY) {
                $inside = true;

            }else if (
                $token['type'] == Token::T_DEFINE_SECTION_VAR ||
                $token['type'] == Token::T_DEFINE_SECTION_TYPE ||
                $token['type'] == Token::T_DEFINE_SECTION_CONST ||
                $token['type'] == Token::T_SCRIPT
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
                    $currentTypeSection = $beforeToken['value'];

                    $types[ $currentTypeSection ]  = [];


                }else if ($currentTypeSection && $token['type'] == Token::T_VARIABLE){

                    $types[ $currentTypeSection ][ $token['value'] ] = [
                        'offset' => Helper::fromIntToHex($offset)
                    ];

                    $offset++;
                }

            }

            $current++;
        }

        return $types;
    }

    /**
     * @param $source
     * @return array
     */
    public function parse($source){

//
//        $source = str_replace(
//            "while Invul = TRUE AND IsEntityAlive('SobbingWoman(hunter)') do",
//            "while (Invul = TRUE) AND (IsEntityAlive('SobbingWoman(hunter)')) do",
//            $source
//        );


        $smemOffset = 0;

        // cleanup the source code
        $source = $this->prepare($source);

        // convert script code into tokens
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->run($source);

        // extract every header and script variable definition
        $headerVariables = $this->getHeaderVariables($tokens);

        $types = $this->getTypes($tokens);
        $entity = $this->getEntitity($tokens);

        $const = $this->getConstants($tokens, $smemOffset);
//        $strings = $this->getStrings($tokens, $smemOffset);



        $tokens = $tokenizer->fixProcedureEndCall($tokens);
        $tokens = $tokenizer->fixTypeMapping($tokens, $types);
        $tokens = $tokenizer->fixHeaderBracketMismatches($tokens, $types);

        // parse the token list to a ast
        $parser = new Parser( );
        $ast = $parser->toAST($tokens);

        $this->fixWriteDebug($ast['body']);

        var_dump($tokens);
        var_dump($ast);

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
                $strings4Scripts[$index] = $this->getStrings($token['body'], $smemOffset);

            }
        }

        foreach ($ast["body"] as $index => $token) {

            if (
                $token['type'] == Token::T_SCRIPT ||
                $token['type'] == Token::T_PROCEDURE ||
                $token['type'] == Token::T_FUNCTION
            ){
                $currentSection = "script";

                /**
                 * Calculate string offsets
                 */

                $scriptVar = $this->getScriptVar($token['body'], $smemOffset2);

                $smemOffset2 = 0;

                $scriptVarFinal = [];
                foreach ($scriptVar as $name => $item) {
                    if (!isset($item['offset'])){

                        $smemOffset2 += $item['size'];
                        $item['offset'] = Helper::fromIntToHex($smemOffset2);
                        $scriptVarFinal[$name ] = $item;

                    }
                }

                foreach ($headerVariables as $name => $item) {

                    if ($this->isVariableInUse($token['body'], $name)){

                        $item['offset'] = Helper::fromIntToHex($smemOffset);
                        $smemOffset += $item['size'];

                        $scriptVarFinal[$name ] = $item;
                    }
                }



                /**
                 * Translate Token AST to Bytecode
                 */
                $emitter = new Emitter(  $scriptVarFinal, $strings4Scripts[$index], $types, $const, $lineCount );

                $code = $emitter->emitter([
                    'type' => "root",
                    'body' => [
                        $token
                    ]
                ]);

                foreach ($code as $line) {

                    if ($line->lineNumber !== $start){
//                        var_dump( $line, $start);
//                        throw new \Exception('Calulated line number did not match with the generated one');
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

        return [$sectionCode, []];

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
                foreach ($token['cases'] as $case) {
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

        return $result;

    }

    private function getScriptVar( $tokens , &$smemOffset){

        $otherTokens = [];
        $varSection = [];
        foreach ($tokens as $token) {
            if ($token['type'] == Token::T_DEFINE_SECTION_VAR) {
                $varSection = $token['body'];
//                break;
            }else{
                $otherTokens[] = $token;
            }
        }

        $tokens = $varSection;

        $current = 0;

        $vars = [];

//        $smemOffset = 0;

        while ($current < count($tokens)) {

            $token = $tokens[ $current ];

            if ($token['type'] == Token::T_VARIABLE && $tokens[$current + 1]['type'] == Token::T_DEFINE_TYPE){

                $variables = [
                    $token
                ];

                if (isset($tokens[ $current - 1])){

                    $prevToken = $tokens[ $current - 1];
                    $innerCurrent = $current;
                    while($prevToken['type'] == Token::T_VARIABLE){
                        $variables[] = $prevToken;
                        $innerCurrent--;
                        $prevToken = $tokens[ $innerCurrent - 1];
                    }
                }

                $variables = array_reverse($variables);
                foreach ($variables as $variable) {
                    $variable = $variable['value'];

//                    if (!$this->isVariableInUse($otherTokens, $variable)){
//                        continue;
//                    }


                    $current = $current + 1;

                    $variableType = strtolower($tokens[$current]['value']);

                    $row = [
                        'section' => 'script',
                        'type' => $variableType,
//                        'offset' => Helper::fromIntToHex($smemOffset)
                    ];

                    if (substr($variableType, 0, 7) == "string["){
                        $size = (int) explode("]", substr($variableType, 7))[0];
                        $row['size'] = $size;


//                        if ($size % 4 == 0){
//                            $smemOffset += $size;
//                        }else{
//                        $row['offset'] = Helper::fromIntToHex($size);
//                            $smemOffset += $size + (4 - $size % 4);
//                        };

                    }else{
                        switch ($variableType){
                            case 'vec3d':
                                $size = 12; // 3 floats a 4-bytes
//                                $row['offset'] = Helper::fromIntToHex($size);
//                                $smemOffset += $size;
                                break;
                            case 'level_var boolean':

                                $size = 4;

                                $row['offset'] = Manhunt2::$levelVarBoolean[$token['value']]['offset'];
                                break;

                            case 'level_var tlevelstate':

                                $size = 4;
                                $row['offset'] = Manhunt2::$levelVarBoolean["tLevelState"]['offset'];
                                break;

                            case 'boolean':
                            case 'et_name':
                            case 'entityptr':
                            case 'televatorlevel':
                            default:
                                $size = 4;
//                                $row['offset'] = Helper::fromIntToHex($size);
//                                $smemOffset += $size;
                                break;

                        }

                        $row['size'] = $size;

                    }

                    if (!isset($row['offset'])){


//                        if (4 - $size % 4 != 0){
//                            $size += 4 - $size % 4;
//                        }

//                        $smemOffset += $size;
//                        $row['offset'] = Helper::fromIntToHex($smemOffset);
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
    public function generateDATA( $tokens ){

        $strings = [];

        foreach ($tokens as $token) {
            if ($token['type'] == Token::T_STRING){
                $strings[] = str_replace('"', '', $token['value']);
            }
        }

        return $strings;
    }

}