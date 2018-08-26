<?php
namespace App\Service\Archive;

use App\Service\Binary;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class Mls extends ZLib {


    private $loopIndex = 0;

    /** @var array  */
    public $header = [
        'start' => '5A32484D',      // the mls header
        'separator' => '78DA'       // zlib compress factor (best)
    ];


    private function getLabelSizeData( Binary $data, Binary &$remain = null, $labelEndPos = 4){

        $label = $data->substr(0, $labelEndPos, $data);
        $size = $data->substr(0, $labelEndPos, $data);
        $data = $data->substr(0, $size->toInt(), $remain);

        return [
            $label->toString(),
            $size->toInt(),
            $data
        ];
    }


    /**
     * @param $data
     * @param string $game
     * @return array
     */
    public function unpack($data, $game = "mh1", OutputInterface $output = null){


        /** @var Binary $remain */
        $binary = new Binary( $data );

        /**
         * Parse file header (MHLS)
         */
        $mhlsHeader = $binary->substr(0, 4, $remain);

        !is_null($output) && $output->writeln(' ' . str_repeat('_', 20));
        !is_null($output) && $output->writeln(sprintf('| <info>Header:</info> %s', $mhlsHeader->toString()));

        $mhlsVersion = $remain->substr(0, 4, $remain);

        $version = (int) $mhlsVersion->substr(0,1)->toHex() . '.';
        $version .= (int) $mhlsVersion->substr(2,1)->toHex();

        !is_null($output) && $output->writeln(sprintf("| <info>Version:</info> %s", $version));

        !is_null($output) && $output->writeln(' ' . str_repeat('¯', 20));

        $nextSection = $remain->substr(0, 4)->toString();

        $mhscs = [];

        $progressBar = null;
        if (!is_null($output)){
            $progressBar = new ProgressBar($output);
            $progressBar->start();
        }

        // there is only one script inside the MLS
        if ($nextSection === "SCPT"){
            $mhscs[] = $this->parseBody($remain, $game, $output, $progressBar);

            // there is multiple scripts inside the MLS
        }else if ($nextSection === "MHSC"){
            do{
                !is_null($output) && $output->writeln("");
                !is_null($output) && $output->writeln("Progress next script...");
                !is_null($output) && $output->writeln("");

                list(,, $data) = $this->getLabelSizeData( $remain, $remain);

                $mhscs[] = $this->parseBody($data, $game, $output, $progressBar);

            }while($remain->length() > 0);
        }

        !is_null($progressBar) && $progressBar->finish();


        !is_null($output) && $output->writeln(sprintf(" == <comment>%s</comment> Scripts extracted", count($mhscs)));


        return $mhscs;

    }

    private function parseBody( Binary $remain, $game = "mh1", OutputInterface $output = null, ProgressBar $progressBar = null ){
        /** @var Binary $code */
        /** @var Binary $sectionCode */
        /** @var Binary $data */

        $unpacked = [];

        do{
            list($scriptLabel, , $data) = $this->getLabelSizeData( $remain, $remain);

            !is_null($progressBar) && $progressBar->advance();

            $unpacked[$scriptLabel] = [];

            !is_null($output) && $output->write(sprintf(" > <info>%s</info> ... ", $scriptLabel));

            switch($scriptLabel){

                /**
                 *
                 * hmmm the TRCE section is missed, i do not know how its skipped -.-
                 *
                 */


                case 'SCPT':
                    /** @var Binary $data */
                    $scptParts = $data->split(72);

                    foreach ($scptParts as $index =>  $part) {

                        $name = $part->substr(0, 64, $part);

                        /**
                         * first entry has always int 0
                         * and all other has 104
                         */
                        $priority = $part->substr(0, 4, $part);

                        $position = $part->substr(0, 4, $part);

                        $unpacked[$scriptLabel][] = [
                            'name' => $name->toString(),
                            'onTrigger' => $priority->toHex(),
                            'scriptStart' => $position->toInt()
                        ];
                    }

                    !is_null($output) && $output->writeln(sprintf("<comment>%s entries</comment>", count($scptParts)));

                    break;

                case 'NAME':                                    // just the name of this script

                    $unpacked['NAME'] = $data->substr(0, "\x00", $data)->toString();

                    // contains garbage
//                    $unpacked['NAME_remain'] = $data->toHex();

                    !is_null($output) && $output->writeln(sprintf("<comment>%s</comment>", $unpacked['NAME']));

                    break;

                case 'ENTT':                                    // entity name

                    /** @var Binary $name */
                    $type = $data->substr(0, 4, $name);


                    $typeName = "other";
                    if ($unpacked['NAME'] == "levelscript") {
                        $typeName = "levelscript";
                    }

                    $unpacked['ENTT'] = [
                        'name' => $name->toString(),
                        'type' => $typeName
                    ];

                    !is_null($output) && $output->writeln(sprintf("<comment>%s</comment>", $name->toString()));

                    break;

                case 'CODE':                                    // bytecode -> https://docs.google.com/spreadsheets/d/1HgZ_K9Yp-KobflyKdVqZiF8aF819AGHbOg_b1YUbfz0/edit?usp=sharing


                    $unpacked['CODE'] = [];

                    $split = $data->split(4);
                    foreach ($split as $value) {
                        $unpacked['CODE'][] = $value->toHex();
                    }

                    !is_null($output) && $output->writeln(sprintf("<comment>%s entries</comment>", count($unpacked['CODE'])));

                    break;

                case 'DATA':                                    // string name storage
                    $code = $data;

                    //keep until mh1 is implemented
                    $unpacked['DATARAW'] = $data->toHex();



                    /*
                     * No fixed space ? we need to search and grab the stuff
                     */

                    do{
                        $name = $code->substr(0, "\x00", $code);


                        while($code->substr(0,1)->toBinary() == "\xBC" || $code->substr(0,1)->toBinary() == "\x20" || $code->substr(0,1)->toBinary() == "\xDA" || $code->substr(0,1)->toBinary() == "\x00"){
                            $code = $code->substr(1);
                        }


                        $unpacked['DATA'][] = $name->toString();


                    }while($code->length() > 0);

                    /**
                     * Note:
                     * if we have string variables declared as array, the length of them will be converted into DA and appended to the block end
                     */

                    !is_null($output) && $output->writeln(sprintf("<comment>%s entries</comment>", count($unpacked['DATA'])));

                    break;

                case 'SMEM':                                    // hm
                    $unpacked['SMEM'] = $data->toInt();
                    !is_null($output) && $output->writeln(sprintf("<comment>%s Byte</comment>", $unpacked['SMEM']));

                    break;

                case 'DBUG':                                    // source code, match 1:1 the bytecode
                    $code = $data;

                    list(, , $sectionCode) = $this->getLabelSizeData( $code, $code);#
                    $unpacked['DBUG']['SRCE'] = $sectionCode->toBinary();

                    list(, , $lineCode) = $this->getLabelSizeData( $code, $code);

                    $trce = $code->split(4);

                    // add TRCE record
                    $unpacked['DBUG']['TRCE'] = [
                        'size' => $trce[1]->toHex(),
                        'data' => $trce[2]->toHex()
                    ];

                    $unpacked['DBUG']['LINE'] = [];


                    //umstellen auf ->split(4)
                    do{

                        $unpacked['DBUG']['LINE'][] = $lineCode->substr(0, 4, $lineCode)->toHex();
                    }while($lineCode->length() > 0);


                    !is_null($output) && $output->writeln("<comment>ok</comment>");

                    break;

                case 'DMEM':                                    // memory allocation for debug
                    $unpacked['DMEM'] = $data->toInt();
                    !is_null($output) && $output->writeln(sprintf("<comment>%s Byte</comment>", $unpacked['DMEM']));

                    break;

                case 'STAB':                                    // data like : acellhaschanged aiinited blockertutdisplayed bmeleetutdone....
                    $code = $data;

                    $unpacked['STAB_RAW'] = $code->toBinary();

                    $entries = [];
                    do {

                        $entry = [];

                        /**
                         * section 1 is 32 byte long
                         *
                         * - name (dynamic length) terminated by \x00
                         * - unknown data
                         *
                         * section 2 is 20 byte long + extra bytes ( 4-byte blocks )
                         *
                         * - 4-bytes defined at (byte offset from CODE) or FF FF FF FF
                         * - 4-bytes Length
                         * - 4-bytes ???         wenn Occurrence vorhanden sind is das immer FFFF
                         * - 4-bytes Value Type (int,bool,float, string, tLevelState ....)
                         * - 4-bytes Occurrence Count
                         * - [4-bytes ... ] byte offset of the occured call in CODE section (MH2 only)
                         *
                         *
                         * the name "me" has always the same setup, except the 4-byte defined for the offset
                         * Todo: über alle levels hinweg checken
                         */

                        /** @var Binary $section2 */

                        $section1 = $code->substr(0, 32, $code);
                        $section2 = $code->substr(0, $game == "mh1" ? 16 : 20, $code);

                        $unknown1 = new Binary();
                        $name = $section1->substr(0, "\x00", $unknown1);
                        $entry['name'] = $name->toString();

                        /** @var Binary $unknown1 */
                        $unknown1 = $unknown1->skipBytes(1);


                        $section2 = $section2->split(4);

                        /** @var Binary[] $section2 */

                        if ($section2[0]->toHex() == "ffffffff"){
                            $entry['offset'] = false;
                        }else{
                            $entry['offset'] = $section2[0]->toHex();
                        }

                        if ($section2[1]->toHex() == "ffffffff"){
                            $entry['size'] = false;
                        }else{
                            $entry['size'] = $section2[1]->toInt();
                        }


                        if ($game == "mh1"){
                            $entry['valueType'] = $section2[2]->toHex();
                            $entry['occurrenceCount'] = $section2[3]->toInt();

                        }else{

                            $entry['unknownType'] = $section2[2]->toHex();
                            $entry['valueType'] = $section2[3]->toHex();
                            $entry['occurrenceCount'] = $section2[4]->toInt();
                        }



                        switch ($entry['valueType']){


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


                            case "0a000000";
                                $objectType = "unknown 0a";
                                break;


                            case "feffffff";
                                $objectType = "unknown fe";
                                break;

                            case "ffffffff";
                                $objectType = "unknown ff";
                                break;


                            case "50bf2b02";
                                $objectType = "unknown 50bf2b02";
                                break;

                            case "20536372";
                                $objectType = "unknown 20536372";
                                break;


                            case "08000000";
                                $objectType = "tLevelState";
                                break;

                            default:
                                var_dump($name->toBinary());
                                throw new \Exception(sprintf('Unknown object type sequence: %s', $entry['valueType'] ));
                                break;

                        }

                        $entry['objectType'] = $objectType;


                        $entry['occurrences'] = [];


                        if ($entry['occurrenceCount'] > 0){
                            $occurrencesRaw = $code->substr(0, $entry['occurrenceCount'] * 4, $code)->split(4);
                            foreach ($occurrencesRaw as $occurrence) {
                                $entry['occurrences'][] = $occurrence->toInt();
                            }
                        }




                        //these are the values inside the first 32-byte name section, appear right after the name -- looks like garbage
                        if ($unknown1->toString() != ""){
                            $entry['unknown'] = "";

                            $split = $unknown1->split(4);
                            foreach ($split as $item) {
                                $entry['unknown'] .= $item->toHex();
                            }

                        }

                        unset($entry['valueType']);
                        unset($entry['occurrenceCount']);

                        $entries[] = $entry;


                    }while($code->length());

                    $unpacked['STAB'] = $entries;


                    !is_null($output) && $output->writeln(sprintf("<comment>%s entries</comment>", count($entries)));

                    break;

                default:
                    throw new \Exception(sprintf('Unknown Script Section %s', $scriptLabel ));
                    break;
            }



        }while($remain->length() > 0);

        $this->loopIndex++;

        return $unpacked;
    }


    private function buildSCPT( $records ){
        if (is_string($records['SCPT'])){
            $scptEntries = \json_decode($records['SCPT'], true);

        }else{

            $scptEntries = $records['SCPT'];
        }

        $code = "";

        foreach ($scptEntries as $scptEntry) {

            // add the name - section is 32-byte long
            $code .= hex2bin($this->pad(current(unpack("H*", $scptEntry['name'])), 64 * 2));

            $code .= hex2bin($scptEntry['onTrigger']);
            $code .= hex2bin($this->fromIntToHex($scptEntry['scriptStart']));
        }


        // SCPT Header
        $section = "\x53\x43\x50\x54";

        // add SCPT  size
        $section .= (pack("L", strlen(bin2hex($code)) / 2));

        // add SCPT section
        $section .= $code;

        return $section;
    }

    private function buildNAME( $records ){

        $data = current(unpack("H*", $records['NAME']));
        $length = strlen($data);
        $name = $this->pad($data, $length + (4 - $length % 4 ));

        // NAME Header
        $section = "\x4E\x41\x4D\x45";

        $section .= (pack("L", strlen($name) / 2));

        $section .= hex2bin($name) ;

        return $section;
    }

    private function buildENTT( $records ){


        if (is_string($records['ENTT'])){
            $records['ENTT'] = \json_decode($records['ENTT'], true);
        }

        $typeHex = "00000000";
        if ($records['ENTT']['type'] == "levelscript") $typeHex = "02000000";

        // ENTT Header
        $section = "\x45\x4E\x54\x54";

        // add ENTT size (always 68 bytes)
        $section .= hex2bin($this->pad(dechex(68)));

        // add ENTT value
        $section .= hex2bin($typeHex);

        $section .= hex2bin($this->pad(current(unpack("H*", $records['ENTT']['name'])), 64 * 2));

        return $section;

    }

    private function buildCODE( $records ){
        if (!is_array($records['CODE'])) $records['CODE'] = explode("\n", $records['CODE']);

        $codeData = implode("", $records['CODE']);

        // CODE Header
        $section = "\x43\x4F\x44\x45";

        // add CODE  size
        $section .= (pack("L", strlen($codeData) / 2));

        // add code value
        $section .= hex2bin($codeData);

        return $section;
    }

    private function buildDATA( $records ){

        if (!is_array($records['DATA'])) $records['DATA'] = explode("\n", $records['DATA']);

        if (isset($records['DATA'])){

            $stringArraySizes = 0;

            if (isset($records['STAB'])){
                if (is_string($records['STAB'])){
                    $stab = \json_decode($records['STAB'], true);
                }else{
                    $stab = $records['STAB'];
                }

                foreach ($stab as $item) {
                    if ($item["size"] !== false){
                        $stringArraySizes += $item["size"];
                    }else{
                        if (isset($item['occurrences']) && count($item['occurrences']) > 0) {
                            // i am not sure about this part, we missed bytes, this solves it...
                            $stringArraySizes += 2 * count($item['occurrences']);
                        }else {
                            $stringArraySizes += 4;
                        }
                    }
                }
            }

            $dataCode = "";

            foreach ($records['DATA'] as $name) {

                $name = current(unpack("H*", $name));
                $name .= "00";
                $nameLength = strlen($name);

                // add NAME size (its always / max 16)
                $dataCodeTmp = $this->pad($name);
                $dataCode .= ($this->pad($dataCodeTmp , $nameLength +  (8 - $nameLength % 8), false, 'da'));
            }

            $dataCode .= str_repeat('da', $stringArraySizes);

            $dataCodeLength = strlen($dataCode) ;

            $dataCode = $this->pad($dataCode, $dataCodeLength +  (8 - $dataCodeLength % 8), false, 'da');

            if (substr($dataCode, -8) == "dadadada"){
                $dataCode = substr($dataCode, 0, -8);
            }


            /**
             * NOTE: it did not match 100% but the recompiled data works fine
             */

//            $dataCode = $records['DATARAW'];
//
//
            // DATA Header
            $scriptCode = "\x44\x41\x54\x41";

            // add DATA size
            $scriptCode .= (pack("L", strlen($dataCode) / 2));

            // add DATA value
            $scriptCode .= hex2bin($dataCode);

            return $scriptCode;
        }

        return "";

    }

    private function buildSMEM( $records ){

        // SMEM Header
        $section = "\x53\x4D\x45\x4D";

        // add SMEM size (always 4 bytes)
        $section .= "\x04\x00\x00\x00";

        // add SMEM value
        $section .= (pack("L", $records['SMEM']));

        return $section;
    }


    /**
     * This section contains the plain source code and some IDE related parameters
     * To bad that we dont have a Manhunt2.exe that loads the DBUG section.
     *
     * Currently its a useless part, we can not access this from the game, only read and learn.
     *
     * @param $records
     * @return string
     */
    private function buildDebug( $records ){

        /*
         * Build DBUG sub section SRCE
         */

        // add SRCE size
        $data = current(unpack("H*", $records['SRCE']));

        // SRCE header
        $srceCode = "\x53\x52\x43\x45";

        $srceCode .= (pack("L", strlen($data) / 2));

        // add SRCE value
        $srceCode .= hex2bin($data);

        /*
         * Build DBUG sub section LINE
         */

        $lineData = implode("", explode("\n", $records['LINE']));

        // LINE header
        $lineCode = "\x4C\x49\x4E\x45";

        // add LINE size
        $lineCode .= (pack("L", strlen($lineData) / 2));

        // add LINE value
        $lineCode .= hex2bin($lineData);



        // TRCE header (TRCE \x04 \x00)
        $trceCode = "\x54\x52\x43\x45\x04\x00\x00\x00\x00\x00\x00\x00";

        // DBUG Header
        $section = "\x44\x42\x55\x47";

        // DBUG Size

        $section .= (pack("L", strlen(bin2hex($srceCode . $lineCode . $trceCode )) / 2));

        // DBUG value
        $section .= $srceCode . $lineCode . $trceCode;

        /*********************
         *
         * Build DMEM section
         *
         *********************/
        //
        // DMEM Header
        $section .= "\x44\x4D\x45\x4D";

        // add DMEM size (always 4 bytes)
        $section .= "\x04\x00\x00\x00";

        // add DMEM value
        $section .= (pack("L", $records['DMEM']));

        return $section;
    }

    private function buildSTAB( $records ){

        if (isset($records['STAB'])){

            if (is_string($records['STAB'])){
                $stabData = \json_decode($records['STAB'], true);
            }else{
                $stabData = $records['STAB'];
            }

            $stabCode = "";
            foreach ($stabData as $indexStab => $record) {


                // add name
                $name = current(unpack("H*", $record['name']));

                //REMOVE this IF we dont need this, just added for debugging
                if (isset($record['unknown'])){
                    $stabCode .= hex2bin($name . '00'. $record['unknown']);
                }else{
                    $stabCode .= hex2bin($this->pad($name , 32 * 2)) ;
                }


                // add offset
                if ($record['offset'] === false){
                    $stabCode .= "\xff\xff\xff\xff";
                }else{
                    $stabCode .= hex2bin( $record['offset'] );
                }

                // add size
                if ($record['size'] === false){
                    $stabCode .= "\xff\xff\xff\xff";
                }else{
                    $stabCode .= hex2bin($this->fromIntToHex( $record['size']));
                }


                if (isset($record['unknownType'])){
                    // add type2 ( still unknown )
                    $stabCode .= hex2bin($record['unknownType']);

                }

                switch ($record['objectType']){

                    case 'integer':
                        $stabCode .= "\x00\x00\x00\x00";
                        break;
                    case 'level_var boolean':
                        $stabCode .= "\x01\x00\x00\x00";
                        break;
                    case 'game_var real':
                        $stabCode .= "\x02\x00\x00\x00";
                        break;
                    case 'boolean':
                        $stabCode .= "\x03\x00\x00\x00";
                        break;
                    case 'level_var integer':
                        $stabCode .= "\x04\x00\x00\x00";
                        break;
                    case 'string':
                        $stabCode .= "\x05\x00\x00\x00";
                        break;
                    case 'vec3d':
                        $stabCode .= "\x06\x00\x00\x00";
                        break;
                    case 'game_var integer':
                        $stabCode .= "\x07\x00\x00\x00";
                        break;
                    case 'level_var tlevelstate':
                    case 'tLevelState':
                        $stabCode .= "\x08\x00\x00\x00";
                        break;
                    case 'unknown 0a':
                        $stabCode .= "\x0a\x00\x00\x00";
                        break;
                    case 'unknown fe':
                        $stabCode .= "\xfe\xff\xff\xff";
                        break;
                    case 'unknown ff':
                        $stabCode .= "\xff\xff\xff\xff";
                        break;
                    default:
//                        var_dump($record);

                        throw new \Exception(sprintf('Unknown object type requested: %s', ($record['objectType']) ));
                        break;

                }

                if (count($record['occurrences']) ){

                    // add occurrence count
                    $stabCode .= hex2bin($this->fromIntToHex( count($record['occurrences'])));

                    // add occurrence position
                    foreach ($record['occurrences'] as $occurrence) {
                        $stabCode .= hex2bin($this->fromIntToHex( $occurrence));
                    }

                }else{
                    // add empty occurrence
                    $stabCode .= "\x00\x00\x00\x00";
                }

            }

            // STAB Header
            $scriptCode = "\x53\x54\x41\x42";


            // add STAB size
            $scriptCode .= (pack("L", strlen(bin2hex($stabCode )) / 2));

            // add STAB value
            $scriptCode .= $stabCode;

            return $scriptCode;
        }

        return "";
    }



    public function pack( $scripts, $game = "mh1", $withDebug = true, OutputInterface $output = null){
        $mls =
            "\x4D\x48\x4C\x53" .      // MHLS
            "\x03\x00\x09\x00"        // MHLS Version (3.9)
        ;

        ksort($scripts);


        $progressBar = null;
        if (!is_null($output)){
            $progressBar = new ProgressBar($output);
            $progressBar->start(count($scripts));
        }

        foreach ($scripts as $index => $records) {

            !is_null($output) && $output->writeln(sprintf(" > <comment>%s</comment>", $records['NAME']));

            $scriptCode = $this->buildSCPT( $records );
            $scriptCode .= $this->buildNAME( $records );
            $scriptCode .= $this->buildENTT( $records );
            $scriptCode .= $this->buildCODE( $records );
            $scriptCode .= $this->buildDATA( $records );
            $scriptCode .= $this->buildSMEM( $records );

            if ($withDebug){
                $scriptCode .= $this->buildDebug( $records );
            }

            $scriptCode .= $this->buildSTAB( $records );

            /**
             * When we pack multiple scripts, always add again the MHSC header
             */
//            if (count($scripts) > 1){
                // MHSC header
                $header = "\x4D\x48\x53\x43";

                // add MSHC size
                $header .= (pack("L", strlen(bin2hex($scriptCode )) / 2));

                $mls .= $header . $scriptCode;

//            }else{
//                $mls .= $scriptCode;
//            }

            !is_null($progressBar) && $progressBar->advance();

        }

        return $mls;
    }



}