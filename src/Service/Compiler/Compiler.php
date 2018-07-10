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
        $source = preg_replace("/\{(.*?)\}/", "", $source);

        // replace line ends with new lines
        $source = preg_replace("/;/", ";\n", $source);

        return trim($source);
    }

    private function getVariables( $tokens, &$smemOffset ){

        $current = 0;
        $currentSection = 'body';

        $vars = [];

        while ($current < count($tokens)) {

            $token = $tokens[ $current ];

            // we need to know the current section for the defined vars
            if ($token['type'] == Token::T_SCRIPTMAIN) $currentSection = 'header';
            if ($token['type'] == Token::T_SCRIPT) $currentSection = 'script';


            if ($token['type'] == Token::T_VARIABLE && $tokens[$current + 1]['type'] == Token::T_DEFINE){

                $variable = $token['value'];
                $variableType = $tokens[$current + 2]['value'];

                $row = [
                    'section' => $currentSection,
                    'type' => $variableType,
                    'offset' => Helper::fromIntToHex($smemOffset)
                ];

                if (substr(strtolower($variableType), 0, 7) == "string["){
                    $size = (int) explode("]", substr($variableType, 7))[0];
                    $row['size'] = $size;


                    if ($size % 4 == 0){
                        $smemOffset += $size;
                    }else{
                        $smemOffset += $size + (4 - $size % 4);
                    };

                }else{
                    switch (strtolower($variableType)){
                        case 'vec3d':
                            $size = 12; // 3 floats a 4-bytes
                            $row['offset'] = Helper::fromIntToHex($size);
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
                            $smemOffset += $size;
                            break;

                    }

                    $row['size'] = $size;

                }


                $vars[$variable] = $row;

            }

            $current++;
        }

        return $vars;
    }

    private function getStrings($tokens, &$smemOffset){
        $strings = [];

        $currentScript = 0;

        foreach ($tokens as $token) {
            // we need to know the current section for the defined vars
            if ($token['type'] == Token::T_SCRIPT) $currentScript++;

            if ($token['type'] != Token::T_STRING) continue;

            $value = str_replace('"', '', $token['value']);

            if (!isset($strings[$currentScript])) $strings[$currentScript] = [];

//            $strings[$currentScript][$value] = [
            $strings[$value] = [
                'offset' => Helper::fromIntToHex($smemOffset),
                'length' => strlen($value)
            ];

            $length = strlen($value);

            if ($length % 4 == 0){
                $smemOffset += $length;
            }else{
                $smemOffset += $length + (4 - $length % 4);
            };

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

        $smemOffset = 0;

        // cleanup the source code
        $source = $this->prepare($source);

        // convert script code into tokens
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->run($source);

        // extract every header and script variable definition
        $variables = $this->getVariables($tokens, $smemOffset);


        $types = $this->getTypes($tokens);
        $entity = $this->getEntitity($tokens);
        $strings = $this->getStrings($tokens, $smemOffset);

        $tokens = $tokenizer->fixProcedureEndCall($tokens);
        $tokens = $tokenizer->fixTypeMapping($tokens, $types);
        $tokens = $tokenizer->fixHeaderBracketMismatches($tokens, $types);



        // parse the token list to a ast
        $parser = new Parser( );
        $ast = $parser->toAST($tokens);

        var_dump($tokens);
        var_dump($ast);

        $emitter = new Emitter( $variables, $strings, $types );
        $code = $emitter->emitter($ast);
        $sectionCode = [];
        foreach ($code as $line) {

            $sectionCode[] = $line->hex;
        }

        //        $sectionDATA = $this->generateDATA( $scriptTokens );

        return [$sectionCode, []];

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