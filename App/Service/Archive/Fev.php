<?php
namespace App\Service\Archive;

use App\Service\Archive\Inst\Build;
use App\Service\Archive\Inst\Extract;
use App\Service\Helper;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Fev extends Archive {

    public $name = 'FMOD Events';

    public static $validationMap = [
        [0, 4, NBinary::STRING, ['FEV1']]
    ];


    /**
     * @param $pathFilename
     * @param $input
     * @param $game
     * @param $platform
     * @return bool
     */
    public static function canPack( $pathFilename, $input, $game, $platform ){
        return false;
    }



    private function parseName(NBinary $binary){
        $size = $binary->consume(4, NBinary::INT_32);
        if ($size == 0) return false;
        return $binary->consume($size, NBinary::STRING);
    }

    private function parseFolders(NBinary $binary){
        $name = $this->parseName($binary);
        $hierarchy =  $binary->consume(4, NBinary::INT_32);

        $childs = [];

        if ($hierarchy === 1065353216){
            $unknownFolderSettings = $binary->consume(12, NBinary::INT_32);

            $folderCount = $binary->consume(4, NBinary::INT_32);

            for($i = 0; $i < $folderCount; $i++){
                $childs[] = $this->parseFolders($binary);
            }
        }

        return [
            'name' => $name,
            'childs' => $childs
        ];
    }



    private function parseEvents(NBinary $binary){
        $eventCount =  $binary->consume(4, NBinary::INT_32);

        $events = [];
        for($i = 0; $i < $eventCount; $i++){
            if ($i > 0){
                $folder = $this->parseName($binary);
            }


            $name = $this->parseName($binary);

            $unknownSettings = $binary->consume(8, NBinary::HEX);
            if ($unknownSettings != "0000000000000000"){
                echo "NOTE: Settings are not zero for " . $name . "\n";
            }

            $entriesInEvent =  $binary->consume(4, NBinary::INT_32);

            if ($entriesInEvent > 0){
                $events[$name][] = $this->parseEntry($binary);



                for($x = 1; $x < $entriesInEvent; $x++){


                    //always master
                    $eventName = $this->parseName($binary);

                    $events[$name][] = $this->parseEntry($binary);


                }

            }else{

                $events[$name] = [];

                //next one...
                $i++;
                $name = $this->parseName($binary);

                $unknownSettings = $binary->consume(8, NBinary::HEX);

                $entriesInEvent =  $binary->consume(4, NBinary::INT_32);
                if ($entriesInEvent > 0){
                    $events[$name][] = $this->parseEntry($binary);

                    for($x = 1; $x < $entriesInEvent; $x++){
                        $eventName = $this->parseName($binary);
                        $events[$eventName][] = $this->parseEntry($binary);
                    }
                }
            }


        }


        return $events;

    }


    private function parseHeader(NBinary $binary){

        $fourCC = $binary->consume(4, NBinary::STRING);

        $unknownSize = $binary->consume(4, NBinary::INT_32);

        $fevName = $this->parseName($binary);

        $hierarchy =  $binary->consume(4, NBinary::INT_32);
        if ($hierarchy !== 1) die('hierarchy is not 1 it is : ' . $hierarchy);

        $fevSettings =  $binary->consume(4, NBinary::HEX);
        if ($fevSettings !== "00020000") die('fevSettings is not 00020000 it is : ' .$fevSettings);

        $maxStreams =  $binary->consume(4, NBinary::INT_32);

        $fsbFileName = $this->parseName($binary);


    }


    private function parseEntryParam(NBinary $binary){
        $param = $this->parseName($binary);
//        var_dump($param);
        $unknown = $binary->consume(36, NBinary::HEX);

        return [
            'unknown' => $unknown
        ];
    }


    private function parseEntry(NBinary $binary){

        $audioName = $this->parseName($binary);
//var_dump($audioName);
        $separator = $binary->consume(4, NBinary::FLOAT_32);

//        if ($separator != 1) die("Parsing not Valid. separator1 is not 1");


        // maybe distance ?
        $floatPack = $binary->readXYZ();


        $someValue1 = $binary->consume(4, NBinary::INT_32);
        if ($someValue1 !== 128) die("someValue1 is not 128!");

        $someValue2 = $binary->consume(4, NBinary::INT_32);
//        var_dump($someValue2);
//        if ($someValue2 !== 2) die("someValue2 is not 2!");

        $someValue3 = $binary->consume(2, NBinary::INT_16);
//        if ($someValue3 !== 16) die("someValue3 is not 16!");

        $someValue4 = $binary->consume(2, NBinary::INT_16);
//        var_dump($someValue4);
//        if ($someValue4 !== 40) die("someValue4 is not 40!");

        $separator = $binary->consume(4, NBinary::FLOAT_32);
//        if ($separator != 1){
//            var_dump($someValue2, $someValue3, $someValue4);
//            die("Parsing not Valid. separator (4) is not 1");
//        }

        /**
         * value between 10 and 40
         */
        $dynamicValue1 = $binary->consume(4, NBinary::FLOAT_32);

        //00000000 OR 00000800
        $dynamicValue2 =  $binary->consume(4, NBinary::HEX);
//        if ($unknown2 !== 0) die('unknown2 is not 0 it is : ' .$unknown2);

        $unknown3 =  $binary->consume(84, NBinary::HEX);
//        if ($unknown3 !== "0000803f0000803f0000000000000000000000000000000000000000000000000000b4430000b4430000803f010000000000803f00000000000000000000000000000000000000000000803f0000803f00000000")
//            die("unknown3 has unknown settings");


        $bottomCount = $binary->consume(4, NBinary::INT_32);

        for($i = 0; $i < $bottomCount; $i++){

            $someCount = $binary->consume(2, NBinary::INT_16);

            $unknown = $binary->consume(4, NBinary::HEX);

            $someFlag = $binary->consume(2, NBinary::INT_16);
            $hasExtraData = $binary->consume(2, NBinary::INT_16);

            if ($someCount > 0){

                /**
                 * value from 0 up to 1663565876 (?)
                 */
                $dynamicValue = $binary->consume(4, NBinary::INT_32);

                //mainly 0
                $unknown = $binary->consume(2, NBinary::INT_16);

                $someDynamicFloat = $binary->consume(4, NBinary::FLOAT_32);

                $unknown = $binary->consume(20, NBinary::HEX);
//                if ($unknown != "0000000001000000ffffffff0000000000000000")
//                    die ("unknown is not valid...");


                $containsFloats = $binary->consume(16, NBinary::HEX);

                $separator = $binary->consume(4, NBinary::FLOAT_32);
                if ($separator != 1){
                    var_dump($audioName);
                    die("Parsing not Valid. separator (2) is not 1");
                }

                $unknown = $binary->consume(16, NBinary::HEX);
                if ($unknown !== "000080bf000080bf0200000002000000")
                    die("unknown has diff format");


                for($x = 0; $x < $hasExtraData; $x++){

                    $ff = $binary->consume(4, NBinary::HEX);
                    if ($ff !== "ffffffff") die("ff is not ff");


                    $unknown2 = $binary->consume(12, NBinary::HEX);
//                    if ($unknown2 !== "00000000000000000c000000") die("unknown2 is not 02");


                    $extraExtra = $binary->consume(2, NBinary::INT_16);

                    $unknown2 = $binary->consume(6, NBinary::HEX);
                    if ($unknown2 !== "000000000000") die("unknown2 is not 0");

                    $aFloat = $binary->consume(4, NBinary::FLOAT_32);


                    //some floats
                    $unknown2 = $binary->consume($extraExtra * 4 * 3, NBinary::HEX);
                }

                if ($someFlag > 1){

                    /**
                     * value from 0 up to 1663565876 (?)
                     */
                    $dynamicValue = $binary->consume(4, NBinary::INT_32);

                    //mainly 0
                    $unknown = $binary->consume(2, NBinary::INT_16);

                    $someDynamicFloat = $binary->consume(4, NBinary::FLOAT_32);

                    $unknown = $binary->consume(20, NBinary::HEX);
                    if ($unknown != "0000000001000000ffffffff0000000000000000")
                        die ("unknown is not valid...");


                    $containsFloats = $binary->consume(16, NBinary::HEX);

                    $separator = $binary->consume(4, NBinary::FLOAT_32);
                    if ($separator != 1){
                        var_dump($audioName);
                        die("Parsing not Valid. separator (3) is not 1");
                    }

                    $unknown = $binary->consume(16, NBinary::HEX);
                    if ($unknown !== "000080bf000080bf0200000002000000")
                        die("unknown has diff format");

                }
            }

        }

        $value = $binary->consume(4, NBinary::INT_32);
        if ($value !== 1) die("value is not 1");

        if ($value > 1){
            $unknown = $binary->consume($value * 4, NBinary::HEX);
            $value = $binary->consume(4, NBinary::FLOAT_32);
        }

        $params = $this->parseEntryParam($binary);

        return [
            'audioName' => $audioName,
            'params' => $params
        ];

    }




    private function parseNameRelationTable(NBinary $binary){
        $internalName = $this->parseName($binary);

        $count = $binary->consume(4, NBinary::INT_32);


        $blocks = [];
        for($i = 0; $i < $count; $i++){
            $blocks[] = $this->parseNameRelationBlock($binary);
        }


        return [
            'name' => $internalName,
            'blocks' => $blocks
        ];

    }

    private function parseNameRelationBlock(NBinary $binary){
        $internalName = $this->parseName($binary);

        $unknown = $binary->consume(60, NBinary::HEX);

        $count = $binary->consume(4, NBinary::INT_32);

        $unknown = $binary->consume(8, NBinary::HEX);

        $relations = [];
        for($i = 0; $i < $count; $i++){

            $relations[] = $this->parseNameRelation($binary, $i + 1 == $count);
        }

        return [
            'name' => $internalName,
            'relations' => $relations
        ];
    }

    private function parseNameRelation(NBinary $binary, $isLast){
        $wavName = $this->parseName($binary);
        $fsbName = $this->parseName($binary);

        $unknown = $binary->consume(4, NBinary::INT_32);
        $unknown2 = $binary->consume(4, NBinary::INT_32);
//        echo($unknown2 . "\t" .$wavName . "\n");

        if ($isLast == false){
            $unknown = $binary->consume(8, NBinary::HEX);
        }

        return [
            'wav' => $wavName,
            'fsb' => $fsbName,
        ];
    }


        /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     */
    public function unpack(NBinary $binary, $game, $platform){
echo "\n";
        $header = $this->parseHeader($binary);

        $folders = $this->parseFolders($binary);
        $events = $this->parseEvents($binary);
        $relations = $this->parseNameRelationTable($binary);

        var_dump($events);
        var_dump($relations);exit;
    }

    /**
     * @param Finder $pathFilename
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack( $pathFilename, $game, $platform){
    }




}
