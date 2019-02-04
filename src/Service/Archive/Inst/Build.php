<?php
namespace App\Service\Archive\Inst;


use App\MHT;
use App\Service\NBinary;

class Build {

    public function build( $records, $game, $platform ){

        $binary = new NBinary();

        if ($platform == MHT::PLATFORM_WII) $binary->numericBigEndian = true;


        // append record count
        $binary->write(count($records), NBinary::INT_32);

        $recordBin = [];
        foreach ($records as $index => $record) {
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
            $entry->write( $record['rotation']['x'], NBinary::FLOAT_32 );
            $entry->write( $record['rotation']['y'], NBinary::FLOAT_32 );
            $entry->write( $record['rotation']['z'], NBinary::FLOAT_32 );
            $entry->write( $record['rotation']['w'], NBinary::FLOAT_32 );

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
