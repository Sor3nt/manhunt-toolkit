<?php
namespace App\Service\Archive\Mls;


use App\MHT;
use App\Service\Helper;
use App\Service\NBinary;

class Build {

    /**
     * @param $scripts
     * @return string
     * @throws \Exception
     */
    public function build( $scripts , $game, $platform){
        $mls =
            "MHLS" .
            "\x03\x00\x09\x00"        // MHLS Version (3.9)
        ;

        //ksort($scripts);

        $levelScriptRecords = false;

        foreach ($scripts as $records) {

            if ($records['ENTT']['type'] == "levelscript"){
                $levelScriptRecords = $records;
                break;
            }
        }

        if ($levelScriptRecords === false){
            throw new \Exception('Levelscript not found ?!');
        }

        foreach ($scripts as $records) {

            $scriptCode = $this->buildSCPT( $records );
            $scriptCode .= $this->buildNAME( $records );
            $scriptCode .= $this->buildENTT( $records );
            $scriptCode .= $this->buildCODE( $records );
            $scriptCode .= $this->buildDATA( $records, $levelScriptRecords, $game );
            $scriptCode .= $this->buildSMEM( $records );
            $scriptCode .= $this->buildDebug( $records );
            $scriptCode .= $this->buildDMEM( $records );
            $scriptCode .= $this->buildSTAB( $records, $game );

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

        $name = bin2hex( $records['NAME']['name'] );

        if (isset($records['NAME']['nameGarbage'])){
            $name .= $records['NAME']['nameGarbage'];
        }else{
            $length = strlen($name) + (8 - strlen($name) % 8);
            $name = Helper::pad($name, $length);
        }

        return $this->buildLabelSizeData("NAME", hex2bin($name));
    }

    private function buildENTT( $records ){

        $typeHex = "00000000";
        if (
            $records['ENTT']['type'] == "levelscript"
        ) $typeHex = "02000000";

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
//        var_dump($records['CODE']);exit;
        return $this->buildLabelSizeData("CODE", hex2bin(implode("", $records['CODE'])));
    }

    private function buildDATA( $records, $levelScriptRecords, $game ){

        if (!isset($records['DATA'])) return "";

        $stringArraySizes = 0;
        if (isset($records['STAB'])){
            foreach ($records['STAB'] as $item) {

                // level_vars inside a script block are not calculated
                if (isset($item["isLevelVarFromScript"]) && $item["isLevelVarFromScript"] == true) continue;

                if ($item["size"] !== "ffffffff"){

                    $size = $item["size"] + ($item["size"] % 4);

                    $stringArraySizes += $size;
                }else{

                    // we need the size from the levelscript
                    foreach ($levelScriptRecords['STAB'] as $levelScriptStab) {
                        if ($levelScriptStab['name'] == $item["name"]){
                            if ($levelScriptStab["size"] !== "ffffffff"){
                                $size = $levelScriptStab["size"] + ($levelScriptStab["size"] % 4);
                                $stringArraySizes += $size;
                                break;
                            }

                        }
                    }
                }

            }
        }

        $dataCode = "";

        foreach ($records['DATA']['const'] as $const) {

            if (is_float($const)){
                $dataCode .= Helper::fromFloatToHex($const);
            }else{
                $dataCode .= Helper::fromIntToHex($const);
            }
        }

        foreach ($records['DATA']['strings'] as $name) {

            $nName = new NBinary();
            $oName = $name;

            $name = current(unpack("H*", $name)) . "00";
            $nameLength = strlen($name);

            // add NAME size (its always / max 16)
            if ($game == MHT::GAME_MANHUNT_2){
                $dataCodeTmp = Helper::pad($name, 8, false, 'da');
                $dataCode .= (Helper::pad($dataCodeTmp , $nameLength +  (8 - $nameLength % 8), false, 'da'));

            }else{

                $nName->write($oName . "\x00", NBinary::STRING);
                $nName->write($nName->getPadding(), NBinary::BINARY);
                $dataCode .= $nName->hex;
            }
        }


        /**
         * What the hack, i am not sure about this part, why they should just take "onlowsightingorabove" into account ?!
         * Whatever, this solve the size error on 3 script files
         */
        if (isset($records['SCPT'])){
            foreach ($records['SCPT'] as $scpt) {
                if ($scpt['name'] == "onlowsightingorabove"){
                    $stringArraySizes += 4;
                }
            }
        }

//
//        if ($records['DATA']['byteReserved'] != $stringArraySizes){
////            var_dump($records['STAB']);
//            var_dump($stringArraySizes);
//            var_dump($records['DATA']['byteReserved']);
//            var_dump($records['ENTT']['name']);
//        }

        if (isset($records['DATA']['byteReserved'])){
            $dataCode .= str_repeat('da', $records['DATA']['byteReserved'] );
        }else{
            $dataCode .= str_repeat('da', $stringArraySizes );
        }


        return $this->buildLabelSizeData("DATA", hex2bin($dataCode));
    }

    private function buildSMEM( $records ){

        return $this->buildLabelSizeData("SMEM", pack("L", $records['SMEM']));
    }

    private function buildDMEM( $records ){

        return $this->buildLabelSizeData("DMEM", pack("L", $records['DMEM']));
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

        $data = $this->buildLabelSizeData('SRCE', hex2bin( current( unpack( "H*", $records['SRCE'] ) ) ) );

        if (isset($records['LINE']) && count($records['LINE']))
            $data .= $this->buildLabelSizeData('LINE', hex2bin( implode('', $records['LINE'])) );
//        else
//            $data .= $this->buildLabelSizeData('LINE', hex2bin( implode('', [])) );

        if (isset($records['TRCE']))
            $data .= $this->buildLabelSizeData('TRCE', hex2bin( implode('', $records['TRCE'])) );

        return $this->buildLabelSizeData(
            'DBUG', $data
        );
    }

    /**
     * @param $records
     * @return string
     * @throws \Exception
     */
    private function buildSTAB( $records, $game ){

        if (!isset($records['STAB']) || count($records['STAB']) == 0) return "";

        $stabData = $records['STAB'];

        $stabCode = "";
        foreach ($stabData as $indexStab => $record) {

            // add name
            if (isset($record['nameGarbage'])){
                $stabCode .= hex2bin(current(unpack("H*", $record['name'])));
                $stabCode .= hex2bin( $record['nameGarbage'] );
            }else{
                $stabCode .= hex2bin(Helper::pad(current(unpack("H*", $record['name'])) , 64)) ;
            }

            // add offset
            $stabCode .= hex2bin( $record['offset'] );

            // add size
            $stabCode .= $record['size'] === "ffffffff" ? "\xff\xff\xff\xff" : hex2bin(Helper::fromIntToHex( $record['size']));

            if (isset($record['hierarchieType'])){
                $stabCode .= hex2bin($record['hierarchieType']);
            }

            if ($game == MHT::GAME_MANHUNT){

                switch ($record['objectType']){
//
                    case 'boolean':
                        $stabCode .= "\x01\x00\x00\x00";
                        break;
                    case 'vec3d':
                        $stabCode .= "\x02\x00\x00\x00";
                        break;
//
                    default:
                        $stabCode .= hex2bin($record['objectType']);
//
                }
            }else{

                switch ($record['objectType']){

                    case 'matrixptr':
                    case 'effectptr':
                    case 'integer':
                        $stabCode .= "\x00\x00\x00\x00";
                        break;
                    case 'level_var boolean':
                        $stabCode .= "\x01\x00\x00\x00";
                        break;
                    case 'real':
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
                    case 'state':
                        $stabCode .= "\x08\x00\x00\x00";
                        break;
                    default:
                        $stabCode .= hex2bin($record['objectType']);
    //                    throw new \Exception(sprintf('Unknown object type requested: %s', ($record['objectType']) ));
    //                    break;

                }
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
