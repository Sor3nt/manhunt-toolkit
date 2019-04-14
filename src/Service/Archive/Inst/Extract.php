<?php
namespace App\Service\Archive\Inst;

use App\MHT;
use App\Service\NBinary;

class Extract {

    public function get( NBinary $binary, $game ){

        // detect the platform
        $placementsBinary = $binary->get(4);

        if ($binary->unpack($placementsBinary, NBinary::INT_32) > 100000){
            $binary->numericBigEndian = true;
        }

        $placements = $binary->consume(4, NBinary::INT_32);

        $sizesLength = $placements * 4;

        //split sizes (header) from content
        $sizes = $binary->consume($sizesLength, NBinary::HEX);
        $sizes = str_split($sizes, 8);

        //extract every record
        $records = [];

        foreach ($sizes as $size) {
            $size = $binary->unpack(hex2bin($size), NBinary::INT_32);

            $block = new NBinary( $binary->consume($size, NBinary::BINARY) );

            $block->numericBigEndian = $binary->numericBigEndian;

            $record = $this->parseRecord( $block, $game );

            $records[$record['internalName'] . '.json'] = $record;
        }

        return $records;
    }


    private function parseRecord( NBinary $binary, $game ){

        /**
         * Find the  Record
         */
        $glgRecord = $binary->getString();

        /**
         * Find the internal name
         */
        $internalName = $binary->getString();


        /**
         * Find the position and rotation
         */

        $x = $binary->consume(4, NBinary::FLOAT_32);
        $y = $binary->consume(4, NBinary::FLOAT_32);
        $z = $binary->consume(4, NBinary::FLOAT_32);


        $rotationX = $binary->consume(4, NBinary::FLOAT_32);
        $rotationY = $binary->consume(4, NBinary::FLOAT_32);
        $rotationZ = $binary->consume(4, NBinary::FLOAT_32);
        $rotationW = $binary->consume(4, NBinary::FLOAT_32);

        /**
         * Find the entity class
         */
        $entityClass = $binary->getString();


        /**
         * Find parameters
         */
        $params = [];

        while($binary->remain() > 0) {

            if ($game == MHT::GAME_AUTO){

                if ($binary->remain() >= 12){
                    $maybeType  = trim($binary->get(4, 4));


                    if (in_array($maybeType, [ 'flo', 'boo', 'str', 'int' ])){
                        $game = MHT::GAME_MANHUNT_2;
                    }else{
                        $game = MHT::GAME_MANHUNT;
                    }

                }else{
                    $game = MHT::GAME_MANHUNT;

                }
            }


            if ($game == MHT::GAME_MANHUNT){
                while($binary->remain() > 0) {

                    $value = $binary->consume(4, NBinary::INT_32);

                    $params[] = [
                        'value' => $value
                    ];
                }
            }else{

                $parameterId = $binary->consume(4, NBinary::HEX);


                if($parameterId == "8bc3259e") $parameterId = "envExecution";
                if($parameterId == "ea6cf6cf") $parameterId = "weapon";


                $type = $binary->consume(4, NBinary::STRING);

                // float, boolean, integer are always 4-byte long
                // string need to be calculated
                switch ($type) {
                    case 'flo':
                        $value = $binary->consume(4, NBinary::FLOAT_32);
                        break;
                    case 'boo':
                        $value = $binary->consume(4, NBinary::INT_32);
                        break;
                    case 'int':
                        $value = $binary->consume(4, NBinary::INT_32);

                        if($parameterId == "weapon"){
                            if ($value == 9) $value = "nightstick";
                            if ($value == 17) $value = "syringe";
                        }

                        break;
                    case 'str':


                        $value = $binary->getString();

                        break;
                    default:
                        var_dump($internalName, $glgRecord);
                        die("type unknown " . $type);
                }

                $params[] = [
                    'parameterId' => $parameterId,
                    'type' => $type,
                    'value' => $value
                ];
            }
        };

        return [
            'record' => $glgRecord,
            'internalName' => $internalName,
            'entityClass' => $entityClass,
            'position' => [
                'x' => $x,
                'y' => $z,
                'z' => $y * -1
            ],
            'rotation' => [
                'x' => $rotationX,
                'y' => $rotationY,
                'z' => $rotationZ,
                'w' => $rotationW,
            ],
            'parameters' => $params
        ];
    }

}