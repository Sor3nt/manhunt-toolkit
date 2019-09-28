<?php
namespace App\Service\Archive;

use App\MHT;
use App\Service\Archive\Glg\EntityTypeData;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Glg extends Archive {
    public $name = 'Settings File';

    public static $supported = ['glg', 'ini'];

    /**
     * @param $pathFilename
     * @param $input
     * @param $game
     * @param $platform
     * @return bool
     */
    public static function canPack( $pathFilename, $input, $game, $platform ){
        if (!$input instanceof Finder) return false;

        foreach ($input as $file) {
            if (strpos($file->getFilename(), ".glg") !== false) return true;
        }

        return false;
    }

    public function unpack(NBinary $binary, $game, $platform){

        $contentAsLower = strtolower($binary->binary);

        if (
            strpos($contentAsLower, 'record') !== false &&
            strpos($contentAsLower, 'class') !== false &&
            strpos($contentAsLower, 'model') !== false
        ){

            $ecs = (new EntityTypeData())->parse($binary);

            $results = [];

            foreach ($ecs as $name => $ec) {

                switch ($ec->class){
                    case MHT::EC_PLAYER: $results[ 'Player/' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_CROPS: $results[ 'Crops/' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_MOVER: $results[ 'Movers/' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_WEAPON: $results[ 'Weapons/' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_BASIC: $results[ 'Basic/' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_SWITCH: $results[ 'Switches/' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_USEABLE: $results[ 'Useable/' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_SHOT: $results[ 'Shots/' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_TRIGGER: $results[ 'Triggers/' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_COLLECTABLE: $results[ 'Collectables/' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_PEDHEAD: $results[ 'Hunters/Heads/' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_SLIDEDOOR: $results[ 'Doors/Sliding/' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_DOOR: $results[ 'Doors/' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_RESPONDER: $results[ 'Responders/' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_ENTITYSOUND: $results[ 'Sounds/' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_ENVIRONMENTAL_EXECUTION: $results[ 'EnvExecution/' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_HUNTER:

                        //todo, lookup the PED with the right option...
                        if ($ec->get('head') == "no_hed"){
                            $results[ 'Hunters/BodyWithHead/' . $ec->get('name') ] = $ec;

                        }else{
                            $results[ 'Hunters/Body/' . $ec->get('name') . '_' . $ec->get('head') ] = $ec;
                        }

                        break;
                    case MHT::EC_ENTITYLIGHT: $results[ 'Lights/' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_HELICOPTER:
                    case 'other': $results[ 'Others/' . $ec->get('name') ] = $ec; break;

                    default:
                        die("unknown class " . $ec->class);

                }
            }

            return $results;

        }

        //it is already unzipped via NBinary
        return $binary->binary;
    }

    /**
     * @param $records
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack( $records, $game, $platform ){

        $result = [];

        /** @var Finder $records */
        foreach ($records as $record) {

            $result[] = $record->getContents();
        }


        return implode("\n", $result) . "\n";
    }

    public function convertRecords( $text ){

        $result = [];

        preg_match_all('/RECORD\s(.*\s)*?END/mi', $text, $matches);
        foreach ($matches[0] as $match) {
            preg_match('/RECORD\s(.*)((.*\s*)*)END/i', $match, $entry);

            $options = [];
            $optionsRaw = explode("\n", $entry[2]);

            foreach ($optionsRaw as $singleOption) {
                $singleOption = trim($singleOption);

                if (empty($singleOption)) continue;
                if (substr($singleOption, 0, 1) == "#") continue;

                if (strpos($singleOption, ' ') !== false){

                    $attr = substr($singleOption, 0 ,strpos($singleOption, ' '));
                    $value = substr($singleOption, strpos($singleOption, ' ') + 1);


                    $options[] = [
                        'attr' => $attr,
                        'value' => $value
                    ];
                }else{
                    $options[] = [
                        'attr' => $singleOption
                    ];
                }


            }


            $result[ trim($entry[1]) ] = $options;

        }

        return $result;
    }


}