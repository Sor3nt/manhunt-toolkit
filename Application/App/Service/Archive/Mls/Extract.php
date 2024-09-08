<?php
namespace App\Service\Archive\Mls;


use App\MHT;
use App\Service\Binary;
use App\Service\Helper;
use App\Service\NBinary;

class Extract {

    private $binary;

    private $game;
    private $platform;

    public function __construct( NBinary $binaryData, $game, $platform)
    {
        $this->game = $game;
        $this->platform = $platform;
        $this->binary = new Binary( $binaryData->binary );
    }

    public function get(){

        /** @var Binary $remain */
        // detect the current version, wii or pc/ps2/psp/xbox?
        $version = $this->binary->substr(4, 4, $remain);


        if ($version == "00090003"){
            $this->platform = MHT::PLATFORM_WII;
        }


        $nextSection = $remain->substr(0, 4)->toString();

        $mhscs = [];

        // there is only one script inside the MLS
        if ($nextSection === "SCPT"){
            $mhscs[] = $this->parse($remain);

            // there is multiple scripts inside the MLS
        }else if ($nextSection === "MHSC"){
            do{

                list(,$data) = $this->getLabelSizeData( $remain, $remain);

                $mhscs[] = $this->parse($data);

            }while($remain->length() > 0);
        }

        return $mhscs;
    }


    private function getLabelSizeData( Binary $data, Binary &$remain = null){

        $label = $data->substr(0, 4, $data);
        $size = $data->substr(0, 4, $data, $this->platform == MHT::PLATFORM_WII);

        return [
            $label->toString(),
            $data->substr(0, $size->toInt(), $remain)
        ];
    }

    /** @var Binary */
    private $dataRaw;

    private function parse(Binary $remain ){
        /** @var Binary $code */
        /** @var Binary $sectionCode */
        /** @var Binary $data */

        $results = [];

        do{
            list($scriptLabel, $data) = $this->getLabelSizeData( $remain, $remain);

            $results[$scriptLabel] = [];

            switch($scriptLabel){

                case 'DMEM': $results['DMEM'] = $this->parseMEM($data); break;
                case 'SCPT': $results['SCPT'] = $this->parseSCPT($data); break;
                case 'NAME': $results['NAME'] = $this->parseNAME($data); break;
                case 'ENTT': $results['ENTT'] = $this->parseENTT($data, $results['NAME']); break;
                case 'CODE': $results['CODE'] = $this->parseCODE($data); break;
                case 'SMEM': $results['SMEM'] = $this->parseMEM($data); break;
                case 'DBUG':
                    $dbug = $this->parseDBUG($data);
                    $results['SRCE'] = $dbug['SRCE'];
                    $results['LINE'] = $dbug['LINE'];
                    $results['TRCE'] = $dbug['TRCE'];
                    if (isset($results['DATA'])) $results['DATA'] = $this->reparseDATA($results);
//                    if (isset($results['DATA'])) $this->reparseDATA($results);


                    break;
                case 'DATA': $results['DATA'] = $this->parseDATA($data, $results); break;
                case 'STAB': $results['STAB'] = $this->parseSTAB($data); break;

                default:
                    throw new \Exception(sprintf('Unknown Script Section %s', $scriptLabel ));
                    break;
            }

        }while($remain->length() > 0);

        //memory workaround
        if (strpos($results['SRCE'], "{#MHT") === false)
            $results['SRCE'] = sprintf("{#MHT SMEM:%s | DMEM:%s}\n", $results['SMEM'],$results['DMEM']) . $results['SRCE'];

        return $results;
    }

    private function parseSCPT( Binary $data ){

        $parts = $data->split(72);

        $result = [];

        foreach ($parts as $index => $part) {

            $name = $part->substr(0, 64, $part);

            list($onTriggerOffset, $position) = $part->split(4, $this->platform == MHT::PLATFORM_WII);

            $result[] = [
                'name' => $name->toString(),
                'onTrigger' => $onTriggerOffset->toHex(),
                'scriptStart' => $position->toInt()
            ];
        }

        return $result;
    }

    private function parseNAME( Binary $data ){
        $name = $data->substr(0, "\x00", $remain);
        $nameGarbage = $remain->toHex();

        return [
            'name' => $name->toString(),
            'nameGarbage' => $nameGarbage
        ];
    }

    private function parseENTT( Binary $data, $levelName ){

        /** @var Binary $name */

//        exit;
        //skip the type
        $type = $data->substr(0, 4, $name);

        if($type->toHex() == "02000000"){
            $type = "levelscript";
        }else{
            $type = "other";
        }

        return [
            'name' => $name->toString(),
            'type' => $type
        ];

    }

    private function parseCODE( Binary $data ){

        $result = [];

        $split = $data->split(4, $this->platform == MHT::PLATFORM_WII);
        foreach ($split as $value) {
            $result[] = $value->toHex();
        }

        return $result;
    }

    private function getConstBySource($srce){

        preg_match('/const\s+/i', $srce, $match);
        if (count($match)){

            $srcLower = strtolower($srce);

            $raw = explode("const", $srcLower)[1];
            $raw = explode("var", $raw)[0];

            if (strpos($raw, "type") !== false){
                $raw = explode("type", $raw)[0];
            }


            $split = explode("=", $raw);
            unset($split[0]);

            $resultsNumberc = [];
            $resultsStrings = [];
            foreach ($split as $item) {
                $item = trim($item);
                $data = explode(";", $item)[0];

                if (strpos($data, "'") !== false){
                    $resultsStrings[] = $data;

                }else{
                    $resultsNumberc[] = strpos($data, ".") !== false ? (float) $data : (int) $data;

                }


            }


            return [$resultsNumberc, $resultsStrings];
        }

        return [[],[]];
    }

    private function reparseDATA( $results ){
        return $this->parseDATA($this->dataRaw, $results);

    }

    private function parseDATA( Binary $data, $results ){

        $binary = new NBinary($data->toBinary());

        $this->dataRaw = new Binary($data->toBinary());

        $result = [
            'const' => [],
            'strings' => [],
            'byteReserved' => 0
        ];

        //consume constants

        $count = 0;
        if (isset($results['SRCE'])){
            list($resultsNumberc, $resultsStrings) = $this->getConstBySource($results['SRCE']);

            foreach ($resultsNumberc as $constEntry) {

                $number = $binary->consume(4,
                    is_float($constEntry) ? NBinary::FLOAT_32 :
                    NBinary::INT_32
                );

                if (is_float($number)){

                    if (0.07999999821186066 === $number)
                        $number = 0.08;

                    if (1.2999999523162842 === $number)
                        $number = 1.3;

                    if (0.07000000029802322 === $number)
                        $number = 0.07;

                    if (Helper::fromFloatToHex($number) == "cdcc4c3d"){
                        $number = 0.05;
                    }

                    /**
                     * Mystery ... the code deliver 1.38 but the DATA need 1.379
                     */
                    if ($number == 1.3799999952316284){
                        $number = 1.38;
                    }

                    if ($number == -13.199999809265137){
                        $number = -13.2;
                    }

                    if ($number == 7.800000190734863){
                        $number = 7.8;
                    }

                }

                $result['const'][] = $number;
            }

        }else{

//        for ($i = 0; $i < $count; $i++){
            while($binary->get(2, 2) == "\x00\x00"){
                $result['const'][] = $binary->consume(4, NBinary::INT_32);
            }
        }

//
//        while($binary->get(2, 2) == "\x00\x00"){
//            $result['const'][] = $binary->consume(4, NBinary::INT_32);
//        }

        //consume strings
        while ($binary->get(1) != "\xda"  && $binary->remain() > 0 ){

            if ($this->game == MHT::GAME_MANHUNT){
                $string = $binary->getString("\x00", true);

                if (mb_strpos($string, "\xda") !== false){
                    $parts = explode("\xda", $string);

                    //remove empty entries
                    $parts = array_filter($parts);
                    foreach ($parts as $part) {
                        $result['strings'][] = $part;
                    }
                    continue;
//                    var_dump(array_filter($parts));exit;
                }
            }else{
                $string = $binary->getString("\xda", true);

                if (mb_substr($string, -1) == "\x00"){
                    $string = mb_substr($string, 0, -1);
                }
            }

            $result['strings'][] = $string;
        }

        //consume the reserved bytes ( reserved for header variables like string, int, bool ... )
        if ($binary->remain() > 0){
            $result['byteReserved'] = $binary->remain();
        }

        return $result;
    }

    private function parseSTAB( Binary $data ){
        $entries = [];
        do {

            $entry = [];

            /**
             * section 1 is 32 byte long
             *
             * - name terminated by \x00 (remaining data is garbage from the r* compiler)
             */
            $entry['name'] = $data->substr(0, 32, $data)->substr(0, "\x00", $nameGarbage)->toString();

            $entry['nameGarbage'] = $nameGarbage->toHex();

            /**
             * section 2 is 16 (MH1) or 20 (MH2) bytes long
             *
             * - 4-bytes defined at (byte offset from CODE) or ffffffff
             * - 4-bytes size
             * - 4-bytes hierarchie access type; 01000000 - header variable; 02000000 - header + script var; ffffffff - a global variable (MH2 only)
             * - 4-bytes Value Type (int,bool,float, string, tLevelState ....)
             * - 4-bytes Occurrence/Usage Count
             * - [4-bytes ... ] byte offset of the occurred call in CODE section
             */
            $section2 = $data->substr(0, $this->game == MHT::GAME_MANHUNT ? 16 : 20, $data)->split(4);

            $entry['offset'] = $section2[0]->toHex($this->platform == MHT::PLATFORM_WII);
            $entry['size']   = $section2[1]->toHex() == "ffffffff" ? 'ffffffff' : $section2[1]->toInt($this->platform == MHT::PLATFORM_WII);


            $valueType = $section2[$this->game == MHT::GAME_MANHUNT ? 2 : 3]->toHex($this->platform == MHT::PLATFORM_WII);
            $occurrenceCount = $section2[$this->game == MHT::GAME_MANHUNT ? 3 : 4]->toInt($this->platform == MHT::PLATFORM_WII);


            if ($this->game == MHT::GAME_MANHUNT_2){
                $entry['hierarchieType'] = $section2[2]->toHex($this->platform == MHT::PLATFORM_WII);
            }

            if ($this->game == MHT::GAME_MANHUNT){

                switch ($valueType) {
                    case "01000000";
                        $objectType = "boolean";
                        break;

                    case "02000000";
                        $objectType = "vec3d";
                        break;

                    default:
                        $objectType = $valueType;
//                        throw new \Exception(sprintf('Unknown object type sequence: %s', $valueType));
                    break;
                }
            }else{
                switch ($valueType){
                    case "00000000";
                        $objectType = "integer";
                        break;

                    case "01000000";
                        $objectType = "level_var boolean";
                        break;

                    case "02000000";
                        $objectType = "real";
                        break;

                    case "03000000";
                        $objectType = "boolean";
                        break;

                    case "04000000";
                        $objectType = "level_var integer";
                        break;

                    case "05000000";
                        $objectType = "string";
                        break;

                    case "06000000";
                        $objectType = "vec3d";
                        break;

                    case "07000000";
                        $objectType = "game_var integer";
                        break;

                    case "08000000";
                        $objectType = "state";
                        break;

                    default:
                        $objectType = $valueType;

                        //                    throw new \Exception(sprintf('Unknown object type sequence: %s', $valueType ));

                }
            }

            $entry['objectType'] = $objectType;
            $entry['occurrences'] = [];

            if ($occurrenceCount > 0){
                $occurrencesRaw = $data->substr(0, $occurrenceCount * 4, $data)->split(4);
                foreach ($occurrencesRaw as $occurrence) {
                    $entry['occurrences'][] = $occurrence->toInt();
                }
            }

            $entries[] = $entry;


        }while($data->length());

        return $entries;
    }

    private function parseMEM( Binary $data ){
        return $data->toInt($this->platform == MHT::PLATFORM_WII);
    }

    private function parseDBUG( Binary $data ){

        $dbug = [];

        list(,$srce) = $this->getLabelSizeData( $data, $data);
        $dbug['SRCE'] = $srce->toBinary();

        list(,$line) = $this->getLabelSizeData( $data, $data);
        $lines = $line->split(4);
        $dbug['LINE'] = [];
        foreach ($lines as $line) {
            $dbug['LINE'][] = $line->toHex();
        }

        list(,$trce) = $this->getLabelSizeData( $data, $data);
        $dbug['TRCE'] = [ $trce->toHex() ];

        return $dbug;
    }

}