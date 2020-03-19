<?php
namespace App\Service\Archive\Inst;


use App\MHT;
use App\Service\Helper;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Build {


    private function calcHash($str){
        $hash = 0;

        for($c = 0; $c < strlen($str); $c++){
            $chr = (ord($str[$c]) - 97) <= 25 ? ord($str[$c]) - 32 : ord($str[$c]);
            $hash = $hash * 33 + $chr;
        }

        return $hash;
    }

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
//            $entry->write( $record['position']['x'], NBinary::FLOAT_32 );
//            $entry->write( $record['position']['z'] * -1, NBinary::FLOAT_32 );
//            $entry->write( $record['position']['y'], NBinary::FLOAT_32 );

            if ($record['position']['x'] === "-0"){
                if ($platform == MHT::PLATFORM_WII){
                    $entry->write( "\x80\x00\x00\x00", NBinary::BINARY );
                }else{
                    $entry->write( "\x00\x00\x00\x80", NBinary::BINARY );
                }
            }else{
                $entry->write( $record['position']['x'], NBinary::FLOAT_32 );
            }


            if ($game == MHT::GAME_MANHUNT_2){

                if ($record['position']['z'] === "-0"){
                    if ($platform == MHT::PLATFORM_WII){
                        $entry->write( "\x80\x00\x00\x00", NBinary::BINARY );
                    }else{
                        $entry->write( "\x00\x00\x00\x80", NBinary::BINARY );
                    }
                }else{
                    $entry->write( $record['position']['z'] * -1, NBinary::FLOAT_32 );
                }

                if ($record['position']['y'] === "-0"){
                    if ($platform == MHT::PLATFORM_WII){
                        $entry->write( "\x80\x00\x00\x00", NBinary::BINARY );
                    }else{
                        $entry->write( "\x00\x00\x00\x80", NBinary::BINARY );
                    }
                }else{
                    $entry->write( $record['position']['y'], NBinary::FLOAT_32 );
                }

            }else{
                if ($record['position']['y'] === "-0"){
                    if ($platform == MHT::PLATFORM_WII){
                        $entry->write( "\x80\x00\x00\x00", NBinary::BINARY );
                    }else{
                        $entry->write( "\x00\x00\x00\x80", NBinary::BINARY );
                    }
                }else{
                    $entry->write( $record['position']['y'], NBinary::FLOAT_32 );
                }


                if ($record['position']['z'] === "-0"){
                    if ($platform == MHT::PLATFORM_WII){
                        $entry->write( "\x80\x00\x00\x00", NBinary::BINARY );
                    }else{
                        $entry->write( "\x00\x00\x00\x80", NBinary::BINARY );
                    }
                }else{
                    $entry->write( $record['position']['z'], NBinary::FLOAT_32 );
                }

            }


            /*
             * Append rotation
             */
            if ($record['rotation']['x'] === "-0"){
                if ($platform == MHT::PLATFORM_WII){
                    $entry->write( "\x80\x00\x00\x00", NBinary::BINARY );
                }else{
                    $entry->write( "\x00\x00\x00\x80", NBinary::BINARY );
                }
            }else{
                $entry->write( $record['rotation']['x'], NBinary::FLOAT_32 );
            }

            if ($record['rotation']['y'] === "-0"){
                if ($platform == MHT::PLATFORM_WII){
                    $entry->write( "\x80\x00\x00\x00", NBinary::BINARY );
                }else{
                    $entry->write( "\x00\x00\x00\x80", NBinary::BINARY );
                }
            }else{
                $entry->write( $record['rotation']['y'], NBinary::FLOAT_32 );
            }

            if ($record['rotation']['z'] === "-0"){
                if ($platform == MHT::PLATFORM_WII){
                    $entry->write( "\x80\x00\x00\x00", NBinary::BINARY );
                }else{
                    $entry->write( "\x00\x00\x00\x80", NBinary::BINARY );
                }
            }else{
                $entry->write( $record['rotation']['z'], NBinary::FLOAT_32 );
            }

            if ($record['rotation']['w'] === "-0"){
                if ($platform == MHT::PLATFORM_WII){
                    $entry->write( "\x80\x00\x00\x00", NBinary::BINARY );
                }else{
                    $entry->write( "\x00\x00\x00\x80", NBinary::BINARY );
                }
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

            /*
             * Append parameters
             */
            foreach ($record['parameters'] as $parameter) {

                if (isset($parameter['parameterId'])){

                    $parameterId = $parameter['parameterId'];

                    foreach ($availableParams as $name => $hex) {
                        if ($parameterId == $name){
                            $parameterId = $hex;
                            break;
                        }
                    }

                    $parameterId = Helper::toBigEndian($parameterId);

                    $entry->write($parameterId, NBinary::HEX);

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


                                switch(strtolower($parameter['value'])){
                                    case "pipe": $parameter['value'] = 0; break;
                                    case "cleaver": $parameter['value'] = 1; break;
                                    case "wooden baseball bat": $parameter['value'] = 2; break;
                                    case "knife": $parameter['value'] = 3; break;
                                    case "baseball bat 1": $parameter['value'] = 4; break;
                                    case "baseball bat 2": $parameter['value'] = 5; break;
                                    case "not defined": $parameter['value'] = 6; break;
                                    case "crowbar": $parameter['value'] = 7; break;
                                    case "small bat": $parameter['value'] = 8; break;
                                    case "nightstick": $parameter['value'] = 9; break;
                                    case "axe": $parameter['value'] = 10; break;
                                    case "icepick": $parameter['value'] = 11; break;
                                    case "machete": $parameter['value'] = 12; break;
                                    case "sickle": $parameter['value'] = 13; break;
                                    case "baseball bat 3": $parameter['value'] = 14; break;
                                    case "spiked bat": $parameter['value'] = 15; break;
                                    case "chainsaw": $parameter['value'] = 16; break;
                                    case "syringe": $parameter['value'] = 17; break;
                                    case "shovel": $parameter['value'] = 18; break;
                                    case "sledgehammer": $parameter['value'] = 19; break;
                                    case "stunprod": $parameter['value'] = 20; break;
                                    case "pen": $parameter['value'] = 21; break;
                                    case "acid bottle": $parameter['value'] = 22; break;
                                    case "1h firearm": $parameter['value'] = 23; break;
                                    case "2h firearm": $parameter['value'] = 24; break;
                                    case "razor": $parameter['value'] = 25; break;
                                    case "blowtorch": $parameter['value'] = 26; break;
                                }
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
