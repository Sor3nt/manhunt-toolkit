<?php
namespace App\Service\Archive;

use App\Service\Binary;

class Inst {

    /**
     * Manhunt 2 - entity_pc.inst pack/unpack
     */

    public function unpack($data){

        $binary = new Binary( $data );

        $placements = $binary->substr(0, 4);

        $sizesLength = $placements->toInt() * 4;

        //split sizes (header) from content
        $sizes = $binary->substr(4, $sizesLength);
        $sizes = $sizes->split(4);

        $content = $binary->substr($sizesLength + 4);

        //extract every record
        $records = [];
        $pos = 0;
        foreach ($sizes as $size) {
            $size = $size->toInt();
            $records[] = $this->parseRecord( $content->substr($pos, $size) );
            $pos += $size;
        }

        return $records;
    }

    public function pack( $records ){

        $result = new Binary();

        // append record count
        $result->addBinary(pack("L", count($records)));

        $recordBin = [];
        foreach ($records as $index => $record) {
            $entry = new Binary();

            /*
             * Append GlgRecord name
             */
            $glgRecord = new Binary($record['glgRecord']);

            $entry->append( $glgRecord );
            $entry->addHex('00');
            $entry->addHex( str_repeat('70', $glgRecord->getMissedBytes() - 1 ) );

            /*
             * Append Internal name
             */
            $internalName = new Binary($record['internalName']);

            $entry->append( $internalName );
            $entry->addHex('00');
            $entry->addHex( str_repeat('70', $internalName->getMissedBytes() - 1 ) );

            /*
             * Append XYZ coordinates
             */
            $entry->addBinary(pack('g*' , $record['position'][1]));
            $entry->addBinary(pack('g*' , $record['position'][2]));
            $entry->addBinary(pack('g*' , $record['position'][3]));

            /*
             * Append rotation
             */
            $entry->addBinary(pack('g*' , $record['rotation'][1]));
            $entry->addBinary(pack('g*' , $record['rotation'][2]));
            $entry->addBinary(pack('g*' , $record['rotation'][3]));
            $entry->addBinary(pack('g*' , $record['rotation'][4]));

            /*
             * Append entity class
             */
            if ($record['entityClass']){

                $entityClass = new Binary($record['entityClass']);

                $entry->append( $entityClass );
                $entry->addHex('00');

                $entry->addHex( str_repeat('70', $entityClass->getMissedBytes() - 1 ) );
            }

            /*
             * Append parameters
             */
            foreach ($record['parameters'] as $parameter) {

                $entry->addBinary(pack('L' , $parameter['parameterId']));

                $type = new Binary($parameter['type']);
                $type->addMissedBytes('00');

                $entry->append($type);

                switch ($parameter['type']) {
                    case 'flo':
                        $entry->addBinary(pack("f", $parameter['value']));
                        break;
                    case 'boo':
                    case 'int':
                        $entry->addBinary( pack("L", $parameter['value']) );
                        break;
                    case 'str':

                        $value = new Binary($parameter['value']);
                        $missedToFinal4Byte = $value->getMissedBytes() - 1;

                        $entry->append($value);

                        $entry->addHex('00');
                        $entry->addHex( str_repeat('70', $missedToFinal4Byte ) );

                        break;
                }
            }

            $recordBin[] = $entry;
        }

        /** @var Binary[] $recordBin */

        // build size header
        foreach ($recordBin as $record) {
            $result->addBinary(pack("L", $record->length(true) / 2));
        }

        // append records
        foreach ($recordBin as $record) {
            $result->append($record);
        }

        return $result->toBinary();
    }


    public function parseRecord( Binary $record ){

        /** @var Binary $remain */
        /** @var Binary $rotation */

        /**
         * Find the Glg Record
         */
        $glgRecord = $record->substr(0, "\x00", $remain);
        $remain = $remain->skipBytes($glgRecord->getMissedBytes());

        /**
         * Find the internal name
         */
        $internalName = $remain->substr(0, "\x00", $remain);
        $remain = $remain->skipBytes($internalName->getMissedBytes());

        /**
         * Find the position and rotation
         */
        $position = $remain->substr(0, 28, $remain);

        $xyz = $position->substr(0, 12, $rotation);
        $xyz = unpack('g*' , $xyz->toBinary());

        $rotation = unpack('g*' , $rotation->toBinary());

        /**
         * Find the entity class
         */
        $entityClass = $remain->substr(0, "\x00", $remain);
        $remain = $remain->skipBytes($entityClass->getMissedBytes());

        /**
         * Find parameters
         */
        $params = [];
        do {

            // always 4-byte long
            $parameterId  = $remain->substr(0, 4, $remain);

            if ($remain->length()){

                // always 4-byte long
                $type = $remain->substr(0, 4, $remain);

                // float, boolean, integer are always 4-byte long
                // string need to be calculated
                switch ($type->toString()) {
                    case 'flo':
                        $value = $remain->substr(0, 4, $remain)->toFloat();
                        break;
                    case 'boo':
                        $value = $remain->substr(0, 4, $remain)->toBoolean();
                        break;
                    case 'int':
                        $value = $remain->substr(0, 4, $remain)->toInt();
                        break;
                    case 'str':

                        $value = $remain->substr(0, "\x00", $remain);
                        $remain = $remain->skipBytes($value->getMissedBytes());
                        $value = $value->toString();
                        break;
                    default:
                        die("type unknown " . $type->toString());
                }

                $params[] = [
                    'parameterId' => $parameterId->toInt(),
                    'type' => $type->toString(),
                    'value' => $value
                ];
            }

        }while($remain->length());

        return [
            'glgRecord' => $glgRecord->toBinary(),
            'internalName' => $internalName->toBinary(),
            'entityClass' => $entityClass->toBinary(),
            'position' => $xyz,
            'rotation' => $rotation,
            'parameters' => $params
        ];
    }

}