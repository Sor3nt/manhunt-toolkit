<?php
namespace App\Service\Archive\Inst;

use App\MHT;
use App\Service\Helper;
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


            $availableParams = [
                "HP%_" => "0028d4bc",
                "Weapon" => "cff66cea",
                "Weapon2" => "cec40a5c",
                "AI Type" => "3844a80c",
                "Drop_Ammo" => "20d5b47e",
                "Closest %" => "280139a2",
                "Light Type" => "5da669ba",
                "Cone Angle" => "0c15620c",
                "Attenuation Radius" => "b7ea39b4",
                "Lensflare Intensity" => "01b65783",
                "Light Effect Type" => "b0d906a7",
                "Effect Duration" => "fa4f8f73",
                "Flicker/Strobe On Time in ms" => "463390a1",
                "Flicker/Strobe Off Time in ms" => "3bcec87f",
                "Fade In Time in ms" => "edc90a4d",
                "Fade Out Time" => "72994537",
                "Type" => "002f9502",
                "Cone Angle" => "0c15620c",
                "Radius" => "c405c7e8",
                "Intensity" => "1c4d3507",
                "Type" => "002f9502",
                "AI_No_Anim" => "1dfb30aa",
                "AI_Piss_Here" => "786ce72b",
                "AI_Smoke_Here" => "103d8d6b",
                "AI_Vending_Machine" => "dec340a8",
                "AI_Check_Crawlspace" => "3735da2b",
                "ASYLUM_DOOR" => "1856b00e",
                "ASYLUM_PEER_ANIM" => "ab05968a",
                "ASYLUM_SPEAK_ANIM" => "d0039412",
                "ASYLUM_MONITOR_ANIM" => "a3ee4b46",
                "WATCHDOG_SMOKE_ANIM" => "9dea4ff3",
                "WATCHDOG_CHECK_CAM_ANIM" => "e72e4882",
                "WATCHDOG_WINDOW_ANIM" => "6768896c",
                "LEGION_I_KNOW_ANIM" => "0e635168",
                "LEGION_KICK_PROP_ANIM" => "9e97fb43",
                "LEGION_TALK_PROP_ANIM" => "f16ffe4d",
                "FREAKS_PISS_ANIM" => "5a64657e",
                "REAKS_VENDING_ANIM" => "80d6d7a4",
                "FREAKS_VOMIT_ANIM" => "d738d98e",
                "GENERIC_TALK_ANIM" => "4b7ef84c",
                "Stream Id" => "97d0ea19",
                "Bank Name" => "2a6c8dbd",
                "Volume" => "ce5c5678",
                "Radius" => "c405c7e8",
                "Trigger Probability" => "5827d3f5",
                "Execution Type" => "c2912616",
                "LOD" => "00014dbf",
                "LODNear" => "978c0905",
                "Max distance" => "05762dd1",
                "Min distance" => "07f22c8f",
                "MaxOpenAngleIn" => "e048e996",
                "MaxOpenAngleOut" => "e9663717",
                "Colour: Red" => "f65225e9",
                "Colour: Green" => "d2b364ff",
                "Colour: Blue" => "c08e3d36",
                "Lensflare Size" => "1afd6ad7",
                "Size" => "002ec5db",
                "Trigger Timeout" => "a13e5b7b",
                "Occlusion Ignorance" => "2f820985",
                "Detection Radius in Metres" => "56bbcd4e",
                "Detection Height in Metres" => "fc4a0dff",
                "HunterStart X" => "59b9cc7c",
                "HunterStart Y" => "59b9cc7d",
                "HunterStart Z" => "59b9cc7e",
                "HunterLook X" => "8d37c9c3",
                "HunterLook Z" => "8d37c9c5",
                "PlayerStart X" => "6aa37173",
                "PlayerStart Y" => "6aa37174",
                "PlayerStart Z" => "6aa37175",
                "PlayerLook X" => "76752bda",
                "PlayerLook Z" => "76752bdc",
                "Not Climbable" => "d9b0bdec",
                "Use Default AI" => "af09137c",
                "Line of sight" => "13ac7e3c",
                "Force to zone" => "802ac3ae",
                "Locked" => "b703a532",
                "Lockable" => "867f89bd",
                "Is Real Light" => "04b34658",
                "Switch On By Default" => "238de64f",
                "Affects Objects" => "7624ff66",
                "Affects Map" => "6e9304fa",
                "Creates Character Shadows" => "b56e06cd",
                "Has Lensflare" => "fc8e6578",
                "Light Fog" => "fb1451f4",
                "Has Searchlight Cone" => "fd7f33ef",
                "Switch Off After Duration" => "a2577825",
                "Fade Continously" => "e4e190d7",
                "Entity Light" => "f791cfb5",
                "Scene Light" => "bd0c0b46",
                "Shadows" => "a1d3fdf9",
                "Static" => "c7b14a28",
                "Flickering" => "e81a8fde",
                "Lens Flare" => "d9a0adbc",
                "Light Fog" => "fb1451f4",
                "Is Streamed" => "862b6551",
                "AdjacentDoor" => "e9c7a64e",
                "Name in Samplebank" => "d97f84f6",
                "Execution Object" => "9e25c38b",
                "Object Animation" => "b0d5e537",
                "React_to_Light" => "6bd8ebe8",
            ];

            if ($game == MHT::GAME_MANHUNT){
                while($binary->remain() > 0) {

                    $parameterId = $binary->consume(4, NBinary::HEX);

                    $parameterId = Helper::toBigEndian($parameterId);

                    foreach ($availableParams as $name => $hex) {
                        if ($parameterId == $hex){
                            $parameterId = $name;
                            break;
                        }
                    }


                    $params[] = [
                        'value' => $parameterId
                    ];
                }
            }else{

                $parameterId = $binary->consume(4, NBinary::HEX);

                $parameterId = Helper::toBigEndian($parameterId);

                foreach ($availableParams as $name => $hex) {
                    if ($parameterId == $hex){
                        $parameterId = $name;
                        break;
                    }
                }


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

                        if($parameterId == "Weapon" || $parameterId == "Weapon2"){

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


        if($game == MHT::GAME_MANHUNT_2){

            $returnPos = [
                'x' => $x,
                'y' => $z
            ];

            if ($y !== "-0"){
                $returnPos['z'] = $y * -1;
            }else{
                $returnPos['z'] = $y;
            }

        }else{

            $returnPos = [
                'x' => $x,
                'y' => $y,
                'z' => $z
            ];


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