<?php
namespace App\Service\Compiler;

use App\Service\Compiler\FunctionMap\Manhunt;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\FunctionMap\ManhuntDefault;
use App\Service\Helper;

class NewCompiler
{

    private $parentScript = false;

    protected $types = [];
    protected $headerVariables = [];
    protected $constants = [];
    protected $stringsForScript = [];
    protected $variablesOverAllScripts = [];

    protected $combinedVariables = [];

    /**
     * TODO: renamen, thats constant strings
     */
    protected $headerStrings = [];

    protected $procedures = [];
    protected $customFunction = [];


    protected $memoryOffset = 0;
    protected $blockOffsets = [];
    protected $scriptBlockSizes = [];

    protected $lastScriptEnd = 0;

    protected $lineCount = 1;
    protected $calculatedLineCount = 1;

    protected $untouchedSource = '';
    protected $ast = false;
    protected $tokens = [];

    public function __construct($source, $parentScript = false)
    {

        $this->untouchedSource = $source;

        // cleanup the source code
        $source = $this->prepare($source);
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->run($source);

        $this->parentScript = $parentScript;

        $this->types = $this->getTypes($tokens);
        list($this->constants, $this->headerStrings) = $this->getConstants($tokens);

        /**
         * Fix some parsing errors
         *
         * TODO: try to solve this inside the tokenizer/parser
         */
        $tokens = $tokenizer->fixShortStatementMissedLineEnd($tokens);
        $tokens = $tokenizer->fixProcedureEndCall($tokens);
        $tokens = $tokenizer->fixCustomFunctionEndCall($tokens);
        $tokens = $tokenizer->fixTypeMapping($tokens, $this->types);
        $tokens = $tokenizer->fixHeaderBracketMismatches($tokens);


        // parse the token list to a ast
        $parser = new Parser();
        $this->ast = $parser->toAST($tokens);

        $this->stringsForScript = $this->getStrings4Script();

        /**
         * Replace the FORWARD order with the actual script/function or procedure code
         */
        $this->ast = $parser->handleForward($this->ast);


        $this->procedures = $this->searchScriptType(Token::T_PROCEDURE);
        $this->customFunction = $this->searchScriptType(Token::T_CUSTOM_FUNCTION);

        $this->headerVariables = $this->getHeaderVariables($tokens);

        $this->combine();


        $this->tokens = $tokens;
    }

    public function compile()
    {

        $result = [];

        foreach ($this->ast["body"] as $token) {

            if (
                $token['type'] == Token::T_SCRIPT ||
                $token['type'] == Token::T_PROCEDURE ||
                $token['type'] == Token::T_CUSTOM_FUNCTION
            ) {
                $code = $this->processBlock($token);
                foreach ($code as $line) {
                    $result[] = $line->hex;
                }

            }
        }

        return [
            'extra' => [
                'headerVariables' => $this->headerVariables
            ],
            'CODE' => $result,
            'DATA' => $this->generateDATA($this->stringsForScript),
            'STAB' => $this->generateSTAB($this->headerVariables, $result, $this->variablesOverAllScripts),
            'SCPT' => $this->generateSCPT('mh2'),
            'ENTT' => $this->generateEntity(),
            'SRCE' => $this->untouchedSource,

            //todo: value did not match...
            'SMEM' => 78596,
            'DMEM' => 78596,
            'LINE' => []

        ];
    }

    /**
     *
     */

    private function processBlock($token)
    {


        $scriptName = strtolower($token['value']);

        /**
         * Save the start point of each block
         * We need this for procedures and functions, there called by the start point.
         */
        $this->blockOffsets[$scriptName] = [
            'blockType' => $token['type'],
            'offset' => $this->lineCount - 1,
            'section' => 'script',
            'type' => 'custom_functions'
        ];


        // OLD CODE; DO REFACTOR
        if ($token['type'] == Token::T_PROCEDURE) {
            $this->procedures[$scriptName] = $this->lineCount - 1;
        } else if ($token['type'] == Token::T_CUSTOM_FUNCTION) {
            $this->customFunction[$scriptName] = $this->lineCount - 1;
        }
        // OLD CODE; DO REFACTOR

        $scriptVar = $this->getScriptVar($token['body']);

        /**
         * Translate Token AST to Bytecode
         */
        $emitter = new Emitter(
            array_merge($this->combinedVariables, $scriptVar),
            array_merge($this->stringsForScript[$scriptName], $this->headerStrings),


            $scriptVar,
            $this->types,
            $this->constants,
            $this->lineCount
        );

        $code = $emitter->emitter($token, true, [

            'procedures' => $this->procedures,
            'customFunctions' => $this->customFunction,

            'combinedVariables' => array_merge($this->combinedVariables, $scriptVar),

            'blockOffsets' => $this->blockOffsets
        ]);

        /**
         * Calculate the end of each SCRIPT block
         * Any PROCEDURE or FUNCTION will just count up the size
         */
        if ($token['type'] == Token::T_SCRIPT) {
            $this->scriptBlockSizes[$scriptName] = $this->lastScriptEnd;
            $this->lastScriptEnd = count($code) * 4;
        } else if (
            $token['type'] == Token::T_PROCEDURE ||
            $token['type'] == Token::T_CUSTOM_FUNCTION
        ) {

            $this->lastScriptEnd += count($code) * 4;
        }

        /** Validate actual line number with calculated one */
        $this->validateLineCount($code);

        if (count($code)) $this->lineCount = end($code)->lineNumber + 1;

        return $code;
    }

    private function combine()
    {

        $combinedVariables = [];

        $combinedVariables = array_merge($combinedVariables, Manhunt2::$constants);
        $combinedVariables = array_merge($combinedVariables, ManhuntDefault::$constants);

        $combinedVariables = array_merge($combinedVariables, Manhunt2::$functions);
        $combinedVariables = array_merge($combinedVariables, ManhuntDefault::$functions);

        $combinedVariables = array_merge($combinedVariables, $this->types);

        $combinedVariables = array_merge($combinedVariables, $this->constants);

        $combinedVariables = array_merge($combinedVariables, $this->headerVariables);

        $this->combinedVariables = $combinedVariables;


    }

    /**
     * Helper
     */
    private function isVariableInUse($tokens, $var)
    {

        $result = $this->recursiveSearch($tokens, [
            Token::T_VARIABLE,
            Token::T_ASSIGN
        ]);

        foreach ($result as $token) {

            if ($token['value'] == $var) {
                return true;
            }
        }

        return false;
    }

    private function recursiveSearch($tokens, $searchType, $ignoreTypes = [])
    {


        $result = [];
        foreach ($tokens as $token) {

            if (count($searchType) == 0 || in_array($token['type'], $searchType)) {
                if (in_array($token['type'], $ignoreTypes)) {
                    continue;
                } else {
                    $result[] = $token;
                }
            }

            if (isset($token['variable'])) {
                $response = $this->recursiveSearch([$token['variable']], $searchType, $ignoreTypes);
                foreach ($response as $item) {
                    $result[] = $item;
                }
            }

            if (isset($token['start'])) {
                $response = $this->recursiveSearch([$token['start']], $searchType, $ignoreTypes);
                foreach ($response as $item) {
                    $result[] = $item;
                }
            }

            if (isset($token['end'])) {
                $response = $this->recursiveSearch([$token['end']], $searchType, $ignoreTypes);
                foreach ($response as $item) {
                    $result[] = $item;
                }
            }

            if (isset($token['params'])) {
                $response = $this->recursiveSearch($token['params'], $searchType, $ignoreTypes);
                foreach ($response as $item) {
                    $result[] = $item;
                }

            } else if (isset($token['body'])) {
                $response = $this->recursiveSearch($token['body'], $searchType, $ignoreTypes);
                foreach ($response as $item) {
                    $result[] = $item;
                }
            } else if (isset($token['cases'])) {

                if (isset($token['switch'])) {
                    $response = $this->recursiveSearch([$token['switch']], $searchType, $ignoreTypes);
                    foreach ($response as $item) {
                        $result[] = $item;
                    }
                }

                foreach ($token['cases'] as $case) {

                    if (!isset($case['condition'])) {
                        $response = $this->recursiveSearch($case['body'], $searchType, $ignoreTypes);
                        foreach ($response as $item) {
                            $result[] = $item;
                        }
                    }

                    if (isset($case['condition'])) {
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

    private function getMemorySizeByType($type, $add4Bytes = true)
    {

        if (substr($type, 0, 7) == "string[") {
            $len = (int)explode("]", substr($type, 7))[0];

            if ($add4Bytes) {
                if ($len % 4 == 0) $len += 4;
            }

            return $len;
        }

        switch ($type) {
            case 'vec3d':
                return 12; // 3 floats a 4-bytes
                break;

            default:
                return 4;
                break;

        }
    }

    private function calculateMissedStringSize($length)
    {
        if (4 - $length % 4 != 0) return 4 - $length % 4;
        return 0;
    }

    private function calculateMissedIntegerSize($length)
    {
        if ($length % 4 != 0) return $length % 4;
        return 0;
    }

    private function searchScriptType($type)
    {

        $result = [];

        foreach ($this->ast['body'] as $token) {
            if ($token['type'] == $type) {

                $result[strtolower($token['value'])] = false;
            }
        }

        return $result;
    }

    private function validateLineCount($code)
    {
        foreach ($code as $line) {
            if ($line->lineNumber !== $this->calculatedLineCount) {
                throw new \Exception('Calculated line number did not match with the generated one');
            }

            $this->calculatedLineCount++;
        }
    }

    private function prepare($source)
    {

        $source = str_replace([

//            "if (GetEntity('Syringe_(CT)')) <> NIL then",
//            "if (GetEntity('Syringe_(CT)')) = nil then",
            "if(",
            "while(",
            "PLAYING  TWITCH",
            "if bMeleeTutDone AND (IsNamedItemInInventory(GetPlayer, CT_SYRINGE ) <> -1) then",
            "if (NOT IsPlayerPositionKnown) AND IsScriptAudioStreamCompleted then"
        ], [

//            "if GetEntity('Syringe_(CT)') <> NIL then",
//            "if GetEntity('Syringe_(CT)') = nil then",
            "if (",
            "while (",
            "PLAYING__TWITCH",  // we replace this because the next operation will remove the whitespaces

            "if (bMeleeTutDone) AND (IsNamedItemInInventory(GetPlayer, CT_SYRINGE ) <> -1) then",
            "if (NOT IsPlayerPositionKnown) AND (IsScriptAudioStreamCompleted) then"

        ], $source);


        // remove double whitespaces
        $source = preg_replace("/\s+/", ' ', $source);

        // remove comments / unused code

        $source = preg_replace("/\{.*?\}/m", "", $source);
//        $source = preg_replace("/({([^{^}])*)*{([^{^}])*}(([^{^}])*})*/m", "", $source);

        if (preg_last_error() == PREG_JIT_STACKLIMIT_ERROR) {
            die("PHP7 issue, pls disable pcre.jit=0 in your php.ini");
        }

        $source = str_replace([
            "PLAYING__TWITCH",
            "end end",
            "if IsEntityAlive('TruckGuard1(hunter)') or IsEntityAlive('TruckGuard2(hunter)') then",
        ], [
            "PLAYING  TWITCH",
            "end; end",
            "if (IsEntityAlive('TruckGuard1(hunter)')) or (IsEntityAlive('TruckGuard2(hunter)')) then",
        ], $source);

        // replace line ends with new lines
        $source = preg_replace("/;/", ";\n", $source);

        $source = trim($source);

        if (empty($source)){
            throw new \Exception('Cleanup going wrong, source is empty');
        }

        return $source;
    }


    /**
     * Getter
     */
    private function getTypes($tokens)
    {

        $types = [];

        $current = 0;
        $offset = 0;
        $inside = false;
        $currentTypeSection = false;

        while ($current < count($tokens)) {

            $token = $tokens[$current];

            if ($token['type'] == Token::T_DEFINE_SECTION_TYPE) {
                $inside = true;

            } else if (
                $inside && (
                    $token['type'] == Token::T_DEFINE_SECTION_VAR ||
                    $token['type'] == Token::T_DEFINE_SECTION_ENTITY ||
                    $token['type'] == Token::T_DEFINE_SECTION_CONST ||
                    $token['type'] == Token::T_PROCEDURE ||
                    $token['type'] == Token::T_CUSTOM_FUNCTION ||
                    $token['type'] == Token::T_SCRIPT
                )
            ) {

                return $types;

            } else if (
                $token['type'] == Token::T_BRACKET_OPEN ||
                $token['type'] == Token::T_BRACKET_CLOSE
            ) {
                // do nothing
            } else if (
                $token['type'] == Token::T_LINEEND
            ) {

                $currentTypeSection = false;
            } else if ($inside) {

                if ($token['type'] == Token::T_IS_EQUAL) {
                    $beforeToken = $tokens[$current - 1];

                    $offset = 0;
                    $currentTypeSection = strtolower($beforeToken['value']);

                    $types[$currentTypeSection] = [];


                } else if ($currentTypeSection && $token['type'] == Token::T_VARIABLE) {

                    $types[$currentTypeSection][strtolower($token['value'])] = [
                        'type' => 'level_var state',
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

    private function getHeaderVariables($tokens)
    {

        $current = 0;
        $currentSection = 'header';

        $vars = [];

        $inside = false;

        while ($current < count($tokens)) {

            $token = $tokens[$current];

            // we need to know the current section for the defined vars
            if (
                $inside &&
                (
                    $token['type'] == Token::T_DEFINE_SECTION_TYPE ||
                    $token['type'] == Token::T_DEFINE_SECTION_ENTITY ||
                    $token['type'] == Token::T_DEFINE_SECTION_CONST ||
                    $token['type'] == Token::T_PROCEDURE ||
                    $token['type'] == Token::T_CUSTOM_FUNCTION ||
                    $token['type'] == Token::T_SCRIPT
                )

            ) {
                break;
            }

            if (
                $token['type'] == Token::T_SCRIPT ||
                $token['type'] == Token::T_CUSTOM_FUNCTION ||
                $token['type'] == Token::T_PROCEDURE
            )
                break;

            if ($token['type'] == Token::T_DEFINE_SECTION_VAR) $inside = true;

            if ($inside == false) {
                $current++;
                continue;
            }

            if ($token['type'] == Token::T_VARIABLE && $tokens[$current + 1]['type'] == Token::T_DEFINE) {

                $variables = [
                    $token
                ];

                $prevToken = $tokens[$current - 1];
                $innerCurrent = $current;
                while ($prevToken['type'] == Token::T_VARIABLE) {
                    $variables[] = $prevToken;
                    $innerCurrent--;
                    $prevToken = $tokens[$innerCurrent - 1];
                }

                $variables = array_reverse($variables);

                foreach ($variables as $variable) {
                    if (!$this->isVariableInUse($tokens, $variable['value'])) continue;

                    $variableType = strtolower($tokens[$current + 2]['value']);


                    $isLevelVar = strpos($variableType, 'level_var') !== false;
                    $variableTypeWihtoutLevel = str_replace('level_var ', '', $variableType);

                    $row = [
                        'section' => $currentSection,
                        'type' => substr($variableTypeWihtoutLevel, 0, 7) == "string[" ? ($isLevelVar ? 'level_var stringarray' : 'stringarray') : $variableType,
                        'length' => $this->getMemorySizeByType($variableTypeWihtoutLevel),
                        'size' => $this->getMemorySizeByType($variableTypeWihtoutLevel, false)
                    ];

                    if (isset($this->types[$variableTypeWihtoutLevel])) {
                        $row['isLevelVar'] = $isLevelVar;
                        $row['abstract'] = 'state';
                    }

                    $vars[$variable['value']] = $row;
                }
            }

            $current++;
        }


        /**
         * Apply variables from parent script
         */
        if ($this->parentScript != false) {
            $parentVariables = $this->parentScript['extra']['headerVariables'];

            // loop over the parent variables
            foreach ($parentVariables as $parentVariableName => $parentVariable) {

                // look if we use a parent varoable
                foreach ($vars as $headerVariableName => &$headerVariable) {
                    if (strpos(strtolower($headerVariable['type']), 'level_var') === false) continue;
                    if ($parentVariableName != $headerVariableName) continue;

                    $headerVariable['offset'] = $parentVariable['offset'];
                }

            }
        }


        /**
         * Calculate the needed memory
         */
        foreach ($vars as $name => &$item) {

            if (!isset($item['offset'])) {
                $item['offset'] = Helper::fromIntToHex($this->memoryOffset);
            }

            $size = $item['length'];

            $size += $this->calculateMissedIntegerSize($size);


            $this->memoryOffset += $size;
        }

        return $vars;
    }

    private function getConstants($tokens)
    {

        $current = 0;

        $constants = [];
        $currentSection = false;

        while ($current < count($tokens)) {

            $token = $tokens[$current];

            // we need to know the current section for the defined vars
            if ($token['type'] == Token::T_DEFINE_SECTION_CONST) {
                $currentSection = Token::T_DEFINE_SECTION_CONST;
                $current++;
                continue;
            }

            if ($currentSection == Token::T_DEFINE_SECTION_CONST) {

                if (
                    $token['type'] == Token::T_DEFINE_SECTION_VAR ||
                    $token['type'] == Token::T_DEFINE_SECTION_ENTITY ||
                    $token['type'] == Token::T_DEFINE_SECTION_TYPE ||
                    $token['type'] == Token::T_PROCEDURE ||
                    $token['type'] == Token::T_CUSTOM_FUNCTION ||
                    $token['type'] == Token::T_SCRIPT
                ) {
                    break;
                } else {
                    $variable = $token['value'];

                    $constants[$variable] = $tokens[$current + 2];

                    if (
                        $constants[$variable]['type'] == Token::T_INT ||
                        $constants[$variable]['type'] == Token::T_FLOAT
                    ) {
                        //just rais the memory, we dont need to save a offset for numerics
                        $this->memoryOffset += 4;
                    }

                    $current = $current + 3;
                }
            }

            $current++;
        }


        /**
         * Caclulate string offsets
         */

        $tmpArray = [];
        $strings = [];
        foreach ($constants as $item) {

            if ($item['type'] == Token::T_STRING) {
                $string = str_replace('"', '', $item['value']);

                if(!isset($tmpArray[$string])){
                    $tmpArray[$string] = $string;

                    $length = strlen($string) + 1;
                    $strings[$string] = [
                        'offset' => Helper::fromIntToHex($this->memoryOffset),
                        'length' => strlen($string)
                    ];


                    $this->memoryOffset += $length + $this->calculateMissedStringSize($length);

                }
            }
        }


        foreach ($constants as &$var) {

            $var['section'] = 'script';

            if ($var['type'] == Token::T_INT) {
                $var['valueType'] = "integer";

            } else if ($var['type'] == Token::T_STRING) {
                $var['valueType'] = "string";

            } else if ($var['type'] == Token::T_FLOAT) {
                $var['valueType'] = "float";

            }

            //todo: hmm ich brauch den richtigen type in T_SCRIPT_CONSTANT -.-
//            $var['type'] = 'constant';

        }

        /**
         * apply the hardcoded constants
         */

        foreach (
            array_merge(
                ManhuntDefault::$constants,
                Manhunt2::$constants
            ) as $index => $hardCodedConstant) {

            $hardCodedConstant['section'] = 'header';
            $hardCodedConstant['type'] = 'constant';
            $constants[$index] = $hardCodedConstant;
        }

        return [$constants, $strings];
    }

    private function getStrings4Script()
    {
        $strings4Scripts = [];
        foreach ($this->ast["body"] as $index => $token) {

            if (
                $token['type'] == Token::T_SCRIPT ||
                $token['type'] == Token::T_PROCEDURE ||
                $token['type'] == Token::T_CUSTOM_FUNCTION
            ) {

                $scriptName = strtolower($token['value']);


                $response = $this->recursiveSearch($token['body'], [Token::T_STRING]);

                $result = [];

                foreach ($response as $item) {

                    $value = str_replace('"', '', $item['value']);
                    $value = str_replace("'", '', $value);

                    if ($value == "") {
                        $result["__empty__"] = '';

                    } else {

                        $result[$value] = $value;
                    }
                }

                $strings = array_unique($result);
                foreach ($strings as &$string) {

                    $length = strlen($string) + 1;

                    $string = [
                        'offset' => Helper::fromIntToHex($this->memoryOffset)
                    ];

                    $length += $this->calculateMissedStringSize($length);

                    $this->memoryOffset += $length;

                }

                $strings4Scripts[$scriptName] = $strings;
            }
        }

        return $strings4Scripts;
    }

    private function getScriptVar($tokens)
    {

        $originalTokens = $tokens;

        $otherTokens = [];
        $varSection = [];

        foreach ($tokens as $token) {
            if ($token['type'] == Token::T_DEFINE_SECTION_VAR) {
                $varSection = $token['body'];
            } else {
                $otherTokens[] = $token;
            }
        }

        $tokens = $varSection;
        $current = 0;
        $vars = [];

        while ($current < count($tokens)) {

            $token = $tokens[$current];

            if ($token['type'] == Token::T_VARIABLE && $tokens[$current + 1]['type'] == Token::T_DEFINE_TYPE) {

                $variables = [$token];

                $oriPos = $current;
                if (isset($tokens[$current - 1])) {

                    $prevToken = $tokens[$current - 1];
                    while ($prevToken['type'] == Token::T_VARIABLE) {
                        $variables[] = $prevToken;
                        $current--;
                        if (!isset($tokens[$current - 1])) break;
                        $prevToken = $tokens[$current - 1];
                    }
                }

                $variables = array_reverse($variables);

                $current = $oriPos + 1;

                $variableType = strtolower($tokens[$current]['value']);

                foreach ($variables as $variable) {
                    $variable = $variable['value'];

                    $row = [
                        'section' => 'script',
                        'type' => $variableType
                    ];

                    if (substr($variableType, 0, 7) == "string[") {
                        $row['type'] = 'stringarray';
                    }

                    $row['size'] = $this->getMemorySizeByType($variableType);

                    $vars[$variable] = $row;
                }
            }

            $current++;
        }

        /**
         * Calculate offsets
         */

        $blockMemory = 0;
        $scriptVarFinal = [];
        foreach ($vars as $name => &$item) {
            $blockMemory += $item['size'];

            $item['offset'] = Helper::fromIntToHex($blockMemory);

            $blockMemory += $this->calculateMissedIntegerSize($blockMemory);

            $scriptVarFinal[$name] = $item;
            $this->variablesOverAllScripts[$name] = $item;
        }

        foreach ($this->headerVariables as $_name => $_item) {

            if ($this->isVariableInUse($originalTokens, $_name)) {
                if (!isset($scriptVarFinal[$_name])) {
                    $scriptVarFinal[$_name] = $_item;
                }
            }
        }

        return $scriptVarFinal;
    }


    /**
     * generate MLS blocks
     */

    private function generateSCPT($game)
    {

        $scpt = [];
        $scriptSize = 0;
        foreach ($this->scriptBlockSizes as $name => $item) {
            $scriptSize += $item;

            $functionEventDefinitionDefault = ManhuntDefault::$functionEventDefinition;
            $functionEventDefinition = Manhunt2::$functionEventDefinition;
            if ($game == "mh1") $functionEventDefinition = Manhunt::$functionEventDefinition;

            if (isset($functionEventDefinitionDefault[strtolower($name)])) {
                $onTrigger = $functionEventDefinitionDefault[strtolower($name)];
            } else if (isset($functionEventDefinition[strtolower($name)])) {
                $onTrigger = $functionEventDefinition[strtolower($name)];
            } else {
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

    private function generateDATA($strings4Scripts)
    {
        $result = [];

        foreach ($strings4Scripts as $strings) {
            foreach ($strings as $value => $string) {
                if ($value !== '__empty__') $result[] = $value;
            }
        }

        if (count($result) == 0) {
            $result[] = hex2bin('dadadadadadadada');
        }

        return $result;
    }

    private function generateSTAB($headerVariables, $sectionCode, $variablesOverAllScripts)
    {

        $result = [];

        $memoryForDoubleEntries = 0;

        foreach ($headerVariables as $name => $variable) {

            $occur = [];

            $varType = $variable['type'];
            $hierarchieType = '01000000';

            if (substr($varType, 0, 9) == "level_var") {
                $varType = substr($varType, 10);

                foreach ($sectionCode as $index => $code) {

                    if ($code == $variable['offset']) {
                        $occur[] = $index * 4;
                    }
                }

                $hierarchieType = "ffffffff";
                $variable['offset'] = "ffffffff";
                $variable['size'] = "ffffffff";
            }


            /**
             * when the variable is defined inside the HEADER and also in one or multiple scripts, we need to give him the 02 sequence
             */
//            if ($variable['type'] != "vec3d"){
                foreach ($variablesOverAllScripts as $varScriptName => $variablesOverAllScript) {
                    if ($varScriptName == $name) {
                        $hierarchieType = '02000000';
                        $variable['offset'] = Helper::fromIntToHex($memoryForDoubleEntries);

                        if ($variable['type'] == "vec3d"){
                            $memoryForDoubleEntries += 12;
                        }else{
                            $memoryForDoubleEntries += 4;
                        }
                    }
                }

//            }


            if (strtolower($varType) == "tlevelstate") $varType = "tLevelState";
            if ($varType == "stringarray") $varType = "string";

            if ($varType == "entityptr"){
                $varType = "integer";
            }

            /**
             * todo: not important, the type should say tLevelState but its messed up by the state handling
             */
            if (isset($variable['abstract']) && $variable['abstract'] == "state") {
                $varType = "tLevelState";
            }

            $row = [
                'name' => strtolower($name),
                'offset' => $variable['offset'],
                'size' => $variable['size'],

                'hierarchieType' => $hierarchieType,
                'objectType' => ($varType),

                'occurrences' => $occur
            ];


            //todo...
//            if (strtolower($name) == "ldebuggingflag"){
//                $row['unknown'] = '012000b6012000dd03200072192000b319';
//            }

            $result[] = $row;
        }
        usort($result, function ($a, $b) {
            return $a['name'] > $b['name'];
        });

        return $result;
    }

    private function generateEntity()
    {

        $tokens = $this->tokens;
        $current = 0;

        if (!isset($tokens[1])){
            return [];
        }

        $scriptName = strtolower($tokens[1]['value']);

        while ($current < count($tokens)) {

            $token = $tokens[$current];

            if ($token['type'] == Token::T_DEFINE_SECTION_ENTITY) {

                return [
                    'name' => strtolower($tokens[$current + 1]['value']),
                    'type' => $scriptName == "levelscript" ? "levelscript" : "other"
                ];
            }

            $current++;
        }

        throw new \Exception('Compiler could not find / parse the Entity section');
    }

}