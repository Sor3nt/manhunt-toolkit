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
//            $entry->write( $record['position']['x'], NBinary::FLOAT_32 );
//            $entry->write( $record['position']['z'] * -1, NBinary::FLOAT_32 );
//            $entry->write( $record['position']['y'], NBinary::FLOAT_32 );

            if ($record['position']['x'] === "-0"){
                $entry->write( "\x00\x00\x00\x80", NBinary::BINARY );
            }else{
                $entry->write( $record['position']['x'], NBinary::FLOAT_32 );
            }

            if ($record['position']['z'] === "-0"){
                $entry->write( "\x00\x00\x00\x80", NBinary::BINARY );
            }else{
                $entry->write( $record['position']['z'] * -1, NBinary::FLOAT_32 );
            }

            if ($record['position']['y'] === "-0"){
                $entry->write( "\x00\x00\x00\x80", NBinary::BINARY );
            }else{
                $entry->write( $record['position']['y'], NBinary::FLOAT_32 );
            }


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

                    if($parameter['parameterId'] == "envExecName") $parameter['parameterId'] = "8bc3259e";
                    if($parameter['parameterId'] == "envExecEntityAnim") $parameter['parameterId'] = "37e5d5b0";
                    if($parameter['parameterId'] == "weapon") $parameter['parameterId'] = "ea6cf6cf";
                    if($parameter['parameterId'] == "envExecTriggerRadius") $parameter['parameterId'] = "4ecdbb56";
                    if($parameter['parameterId'] == "envExecHunterStartY") $parameter['parameterId'] = "7eccb959";
                    if($parameter['parameterId'] == "envExecHunterStartX") $parameter['parameterId'] = "7cccb959";
                    if($parameter['parameterId'] == "envExecHunterStartRotation") $parameter['parameterId'] = "c3c9378d";
                    if($parameter['parameterId'] == "envExecPlayerStartRotation") $parameter['parameterId'] = "da2b7576";
                    if($parameter['parameterId'] == "envExecPlayerStartRotation2") $parameter['parameterId'] = "dc2b7576";
                    if($parameter['parameterId'] == "envExecPlayerStartY") $parameter['parameterId'] = "7571a36a";
                    if($parameter['parameterId'] == "envExecPlayerStartX") $parameter['parameterId'] = "7371a36a";
                    if($parameter['parameterId'] == "envExecType") $parameter['parameterId'] = "ff0d4afc";
                    if($parameter['parameterId'] == "envExecUnknown") $parameter['parameterId'] = "7471a36a";
                    if($parameter['parameterId'] == "envExecUnknown3") $parameter['parameterId'] = "7dccb959";


                    if($parameter['parameterId'] == "envExecUnknown2") $parameter['parameterId'] = "162691c2";
                    if($parameter['parameterId'] == "envExecId") $parameter['parameterId'] = "162691c2";

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
