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

        foreach ($sizes as $i => $size) {
            $size = $binary->unpack(hex2bin($size), NBinary::INT_32);

            $block = new NBinary( $binary->consume($size, NBinary::BINARY) );

            $block->numericBigEndian = $binary->numericBigEndian;

            $record = $this->parseRecord( $block, $game );

            $records[($i + 1) . "#" .$record['internalName'] . '.json'] = $record;
//            $records[$record['internalName'] . '.json'] = $record;
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

        $x = $binary->consume(4, NBinary::BINARY);
        $y = $binary->consume(4, NBinary::BINARY);
        $z = $binary->consume(4, NBinary::BINARY);


        if ($x == "\x00\x00\x00\x80" || $x == "\x80\x00\x00\x00"){
            $x = "-0";
        }else{
            $x = $binary->unpack($x, NBinary::FLOAT_32);
        }

        if ($y == "\x00\x00\x00\x80" || $y == "\x80\x00\x00\x00"){
            $y = "-0";
        }else{
            $y = $binary->unpack($y, NBinary::FLOAT_32);
        }

        if ($z == "\x00\x00\x00\x80" || $z == "\x80\x00\x00\x00"){
            $z = "-0";
        }else{
            $z = $binary->unpack($z, NBinary::FLOAT_32);
        }

        $rotationX = $binary->consume(4, NBinary::BINARY);
        $rotationY = $binary->consume(4, NBinary::BINARY);
        $rotationZ = $binary->consume(4, NBinary::BINARY);
        $rotationW = $binary->consume(4, NBinary::BINARY);


        if ($rotationX == "\x00\x00\x00\x80" || $rotationX == "\x80\x00\x00\x00"){
            $rotationX = "-0";
        }else{
            $rotationX = $binary->unpack($rotationX, NBinary::FLOAT_32);
        }

        if ($rotationY == "\x00\x00\x00\x80" || $rotationY == "\x80\x00\x00\x00"){
            $rotationY = "-0";
        }else{
            $rotationY = $binary->unpack($rotationY, NBinary::FLOAT_32);
        }


        if ($rotationZ == "\x00\x00\x00\x80" || $rotationZ == "\x80\x00\x00\x00"){
            $rotationZ = "-0";
        }else{
            $rotationZ = $binary->unpack($rotationZ, NBinary::FLOAT_32);
        }

        if ($rotationW == "\x00\x00\x00\x80" || $rotationW == "\x80\x00\x00\x00"){
            $rotationW = "-0";
        }else{
            $rotationW = $binary->unpack($rotationW, NBinary::FLOAT_32);
        }


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


                if($parameterId == "8bc3259e") $parameterId = "envExecName";
                if($parameterId == "37e5d5b0") $parameterId = "envExecEntityAnim";
                if($parameterId == "ea6cf6cf") $parameterId = "weapon";
                if($parameterId == "4ecdbb56") $parameterId = "envExecTriggerRadius";
                if($parameterId == "7eccb959") $parameterId = "envExecHunterStartY";
                if($parameterId == "7cccb959") $parameterId = "envExecHunterStartX";
                if($parameterId == "c3c9378d") $parameterId = "envExecHunterStartRotation";
                if($parameterId == "7571a36a") $parameterId = "envExecPlayerStartY";
                if($parameterId == "7371a36a") $parameterId = "envExecPlayerStartX";
                if($parameterId == "da2b7576") $parameterId = "envExecPlayerStartRotation";
                if($parameterId == "dc2b7576") $parameterId = "envExecPlayerStartRotation2";
                if($parameterId == "ff0d4afc") $parameterId = "envExecType";
                if($parameterId == "7471a36a") $parameterId = "envExecUnknown";
                if($parameterId == "162691c2") $parameterId = "envExecId";
                if($parameterId == "7dccb959") $parameterId = "envExecUnknown3";


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

                            switch($value){
                                case 0: $value = "pipe"; break;
                                case 1: $value = "cleaver"; break;
                                case 2: $value = "wooden baseball bat"; break;
                                case 3: $value = "knife"; break;
                                case 4: $value = "baseball bat 1"; break;
                                case 5: $value = "baseball bat 2"; break;
                                case 6: $value = "not defined"; break;
                                case 7: $value = "crowbar"; break;
                                case 8: $value = "small bat"; break;
                                case 9: $value = "nightstick"; break;
                                case 10: $value = "axe"; break;
                                case 11: $value = "icepick"; break;
                                case 12: $value = "machete"; break;
                                case 13: $value = "sickle"; break;
                                case 14: $value = "baseball bat 3"; break;
                                case 15: $value = "spiked Bat"; break;
                                case 16: $value = "chainsaw"; break;
                                case 17: $value = "syringe"; break;
                                case 18: $value = "shovel"; break;
                                case 19: $value = "sledgehammer"; break;
                                case 20: $value = "stunprod"; break;
                                case 21: $value = "pen"; break;
                                case 22: $value = "acid bottle"; break;
                                case 23: $value = "1h firearm"; break;
                                case 24: $value = "2h firearm"; break;
                                case 25: $value = "razor"; break;
                                case 26: $value = "blowtorch"; break;
                            }

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

        $returnPos = [
            'x' => $x,
            'y' => $z
        ];

        if ($y !== "-0"){
            $returnPos['z'] = $y * -1;
        }else{
            $returnPos['z'] = $y;
        }

        return [
            'record' => $glgRecord,
            'internalName' => $internalName,
            'entityClass' => $entityClass,
            'position' => $returnPos,
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