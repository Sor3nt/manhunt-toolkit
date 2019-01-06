<?php
namespace App\Service\Archive\Mls;


use App\Service\Helper;

class Build {

    /**
     * @param $scripts
     * @return string
     */
    public function build( $scripts){
        $mls =
            "MHLS" .
            "\x03\x00\x09\x00"        // MHLS Version (3.9)
        ;
        ksort($scripts);

        foreach ($scripts as $index => $records) {


            $scriptCode = $this->buildSCPT( $records );
            $scriptCode .= $this->buildNAME( $records );
            $scriptCode .= $this->buildENTT( $records );
            $scriptCode .= $this->buildCODE( $records );
            $scriptCode .= $this->buildDATA( $records );
            $scriptCode .= $this->buildSMEM( $records );
            $scriptCode .= $this->buildDebug( $records );
            $scriptCode .= $this->buildSTAB( $records );

            $mls .= $this->buildLabelSizeData("MHSC", $scriptCode);
        }

        return $mls;
    }

    private function buildSCPT( $records ){
        $code = "";

        foreach ($records['SCPT'] as $scptEntry) {

            // add the name - section is 64-byte long
            $code .= hex2bin(Helper::pad(current(unpack("H*", $scptEntry['name'])), 128));
            $code .= hex2bin($scptEntry['onTrigger']);
            $code .= hex2bin(Helper::fromIntToHex($scptEntry['scriptStart']));
        }

        return $this->buildLabelSizeData("SCPT", $code);
    }

    private function buildNAME( $records ){

        $data = current(unpack("H*", $records['NAME']));
        $length = strlen($data);
        $name = Helper::pad($data, $length + (4 - $length % 4 ));

        return $this->buildLabelSizeData("NAME", hex2bin($name));
    }

    private function buildENTT( $records ){

        $typeHex = "00000000";
        if ($records['ENTT']['type'] == "levelscript") $typeHex = "02000000";

        // ENTT Header
        $section = "ENTT";

        // add ENTT size (always 68 bytes)
        $section .= hex2bin(Helper::pad(dechex(68)));

        // add ENTT value
        $section .= hex2bin($typeHex);

        $section .= hex2bin(Helper::pad(current(unpack("H*", $records['ENTT']['name'])), 128));

        return $section;

    }

    private function buildCODE( $records ){
        return $this->buildLabelSizeData("CODE", hex2bin(implode("", $records['CODE'])));
    }

    private function buildDATA( $records ){

        if (!isset($records['DATA'])) return "";

        $stringArraySizes = 0;

        if (isset($records['STAB'])){

            foreach ($records['STAB'] as $item) {
                if ($item["size"] !== "ffffffff"){
                    $stringArraySizes += $item["size"];
                }else{
                    if (isset($item['occurrences']) && count($item['occurrences']) > 0) {
                        $stringArraySizes += 2 * count($item['occurrences']);
                    }else {
                        $stringArraySizes += 4;
                    }
                }
            }
        }

        $dataCode = "";

        foreach ($records['DATA'] as $name) {

            $name = current(unpack("H*", $name)) . "00";
            $nameLength = strlen($name);

            // add NAME size (its always / max 16)
            $dataCodeTmp = Helper::pad($name);
            $dataCode .= (Helper::pad($dataCodeTmp , $nameLength +  (8 - $nameLength % 8), false, 'da'));
        }

        $dataCode .= str_repeat('da', $stringArraySizes);

        $dataCodeLength = strlen($dataCode) ;

        $dataCode = Helper::pad($dataCode, $dataCodeLength +  (8 - $dataCodeLength % 8), false, 'da');

        if (substr($dataCode, -8) == "dadadada"){
            $dataCode = substr($dataCode, 0, -8);
        }

        return $this->buildLabelSizeData("DATA", hex2bin($dataCode));
    }

    private function buildSMEM( $records ){

        return $this->buildLabelSizeData("SMEM", pack("L", $records['SMEM']));
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

        return $this->buildLabelSizeData(
            'DBUG',
            $this->buildLabelSizeData('SRCE', hex2bin( current( unpack( "H*", $records['SRCE'] ) ) ) )
        );
    }

    /**
     * @param $records
     * @return string
     * @throws \Exception
     */
    private function buildSTAB( $records ){

        if (!isset($records['STAB'])) return "";

        $stabData = $records['STAB'];

        $stabCode = "";
        foreach ($stabData as $indexStab => $record) {

            // add name
            $stabCode .= hex2bin(Helper::pad(current(unpack("H*", $record['name'])) , 64)) ;

            // add offset
            $stabCode .= hex2bin( $record['offset'] );

            // add size
            $stabCode .= $record['size'] === "ffffffff" ? "\xff\xff\xff\xff" : hex2bin(Helper::fromIntToHex( $record['size']));

            if (isset($record['hierarchieType'])){
                $stabCode .= hex2bin($record['hierarchieType']);
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
//                case 'level_var tlevelstate':
                case 'tLevelState':
                    $stabCode .= "\x08\x00\x00\x00";
                    break;
//                case 'unknown 0a':
//                    $stabCode .= "\x0a\x00\x00\x00";
//                    break;
//                case 'unknown fe':
//                    $stabCode .= "\xfe\xff\xff\xff";
//                    break;
//                case 'unknown ff':
//                    $stabCode .= "\xff\xff\xff\xff";
//                    break;
                default:
                    $stabCode .= hex2bin($record['objectType']);
//                    throw new \Exception(sprintf('Unknown object type requested: %s', ($record['objectType']) ));
//                    break;

            }

            if (count($record['occurrences']) ){

                // add occurrence count
                $stabCode .= hex2bin(Helper::fromIntToHex( count($record['occurrences'])));

                // add occurrence position
                foreach ($record['occurrences'] as $occurrence) {
                    $stabCode .= hex2bin(Helper::fromIntToHex( $occurrence));
                }

            }else{
                // add empty occurrence
                $stabCode .= "\x00\x00\x00\x00";
            }

        }

        return $this->buildLabelSizeData('STAB', $stabCode);
    }

    private function buildLabelSizeData( $label, $data){
        return $label . pack("L", strlen( bin2hex($data) ) / 2) . $data;
    }
}
