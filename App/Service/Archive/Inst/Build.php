<?php
namespace App\Service\Archive\Inst;

use App\MHT;
use App\Service\Archive\Inst;
use App\Service\CompilerV2\Manhunt2;
use App\Service\Helper;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use function json_decode;

class Build {

    public function build( Finder $pathFilename, $platform ){

        // append record count
        $binary = new NBinary();

        if ($platform == MHT::PLATFORM_WII) $binary->numericBigEndian = true;

        $binary->write($pathFilename->count(), NBinary::INT_32);

        $pathFilename->sort(function(SplFileInfo $a, SplFileInfo $b){
            return (int)$a->getFilename() > (int)$b->getFilename();
        });

        $recordBin = [];
        foreach ($pathFilename as $file) {

            $record = json_decode($file->getContents(), true);

            /*
             * Append GlgRecord name
             */
            $entry = new NBinary($record['record']);
            $entry->numericBigEndian = $binary->numericBigEndian;

            $entry->write("\x00", NBinary::BINARY);
            $entry->write($entry->getPadding( "\x70"), NBinary::BINARY);

            /*
             * Append Internal name
             */
            $entry->write($record['internalName'], NBinary::BINARY);
            $entry->write("\x00", NBinary::BINARY);
            $entry->write($entry->getPadding("\x70"), NBinary::BINARY);

            $entry->writeCoordinates($record['position']);
            $entry->writeCoordinates($record['rotation']);

            /*
             * Append entity class
             */
            if ($record['entityClass']){
                $entry->write($record['entityClass'], NBinary::BINARY);
                $entry->write("\x00", NBinary::BINARY);
                $entry->write($entry->getPadding("\x70"), NBinary::BINARY);
            }


            /*
             * Append parameters
             */
            foreach ($record['parameters'] as $parameter) {


                if (!isset($record['game']) && isset($parameter['parameterId'])){
                    $record['game'] = MHT::GAME_MANHUNT_2;
                }else if (!isset($record['game'])){
                    $record['game'] = MHT::GAME_MANHUNT;
                }

                if ($record['game'] == MHT::GAME_MANHUNT_2 ){

                    $parameterName = $parameter['parameterId'];
                    $parameter['parameterId'] = Manhunt2::getHashByName($parameter['parameterId']);

                    if ($binary->numericBigEndian) $parameter['parameterId'] = Helper::toBigEndian($parameter['parameterId']);

                    $entry->write($parameter['parameterId'], NBinary::HEX);

                    $entry->write($parameter['type'], NBinary::BINARY);
                    $entry->write("\x00", NBinary::BINARY);
                    $entry->write($entry->getPadding("\x70"), NBinary::BINARY);

                    switch ($parameter['type']) {
                        case 'flo':
                            $entry->write($parameter['value'], NBinary::FLOAT_32);
                            break;

                        case 'boo':
                        case 'int':
                            if($parameterName == "WEAPON") {
                                $parameter['value'] = Inst::$weapon[strtolower($parameter['value'])];
                            }else if($parameterName == "WEAPON2"){
                                $parameter['value'] = Inst::$weapon2[strtolower($parameter['value'])];
                            }

                            $entry->write($parameter['value'], NBinary::INT_32);
                            break;

                        case 'str':
                            $entry->write($parameter['value'], NBinary::BINARY);
                            $entry->write("\x00", NBinary::BINARY);
                            $entry->write($entry->getPadding("\x70"), NBinary::BINARY);
                            break;
                    }

                }else{

                    $entry->write($parameter['value'], NBinary::INT_32);
                    //
//                    $type = Inst::$mh1Map[$record['entityClass']][$parameter['parameterId']];
//
//                    $entry->write($parameter['value'], $type);
                }

            }

            $recordBin[] = $entry;
        }

        // build size header
        foreach ($recordBin as $record) {
            $binary->write($record->length(), NBinary::INT_32);
        }

        // append records
        foreach ($recordBin as $record) {
            $binary->concat($record);
        }

        return $binary->binary;
    }
}
