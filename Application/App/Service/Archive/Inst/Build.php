<?php
namespace App\Service\Archive\Inst;

use App\MHT;
use App\Service\Archive\Inst;
use App\Service\CompilerV2\Manhunt2;
use App\Service\Helper;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Build {

    public function build( $pathFilename, string $platform ){

        $binary = new NBinary();

        if ($platform == MHT::PLATFORM_WII)
            $binary->numericBigEndian = true;

        if ($pathFilename instanceof Finder){
            $binary->write($pathFilename->count(), NBinary::INT_32);

            $pathFilename->sort(function(SplFileInfo $a, SplFileInfo $b){
                if ((int)$a->getFilename() == (int)$b->getFilename())
                    return 0;

                return (int)$a->getFilename() > (int)$b->getFilename() ? 1 : -1;
            });

        }else{
            $binary->write(count($pathFilename), NBinary::INT_32);
        }

        $recordBin = [];
        foreach ($pathFilename as $file) {

            $record = $file;

            if ($pathFilename instanceof Finder)
                $record = \json_decode($file->getContents(), true);

            /*
             * Append GlgRecord name
             */
            $entry = new NBinary($record['record']);
            $entry->numericBigEndian = $binary->numericBigEndian;
            $this->applyPadding($entry);

            /*
             * Append Internal name
             */
            $entry->write($record['internalName'], NBinary::BINARY);
            $this->applyPadding($entry);

            $entry->writeCoordinates($record['position']);
            $entry->writeCoordinates($record['rotation']);

            /*
             * Append entity class
             */
            if ($record['entityClass']){
                $entry->write($record['entityClass'], NBinary::BINARY);
                $this->applyPadding($entry);
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

                if ($record['game'] == MHT::GAME_MANHUNT ) {
                    $entry->write($parameter['value'], NBinary::INT_32);
                    continue;
                }

                $parameter['parameterId'] = Manhunt2::getHashByName($parameter['parameterId']);
                if ($binary->numericBigEndian)
                    $parameter['parameterId'] = Helper::toBigEndian($parameter['parameterId']);

                $entry->write($parameter['parameterId'], NBinary::HEX);
                $entry->write($parameter['type'], NBinary::BINARY);
                $this->applyPadding($entry);

                $parameterName = $parameter['parameterId'];
                switch ($parameter['type']) {
                    case 'flo':
                        $entry->write($parameter['value'], NBinary::FLOAT_32);
                        break;

                    case 'boo':
                    case 'int':
                        if($parameterName == "WEAPON") {
                            $parameter['value'] = Inst::$weapon[strtolower($parameter['value'])];
                        } else if($parameterName == "WEAPON2"){
                            $parameter['value'] = Inst::$weapon2[strtolower($parameter['value'])];
                        }

                        $entry->write($parameter['value'], NBinary::INT_32);
                        break;

                    case 'str':
                        $entry->write($parameter['value'], NBinary::BINARY);
                        $this->applyPadding($entry);
                        break;
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

    private function applyPadding(NBinary $binary) {
        $binary->write("\x00", NBinary::BINARY);
        $binary->write($binary->getPadding( "\x70"), NBinary::BINARY);
    }
}
