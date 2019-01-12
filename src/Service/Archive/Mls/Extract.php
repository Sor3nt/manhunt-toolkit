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


        $this->platform = $version == "00090003" ? MHT::PLATFORM_WII : MHT::PLATFORM_PC;


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
                    break;
                case 'DATA': $results['DATA'] = $this->parseDATA($data); break;
                case 'STAB': $results['STAB'] = $this->parseSTAB($data); break;

                default:
                    throw new \Exception(sprintf('Unknown Script Section %s', $scriptLabel ));
                    break;
            }

        }while($remain->length() > 0);

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

        //skip the type
        $data->substr(0, 4, $name);

        return [
            'name' => $name->toString(),
            'type' => $levelName['name'] == "levelscript" ? "levelscript" : "other"
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

    private function parseDATA( Binary $data ){

        $rows = preg_split("/(?:00)(?:da)+/", $data->toHex());

        $result = [
            'const' => [],
            'strings' => []
        ];
        foreach ($rows as $row) {
            if (!$row) continue;

            while(substr($row, 4, 4) == "0000"){
                $result['const'][] = Helper::fromHexToInt(substr($row, 0, 8));
                $row = substr($row, 8);
            }

            if (strlen($row) > 0){
                $result['strings'][] = hex2bin($row);
            }
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
             * - 4-bytes hierarchie access type; 01000000 - header variable; 02000000 - header + script var; ffffffff - a global variable
             * - 4-bytes Value Type (int,bool,float, string, tLevelState ....)
             * - 4-bytes Occurrence/Usage Count
             * - [4-bytes ... ] byte offset of the occurred call in CODE section (MH2 only)
             */
            $section2 = $data->substr(0, $this->game == MHT::GAME_MANHUNT ? 16 : 20, $data)->split(4);

            $entry['offset'] = $section2[0]->toHex($this->platform == MHT::PLATFORM_WII);
            $entry['size']   = $section2[1]->toHex() == "ffffffff" ? 'ffffffff' : $section2[1]->toInt($this->platform == MHT::PLATFORM_WII);


            $valueType = $section2[$this->game == MHT::GAME_MANHUNT ? 2 : 3]->toHex($this->platform == MHT::PLATFORM_WII);
            $occurrenceCount = $section2[$this->game == MHT::GAME_MANHUNT ? 3 : 4]->toInt($this->platform == MHT::PLATFORM_WII);


            if ($this->game == MHT::GAME_MANHUNT_2){
                $entry['hierarchieType'] = $section2[2]->toHex($this->platform == MHT::PLATFORM_WII);
            }

            switch ($valueType){
                case "00000000";
                    $objectType = "integer";
                    break;

                case "01000000";
                    $objectType = "level_var boolean";
                    break;

                case "02000000";
                    $objectType = "game_var real";
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
                    $objectType = "tLevelState";
                    break;

//                case "0a000000";
//                    $objectType = "unknown 0a";
//                    break;

//
//                case "feffffff";
//                    $objectType = "unknown fe";
//                    break;
//
//                case "ffffffff";
//                    $objectType = "unknown ff";
//                    break;
//
//                case "50bf2b02";
//                    $objectType = "unknown 50bf2b02";
//                    break;
//
//                case "20536372";
//                    $objectType = "unknown 20536372";
//                    break;


                default:
                    $objectType = $valueType;
//                    var_dump($entry['name']);
//                    throw new \Exception(sprintf('Unknown object type sequence: %s', $valueType ));
//                    break;

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