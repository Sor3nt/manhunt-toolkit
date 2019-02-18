<?php
namespace App\Service\Archive;

use App\MHT;
use App\Service\NBinary;

class Glg extends Archive {
    public $name = 'Settings File';

    public static $supported = 'glg';

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

    public function unpack(NBinary $binary, $game, $platform){

        //it is already unzipped via NBinary
        return $binary->binary;
    }

    /**
     * @param $records
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack( $records, $game, $platform ){

        return false;
    }

    public function convertRecords( $text ){

        $result = [];

        preg_match_all('/RECORD\s(.*\s)*?END/mi', $text, $matches);
        foreach ($matches[0] as $match) {
            preg_match('/RECORD\s(.*)((.*\s*)*)END/i', $match, $entry);

            $options = [];
            $optionsRaw = explode("\n", $entry[2]);

            foreach ($optionsRaw as $singleOption) {
                $singleOption = trim($singleOption);
                if (empty($singleOption)) continue;

                if (strpos($singleOption, ' ') !== false){
                    preg_match('/(.*)\s(.*)/i', $singleOption, $singleOptionRow);

                    $options[] = [
                        'attr' => $singleOptionRow[1],
                        'value' => $singleOptionRow[2],
                    ];
                }else{
                    $options[] = [
                        'attr' => $singleOption
                    ];
                }


            }


            $result[ trim($entry[1]) ] = $options;

        }

        return $result;
    }


}