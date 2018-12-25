<?php
namespace App\Service\Archive\Mls;


use App\Service\Binary;

class Extract {

    private $binary;

    private $game;
    private $platform;

    public function __construct( $binaryData, $game = "mh2")
    {
        $this->game = $game;
        $this->binary = new Binary( $binaryData );
    }

    public function get(){

        /** @var Binary $remain */
        // detect the current version, wii or pc/ps2/psp?
        $this->platform = $this->binary->substr(4, 4, $remain) == "00090003" ? "wii" : "pc";

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
        $size = $data->substr(0, 4, $data, $this->platform == "wii");

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

                case 'DMEM': break;
                case 'SCPT': $results['SCPT'] = $this->parseSCPT($data); break;
                case 'NAME': $results['NAME'] = $this->parseNAME($data); break;
                case 'ENTT': $results['ENTT'] = $this->parseENTT($data, $results['NAME']); break;
                case 'CODE': $results['CODE'] = $this->parseCODE($data); break;
                case 'SMEM': $results['SMEM'] = $this->parseSMEM($data); break;
                case 'DBUG': $results['SRCE'] = $this->parseDBUG($data); break;
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

            list($onTriggerOffset, $position) = $part->split(4, $this->platform == "wii");

            $result[] = [
                'name' => $name->toString(),
                'onTrigger' => $onTriggerOffset->toHex(),
                'scriptStart' => $position->toInt()
            ];
        }

        return $result;
    }

    private function parseNAME( Binary $data ){
        return $data->substr(0, "\x00", $data)->toString();
    }

    private function parseENTT( Binary $data, $levelName ){

        /** @var Binary $name */

        //skip the type
        $data->substr(0, 4, $name);

        return [
            'name' => $name->toString(),
            'type' => $levelName == "levelscript" ? "levelscript" : "other"
        ];

    }

    private function parseCODE( Binary $data ){

        $result = [];

        $split = $data->split(4, $this->platform == "wii");
        foreach ($split as $value) {
            $result[] = $value->toHex();
        }

        return $result;
    }

    private function parseDATA( Binary $data ){

        $rows = preg_split("/(?:00)(?:da)+/", $data->toHex());

        $result = [];
        foreach ($rows as $row) {
            if (!$row) continue;

            /*
             * I dont know. this is garbage, we can throw it away
             */
            if (substr($row, 0, 8) == "2c010000"){
                $result[] = hex2bin(substr($row, 8));

            }else{
                $result[] = hex2bin($row);

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
            $entry['name'] = $data->substr(0, 32, $data)->substr(0, "\x00")->toString();


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

            $section2 = $data->substr(0, $this->game == "mh1" ? 16 : 20, $data)->split(4);

            $entry['offset'] = $section2[0]->toHex($this->platform == "wii");
            $entry['size']   = $section2[1]->toHex() == "ffffffff" ? 'ffffffff' : $section2[1]->toInt($this->platform == "wii");


            $valueType = $section2[$this->game == "mh1" ? 2 : 3]->toHex($this->platform == "wii");
            $occurrenceCount = $section2[$this->game == "mh1" ? 3 : 4]->toInt($this->platform == "wii");


            if ($this->game == "mh2"){
                $entry['hierarchieType'] = $section2[2]->toHex($this->platform == "wii");
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
                    var_dump($entry['name']);
                    throw new \Exception(sprintf('Unknown object type sequence: %s', $valueType ));
                    break;

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

    private function parseSMEM( Binary $data ){
        return $data->toInt($this->platform == "wii");
    }

    private function parseDBUG( Binary $data ){

        list(,$sectionCode) = $this->getLabelSizeData( $data, $data);
        return $sectionCode->toBinary();
    }
}