<?php
namespace App\Service\Archive\Inst;


use App\MHT;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Build {

    public function build( Finder $pathFilename, $game, $platform ){

        // append record count
        $binary = new NBinary();

        if ($platform == MHT::PLATFORM_WII) $binary->numericBigEndian = true;

        $binary->write($pathFilename->count(), NBinary::INT_32);

        $pathFilename->sort(function($a,$b){
            return (int)$a->getFilename() > (int)$b->getFilename();
        });

        $recordBin = [];
        foreach ($pathFilename as $file) {

            $record = \json_decode($file->getContents(), true);

//        foreach ($records as $index => $record) {
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

            /*
             * Append XYZ coordinates
             */
            $entry->write( $record['position']['x'], NBinary::FLOAT_32 );
            $entry->write( $record['position']['z'] * -1, NBinary::FLOAT_32 );
            $entry->write( $record['position']['y'], NBinary::FLOAT_32 );

            /*
             * Append rotation
             */
            if ($record['rotation']['x'] === "-0"){
                $entry->write( "\x00\x00\x00\x80", NBinary::BINARY );
            }else{
                $entry->write( $record['rotation']['x'], NBinary::FLOAT_32 );
            }

            if ($record['rotation']['y'] === "-0"){
                $entry->write( "\x00\x00\x00\x80", NBinary::BINARY );
            }else{
                $entry->write( $record['rotation']['y'], NBinary::FLOAT_32 );
            }

            if ($record['rotation']['z'] === "-0"){
                $entry->write( "\x00\x00\x00\x80", NBinary::BINARY );
            }else{
                $entry->write( $record['rotation']['z'], NBinary::FLOAT_32 );
            }

            if ($record['rotation']['w'] === "-0"){
                $entry->write( "\x00\x00\x00\x80", NBinary::BINARY );
            }else{
                $entry->write( $record['rotation']['w'], NBinary::FLOAT_32 );
            }

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

                if (isset($parameter['parameterId'])){

                    if($parameter['parameterId'] == "envExecution") $parameter['parameterId'] = "8bc3259e";
                    if($parameter['parameterId'] == "weapon") $parameter['parameterId'] = "ea6cf6cf";

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
                            if($parameter['parameterId'] == "ea6cf6cf"){  //weapon
                                if ($parameter['value'] == "nightstick") $parameter['value'] = 9;
                                if ($parameter['value'] == "syringe") $parameter['value'] = 17;
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
