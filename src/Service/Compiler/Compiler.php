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

    private function getVariables( $tokens ){

        $smemOffset = 0;
        $current = 0;
        $currentSection = 'header';

        $vars = [];

        $inside = false;

        while ($current < count($tokens)) {

            $token = $tokens[ $current ];

            // we need to know the current section for the defined vars
            if ($token['type'] == Token::T_SCRIPT) $currentSection = 'script';


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
                    $variableType = $tokens[$current + 2]['value'];

                    $row = [
                        'section' => $currentSection,
                        'type' => $variableType,
//                        'offset' => Helper::fromIntToHex($smemOffset)
                    ];

                    if (substr(strtolower($variableType), 0, 7) == "string["){
                        $size = (int) explode("]", substr($variableType, 7))[0];
                        $row['size'] = $size;


//                        if ($size % 4 == 0){
//                            $smemOffset += $size;
//                        }else{
                            $row['offset'] = Helper::fromIntToHex($size);
//                            $smemOffset += $size + (4 - $size % 4);
//                        };

                    }else{
                        switch (strtolower($variableType)){
                            case 'vec3d':
                                $size = 16; // 3 floats a 4-bytes
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


        $strings = [];
        foreach ($tokens as $token) {

            if (isset($token['params'])) {
                $innerStrings = $this->getStrings($token['params'], $smemOffset);
                foreach ($innerStrings as $index => $innerString) {
                    if (!isset($strings[$index])) $strings[$index] = $innerString;
                }
            }else if (isset($token['body'])){
                $innerStrings = $this->getStrings($token['body'], $smemOffset);
                foreach ($innerStrings as $index => $innerString) {
                    if (!isset($strings[$index])) $strings[$index] = $innerString;
                }
            }else if (isset($token['cases'])){
                foreach ($token['cases'] as $case) {
                    $innerStrings = $this->getStrings($case['condition'], $smemOffset);
                    foreach ($innerStrings as $index => $innerString) {
                        if (!isset($strings[$index])) $strings[$index] = $innerString;
                    }

                    $innerStrings = $this->getStrings($case['isTrue'], $smemOffset);
                    foreach ($innerStrings as $index => $innerString) {
                        if (!isset($strings[$index])) $strings[$index] = $innerString;
                    }
                }
            }else{

                if ($token['type'] == Token::T_STRING){

                    $value = str_replace('"', '', $token['value']);

                    $strings[$value] = [
                        'offset' => Helper::fromIntToHex($smemOffset),
                        'length' => strlen($value)
                    ];

                    $length = strlen($value) + 1;
                    $smemOffset += $length + (4 - $length % 4);
                }
            }
        }

        return $strings;
    }

    private function getStrings_old($tokens, &$smemOffset){
        $strings = [];

        $currentScript = 0;
//var_dump($tokens);
        foreach ($tokens as $token) {
            // we need to know the current section for the defined vars
            if ($token['type'] == Token::T_SCRIPT) $currentScript++;

            if ($token['type'] != Token::T_STRING) continue;
            if ($currentScript == 0) continue;

            $value = str_replace('"', '', $token['value']);

//            if (!isset($strings[$currentScript])) $strings[$currentScript] = [];

//            $strings[$currentScript][$value] = [

            if (isset($strings[$value])) continue;

            $strings[$value] = [
                'offset' => Helper::fromIntToHex($smemOffset),
                'length' => strlen($value)
            ];

            $length = strlen($value) + 1;

//            if ($length % 4 == 0){
//                $smemOffset += $length;
//            }else{
                $smemOffset += $length + (4 - $length % 4);
//            };


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


        $source = str_replace(
            "while Invul = TRUE AND IsEntityAlive('SobbingWoman(hunter)') do",
            "while (Invul = TRUE) AND (IsEntityAlive('SobbingWoman(hunter)')) do",
            $source
        );


        $smemOffset = 0;

        // cleanup the source code
        $source = $this->prepare($source);

        // convert script code into tokens
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->run($source);

        // extract every header and script variable definition
        $variables = $this->getVariables($tokens);

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

//        var_dump($tokens);
//        var_dump($ast);



        $header = [];
        $currentSection = "header";

        $sectionCode = [];
        $start = 1;

        $lineCount = 1;

        foreach ($ast["body"] as $token) {

            if (
                $token['type'] == Token::T_SCRIPT ||
                $token['type'] == Token::T_PROCEDURE ||
                $token['type'] == Token::T_FUNCTION
            ){
                $currentSection = "script";

                $strings = $this->getStrings($token['body'], $smemOffset);

                /**
                 * Translate Token AST to Bytecode
                 */
                $emitter = new Emitter( $variables, $strings, $types, $const, $lineCount );

                $code = $emitter->emitter([
                    'type' => "root",
                    'body' => [
                        $token
                    ]
                ]);

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

        return [$sectionCode, []];

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
                $new = $this->fixWriteDebug( $item['body'] );

                if (is_array($new)){
                    foreach ($new as $item2) {
                        $item['body'][] = $item2;
                    }
                }
            }

            if (
                $item['type'] == Token::T_FUNCTION &&
                strtolower($item['value']) == "writedebug"
            ){

                foreach ($item['params'] as $innerIndex => $param) {
                    $new = [
                        'type' => Token::T_FUNCTION,
                        'value' => 'writedebug',
                        'nested' => false,
                        'last' => count($item['params']) - 1 == $innerIndex,
                        'index' => $innerIndex,
                        'params' => [ $param ]
                    ];

                    if ($innerIndex == 0){
                        $item = $new;
                    }else{
                        $add[] = $new;

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